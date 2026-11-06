<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\Event;
use App\Repositories\TicketClass;
use App\Repositories\Order;
use App\Repositories\OrderItem;
use App\Repositories\Ticket;
use App\Repositories\POAccess;

class CheckoutController extends Controller
{
    public function store(): void
    {
        Auth::requireAuth();
        
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }
        
        $eventId = (int)($_POST['event_id'] ?? 0);
        $ticketClassId = (int)($_POST['ticket_class_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        $poCode = trim($_POST['po_code'] ?? '');
        $isPO = !empty($poCode);

        $event = Event::find($eventId);
        $ticketClass = TicketClass::find($ticketClassId);

        if (!$event || !$ticketClass) {
            Flash::error('Acara atau kelas tiket tidak ditemukan');
            $this->back();
        }

        if ($ticketClass->event_id !== $event->id) {
            Flash::error('Kelas tiket tidak sesuai dengan acara');
            $this->back();
        }

        // Seated flow: 1 ticket = 1 specific seat, quantity comes from seats[]
        $seats = [];
        $isSeated = $ticketClass->isSeated();
        if ($isSeated) {
            $raw = $_POST['seats'] ?? [];
            if (!is_array($raw)) $raw = [$raw];
            $seats = array_values(array_unique(array_map(
                fn($s) => strtoupper(trim((string)$s)),
                $raw
            )));
            $seats = array_values(array_filter($seats));
            if (!$seats) {
                Flash::error('Pilih minimal 1 kursi');
                $this->back();
            }
            if (count($seats) > 10) {
                Flash::error('Maksimal pembelian 10 kursi per transaksi');
                $this->back();
            }
            $validSeats = $ticketClass->seats();
            foreach ($seats as $s) {
                if (!in_array($s, $validSeats, true)) {
                    Flash::error("Kursi {$s} tidak valid untuk kelas ini");
                    $this->back();
                }
            }
            $taken = array_intersect($seats, $ticketClass->soldSeats());
            if ($taken) {
                Flash::error('Kursi sudah terjual: ' . implode(', ', array_slice($taken, 0, 5)));
                $this->back();
            }
            $quantity = count($seats);
        }

        $available = $ticketClass->available();
        if ($quantity > $available) {
            Flash::error("Tiket tersisa {$available} saja");
            $this->back();
        }

        if ($quantity > 10) {
            Flash::error('Maksimal pembelian 10 tiket per transaksi');
            $this->back();
        }
        
        // Check PO code if provided
        $poAccess = null;
        if ($isPO) {
            $poAccess = POAccess::first(['code' => strtoupper($poCode), 'event_id' => $event->id, 'is_active' => 1]);
            
            if (!$poAccess) {
                Flash::error('Kode Flash Sale tidak valid untuk acara ini');
                $this->back();
            }
            
            if ($poAccess->expires_at && strtotime($poAccess->expires_at) < time()) {
                Flash::error('Kode Flash Sale sudah kedaluwarsa');
                $this->back();
            }
            
            if ($poAccess->used_count >= $poAccess->max_quota) {
                Flash::error('Kuota kode Flash Sale sudah habis');
                $this->back();
            }
            
            if (!$event->canPurchase(null, true)) {
                Flash::error('Flash Sale belum dibuka atau sudah ditutup');
                $this->back();
            }
            
            $remaining = $event->getPORemaining();
            if ($quantity > $remaining) {
                Flash::error("Sisa kuota Flash Sale: {$remaining} tiket");
                $this->back();
            }
        } else {
            if (!$event->canPurchase()) {
                Flash::error('Pembelian tiket belum dibuka untuk umum');
                $this->back();
            }
        }
        
        $user = Auth::user();
        $unitPrice = $ticketClass->price;
        $totalAmount = $unitPrice * $quantity;
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours'));
        
        $db = \App\Database\Database::getInstance();
        try {
            $db->beginTransaction();

            // Re-check stock with lock to avoid race
            $lockStmt = $db->prepare("SELECT sold_count, capacity FROM ticket_classes WHERE id = ? FOR UPDATE");
            $lockStmt->execute([$ticketClass->id]);
            $locked = $lockStmt->fetch();
            if ($locked) {
                $availableLocked = max(0, (int)$locked['capacity'] - (int)$locked['sold_count']);
                if ($quantity > $availableLocked) {
                    $db->rollBack();
                    Flash::error("Tiket tersisa {$availableLocked} saja (terupdate)");
                    $this->back();
                }
            }
            // Re-check seats inside lock (serializes concurrent buyers)
            if ($isSeated) {
                $takenLocked = array_intersect($seats, $ticketClass->soldSeats());
                if ($takenLocked) {
                    $db->rollBack();
                    Flash::error('Kursi baru saja terjual: ' . implode(', ', array_slice($takenLocked, 0, 5)));
                    $this->back();
                }
            }
            if ($isPO && $poAccess) {
                $poLock = $db->prepare("SELECT used_count, max_quota FROM po_accesses WHERE id = ? FOR UPDATE");
                $poLock->execute([$poAccess->id]);
                $poLocked = $poLock->fetch();
                if ($poLocked && (int)$poLocked['used_count'] + $quantity > (int)$poLocked['max_quota']) {
                    $db->rollBack();
                    Flash::error('Kuota kode Flash Sale sudah habis (terupdate)');
                    $this->back();
                }
                $remaining = $event->getPORemaining();
                if ($quantity > $remaining) {
                    $db->rollBack();
                    Flash::error("Sisa kuota Flash Sale: {$remaining} tiket");
                    $this->back();
                }
            }

            // Create order
            $order = Order::create([
                'user_id' => $user['id'],
                'event_id' => $event->id,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'payment_method' => 'midtrans',
                'expires_at' => $expiresAt,
                'is_po' => $isPO ? 1 : 0,
                'po_code_used' => $isPO ? strtoupper($poCode) : null,
            ]);
            
            // Create order item
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'ticket_class_id' => $ticketClass->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $totalAmount,
            ]);

            // Reserve seats immediately (pending orders hold their seats until expiry)
            if ($isSeated) {
                foreach ($seats as $seat) {
                    Ticket::create([
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'ticket_code' => generateTicketCode(),
                        'seat_label' => $seat,
                        'holder_name' => $user['name'],
                        'holder_email' => $user['email'],
                    ]);
                }
            }
            
            // Increment PO usage if applicable
            if ($isPO && $poAccess) {
                $db->prepare("UPDATE po_accesses SET used_count = used_count + ? WHERE id = ?")
                   ->execute([$quantity, $poAccess->id]);
            }
            
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log('[Artatix checkout] ' . $e->getMessage());
            Flash::error('Gagal membuat pesanan, coba lagi');
            $this->back();
        }
        
