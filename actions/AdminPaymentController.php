<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Repositories\Payment;

class AdminPaymentController extends Controller
{
    public function index(): void
    {
        Auth::requireAdminOrOrganizer();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $eventId = (int)($_GET['event_id'] ?? 0);

        if (Auth::isOrganizer()) {
            $ids = Auth::organizerEventIds();
            if ($eventId && !in_array($eventId, $ids, true)) {
                $eventId = 0;
            }
        }

        $db = \App\Database\Database::getInstance();
        $query = "
            SELECT p.*, o.id as order_id, e.name as event_name, u.name as user_name
            FROM payments p
            JOIN orders o ON p.order_id = o.id
            JOIN events e ON o.event_id = e.id
            JOIN users u ON o.user_id = u.id
        ";
        $where = [];
        $params = [];

        if ($status) {
            $where[] = "p.status = ?";
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

        if ($where) {
            $query .= " WHERE " . implode(" AND ", $where);
        }

        $query .= " ORDER BY p.created_at DESC";

        // Count total (same joins so o.* filters work)
        $countQuery = "SELECT COUNT(*) as total FROM payments p JOIN orders o ON p.order_id = o.id JOIN events e ON o.event_id = e.id JOIN users u ON o.user_id = u.id" . ($where ? " WHERE " . implode(" AND ", $where) : "");
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetch()['total'];

        // Paginate
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        $events = Auth::isAdmin() ? \App\Repositories\Event::all([], 'name ASC') : $this->organizerEvents();

        $pagination = [
            'data' => $payments,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
        ];

        $this->view('admin/payments/index', [
            'title' => 'Verifikasi Pembayaran',
            'payments' => $pagination['data'],
            'pagination' => $pagination,
            'events' => $events,
            'filters' => compact('status', 'eventId'),
        ]);
    }

    private function organizerEvents(): array
    {
        $ids = Auth::organizerEventIds();
        if (!$ids) return [];
        $all = \App\Repositories\Event::all([], 'name ASC');
        return array_values(array_filter($all, fn($e) => in_array((int)$e->id, $ids, true)));
    }
}