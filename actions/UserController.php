<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\Order;
use App\Repositories\Ticket;
use App\Repositories\POAccess;
use App\Repositories\User;

class UserController extends Controller
{
    public function orders(): void
    {
        Auth::requireAuth();
        
        $userId = Auth::user()['id'];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $db = \App\Database\Database::getInstance();
        // Count efficiently
        $cntStmt = $db->prepare("SELECT COUNT(*) as cnt FROM orders WHERE user_id = ?");
        $cntStmt->execute([$userId]);
        $total = (int)($cntStmt->fetch()['cnt'] ?? 0);
        
        $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        // Hydrate to Order objects so views have model methods
        $orders = array_map(fn($row) => Order::hydrate($row), $rows);
        
        $pagination = [
            'data' => $orders,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int)ceil($total / $perPage) ?: 1,
        ];
        
        $this->view('user/orders', [
            'title' => 'Pesanan Saya',
            'pagination' => $pagination,
        ]);
    }
    
    public function orderDetail(int $id): void
    {
        Auth::requireAuth();
        
        $order = Order::find($id);
        
        if (!$order || $order->user_id !== Auth::user()['id']) {
            $this->abort(404);
        }
        
        $event = $order->event();
        $items = $order->items();
        $tickets = $order->tickets();
        $payment = $order->payment();
        
        $this->view('user/order_detail', [
            'title' => 'Detail Pesanan #' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
            'order' => $order,
            'event' => $event,
            'items' => $items,
            'tickets' => $tickets,
            'payment' => $payment,
        ]);
    }
    
    public function ticketDetail(string $code): void
    {
        Auth::requireAuth();
        
        $ticket = Ticket::first(['ticket_code' => $code]);

        if (!$ticket) {
            $this->abort(404);
        }

        $order = $ticket->order();

        if (!$order || $order->user_id !== Auth::user()['id']) {
            $this->abort(404);
        }

        $event = $order->event();

        if (!$event) {
            $this->abort(404, 'Acara tiket ini sudah tidak tersedia');
        }

        $item = $ticket->orderItem();
        $tc = $item ? $item->ticketClass() : null;
        
        $this->view('user/ticket_detail', [
            'title' => 'Tiket - ' . $ticket->ticket_code,
            'ticket' => $ticket,
            'order' => $order,
            'event' => $event,
            'ticketClass' => $tc,
        ]);
    }
    
    // API endpoint for checking Flash Sale code
    public function checkPOCode(): void
    {
        header('Content-Type: application/json');
        
        if (!verifyCsrf()) {
            echo json_encode(['valid' => false, 'message' => 'Token tidak valid']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $code = strtoupper(trim($input['code'] ?? ''));
        $eventId = (int)($input['event_id'] ?? 0);
        
        if (!$code || !$eventId) {
            echo json_encode(['valid' => false, 'message' => 'Kode Flash Sale dan ID acara wajib diisi']);
            exit;
        }
        
        $poAccess = POAccess::first(['code' => $code, 'event_id' => $eventId, 'is_active' => 1]);
        
        if (!$poAccess) {
            echo json_encode(['valid' => false, 'message' => 'Kode Flash Sale tidak ditemukan']);
            exit;
        }
        
        if ($poAccess->expires_at && strtotime($poAccess->expires_at) < time()) {
            echo json_encode(['valid' => false, 'message' => 'Kode Flash Sale sudah kedaluwarsa']);
            exit;
        }
        
        if ($poAccess->used_count >= $poAccess->max_quota) {
            echo json_encode(['valid' => false, 'message' => 'Kuota kode Flash Sale sudah habis']);
            exit;
        }
        
        $event = \App\Repositories\Event::find($eventId);
        if (!$event || !$event->canPurchase(null, true)) {
            echo json_encode(['valid' => false, 'message' => 'Flash Sale belum dibuka atau sudah ditutup']);
            exit;
        }
        
        $remaining = $event->getPORemaining();
        
        echo json_encode([
            'valid' => true,
            'message' => 'Kode Flash Sale valid',
            'remaining_quota' => $remaining,
            'po_quota' => $poAccess->max_quota,
            'used' => $poAccess->used_count,
        ]);
    }

    public function settings(): void
    {
        Auth::requireAuth();

        $this->view('user/settings', [
            'title' => 'Pengaturan Akun',
            'user' => Auth::user(),
        ]);
    }

    public function updateSettings(): void
    {
        Auth::requireAuth();
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->redirect(url('/user/settings'));
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $errors = [];
        if (!$name) $errors[] = 'Nama wajib diisi';
        if ($phone && !preg_match('/^[\d\s\-\+\(\)]{10,20}$/', $phone)) $errors[] = 'Nomor telepon tidak valid';

        if ($errors) {
            Flash::error(implode('<br>', $errors));
            $this->redirect(url('/user/settings'));
        }

        $user = User::find(Auth::user()['id']);
        if (!$user) {
            $this->abort(404);
        }

        $user->update(['name' => $name, 'phone' => $phone ?: null]);
        Flash::success('Profil berhasil diperbarui');
        $this->redirect(url('/user/settings'));
    }

    public function updatePassword(): void
    {
        Auth::requireAuth();
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->redirect(url('/user/settings'));
        }

        $current = $_POST['current_password'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        $errors = [];
        if (!$current) $errors[] = 'Password lama wajib diisi';
        if (!$password) $errors[] = 'Password baru wajib diisi';
        if (strlen($password) < 8) $errors[] = 'Password baru minimal 8 karakter';
        if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak cocok';

        $user = User::find(Auth::user()['id']);
        if (!$user) {
            $this->abort(404);
        }

        if ($current && !$user->verifyPassword($current)) {
            $errors[] = 'Password lama salah';
        }

        if ($errors) {
            Flash::error(implode('<br>', $errors));
            $this->redirect(url('/user/settings'));
        }

        $db = \App\Database\Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $user->id]);
        Flash::success('Password berhasil diubah');
        $this->redirect(url('/user/settings'));
    }

    public function cancelOrder(int $id): void
    {
        Auth::requireAuth();
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->redirect(url('/user/orders/' . $id));
        }
        $order = Order::find($id);
        if (!$order || $order->user_id !== Auth::user()['id']) {
            $this->abort(404);
        }
        if (!$order->isPending()) {
            Flash::error('Hanya pesanan pending yang bisa dibatalkan');
            $this->redirect(url('/user/orders/' . $id));
        }
        if ($order->cancel()) {
            // Restore PO quota if PO order
            if ($order->is_po && $order->po_code_used) {
                $po = POAccess::first(['code' => $order->po_code_used, 'event_id' => $order->event_id]);
                if ($po) {
                    $items = $order->items();
                    $qty = array_sum(array_map(fn($it)=>(int)$it->quantity, $items));
                    $po->used_count = max(0, (int)$po->used_count - $qty);
                    $po->update(['used_count' => $po->used_count]);
                }
            }
            Flash::success('Pesanan dibatalkan');
        } else {
            Flash::error('Gagal membatalkan pesanan');
        }
        $this->redirect(url('/user/orders'));
    }
}