        // Mock success - redirect to success page
        $this->redirect(url('/checkout/' . $order->id . '/success'));
    }
    
    public function success(int $orderId): void
    {
        Auth::requireAuth();
        
        $order = Order::find($orderId);
        
        if (!$order || $order->user_id !== Auth::user()['id']) {
            $this->abort(404);
        }
        
        $event = $order->event();
        $items = $order->items();
        
        $this->view('checkout/success', [
            'title' => 'Pembelian Berhasil',
            'order' => $order,
            'event' => $event,
            'items' => $items,
        ]);
    }
    
    public function pay(int $orderId): void
    {
        Auth::requireAuth();
        
        $order = Order::find($orderId);
        
        if (!$order || $order->user_id !== Auth::user()['id']) {
            $this->abort(404);
        }
        
        if (!$order->canPay()) {
            Flash::error('Pesanan tidak bisa dibayar');
            $this->redirect(url('/user/orders'));
        }
        
        // TODO: Integrate with Midtrans Snap
        // For now, mock payment success
        $order->markPaid();
        
        // Generate tickets (seated classes already reserved theirs at checkout)
        foreach ($order->items() as $item) {
            $existing = Ticket::all(['order_id' => $order->id, 'order_item_id' => $item->id]);
            $need = max(0, (int)$item->quantity - count($existing));
            for ($i = 0; $i < $need; $i++) {
                $ticketCode = generateTicketCode();
                Ticket::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'ticket_code' => $ticketCode,
                    'holder_name' => Auth::user()['name'],
                    'holder_email' => Auth::user()['email'],
                ]);
            }
        }
        
        Flash::success('Pembayaran berhasil! Tiket sudah tersedia di akun Anda.');
        $this->redirect(url('/user/orders/' . $order->id));
    }
}