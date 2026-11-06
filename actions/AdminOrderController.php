<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\Order;
use App\Repositories\Payment;

class AdminOrderController extends Controller
{
    public function index(): void
    {
        Auth::requireAdminOrOrganizer();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $eventId = (int)($_GET['event_id'] ?? 0);
        $search = trim($_GET['search'] ?? '');

        if (Auth::isOrganizer()) {
            $ids = Auth::organizerEventIds();
            if ($eventId && !in_array($eventId, $ids, true)) {
                $eventId = 0;
            }
        }

        $db = \App\Database\Database::getInstance();
        $query = "
            SELECT o.*, e.name as event_name, u.name as user_name, u.email as user_email
            FROM orders o
            JOIN events e ON o.event_id = e.id
            JOIN users u ON o.user_id = u.id
        ";
        $where = [];
        $params = [];

        if ($status) {
            $where[] = "o.status = ?";
            $params[] = $status;
        }

        if ($eventId) {
            $where[] = "o.event_id = ?";
            $params[] = $eventId;
        } elseif (Auth::isOrganizer()) {
            $ids = Auth::organizerEventIds();
            if (!$ids) {
                $where[] = "1 = 0";
            } else {
                $ph = implode(',', array_fill(0, count($ids), '?'));
                $where[] = "o.event_id IN ({$ph})";
                $params = array_merge($params, $ids);
            }
        }

        if ($search) {
            $where[] = "(o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($where) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        $query .= " ORDER BY o.created_at DESC";

        // Count total (same joins so u.* filters work)
        $countQuery = "SELECT COUNT(*) as total FROM orders o JOIN events e ON o.event_id = e.id JOIN users u ON o.user_id = u.id" . ($where ? " WHERE " . implode(" AND ", $where) : "");
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        // Paginate
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        $events = Auth::isAdmin() ? \App\Repositories\Event::all([], 'name ASC') : $this->organizerEvents();
        
        $pagination = [
            'data' => $orders,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
        ];
        
        $this->view('admin/orders/index', [
            'title' => 'Kelola Pesanan',
            'orders' => $pagination['data'],
            'pagination' => $pagination,
            'events' => $events,
            'filters' => compact('status', 'eventId', 'search'),
        ]);
    }
    
    public function show(int $id): void
    {
        Auth::requireAdminOrOrganizer();

        $db = \App\Database\Database::getInstance();
        $stmt = $db->prepare("
            SELECT o.*, e.name as event_name, u.name as user_name, u.email as user_email, u.phone as user_phone
            FROM orders o
            JOIN events e ON o.event_id = e.id
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order || !Auth::canManageEvent((int)$order['event_id'])) {
            $this->abort(404);
        }
        
        // Get order items
        $itemsStmt = $db->prepare("
            SELECT oi.*, tc.name as class_name, tc.class_type, tc.section
            FROM order_items oi
            JOIN ticket_classes tc ON oi.ticket_class_id = tc.id
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$id]);
        $items = $itemsStmt->fetchAll();
        
        // Get tickets
        $ticketsStmt = $db->prepare("
            SELECT * FROM tickets WHERE order_id = ?
        ");
        $ticketsStmt->execute([$id]);
        $tickets = $ticketsStmt->fetchAll();
        
        // Get payment
        $paymentStmt = $db->prepare("
            SELECT * FROM payments WHERE order_id = ?
        ");
        $paymentStmt->execute([$id]);
        $payment = $paymentStmt->fetch();
        
        $this->view('admin/orders/show', [
            'title' => 'Detail Pesanan #' . str_pad($id, 8, '0', STR_PAD_LEFT),
            'order' => $order,
            'items' => $items,
            'tickets' => $tickets,
            'payment' => $payment,
        ]);
    }
    
    public function verifyPayment(int $id): void
    {
        Auth::requireAdminOrOrganizer();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $action = $_POST['action'] ?? ''; // 'approve' or 'reject'
        $notes = trim($_POST['notes'] ?? '');

        $payment = Payment::find($id);
        if (!$payment) {
            Flash::error('Pembayaran tidak ditemukan');
            $this->back();
        }

        $order = Order::find($payment->order_id);
        if (!$order || !Auth::canManageEvent((int)$order->event_id)) {
            $this->abort(404);
        }
        
        if ($action === 'approve') {
            $payment->update([
                'status' => 'approved',
                'verified_by' => Auth::user()['id'],
                'verified_at' => date('Y-m-d H:i:s'),
            ]);
            
            $order->markPaid();
            
            // Generate tickets (seated classes already reserved theirs at checkout)
            foreach ($order->items() as $item) {
                $existing = \App\Repositories\Ticket::all(['order_id' => $order->id, 'order_item_id' => $item->id]);
                $need = max(0, (int)$item->quantity - count($existing));
                for ($i = 0; $i < $need; $i++) {
                    $ticketCode = generateTicketCode();
                    \App\Repositories\Ticket::create([
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'ticket_code' => $ticketCode,
                        'holder_name' => $order->user()->name,
                        'holder_email' => $order->user()->email,
                    ]);
                }
            }
            
            Flash::success('Pembayaran diverifikasi & tiket telah dibuat');
        } elseif ($action === 'reject') {
            $payment->update([
                'status' => 'rejected',
                'verified_by' => Auth::user()['id'],
                'verified_at' => date('Y-m-d H:i:s'),
            ]);
            
            Flash::success('Pembayaran ditolak');
        } else {
            Flash::error('Aksi tidak valid');
        }
        
        $this->redirect('/admin/orders/' . $order->id);
    }

    private function organizerEvents(): array
    {
        $ids = Auth::organizerEventIds();
        if (!$ids) return [];
        $all = \App\Repositories\Event::all([], 'name ASC');
        return array_values(array_filter($all, fn($e) => in_array((int)$e->id, $ids, true)));
    }
}