<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Repositories\Event;
use App\Repositories\Order;
use App\Repositories\Payment;

class AdminController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireAdminOrOrganizer();

        $db = \App\Database\Database::getInstance();
        $isOrganizer = Auth::isOrganizer();
        $ids = $isOrganizer ? Auth::organizerEventIds() : [];

        if ($isOrganizer && !$ids) {
            $stats = [
                'total_events' => 0, 'active_events' => 0, 'total_orders' => 0,
                'pending_orders' => 0, 'paid_orders' => 0, 'total_revenue' => 0,
                'pending_payments' => 0,
            ];
            $recentOrders = [];
            $eventList = [];
            $recentPOs = [];
            $venueList = [];
        } else {
            $ph = $isOrganizer ? implode(',', array_fill(0, count($ids), '?')) : '';
            $evWhere = $isOrganizer ? "WHERE id IN ({$ph})" : '';
            $evActiveWhere = $isOrganizer ? "WHERE status IN ('po_open', 'public') AND id IN ({$ph})" : "WHERE status IN ('po_open', 'public')";
            $evParams = $isOrganizer ? $ids : [];
            $ordWhere = function (string $extra = '') use ($isOrganizer, $ph) {
                $w = [];
                if ($extra) $w[] = $extra;
                if ($isOrganizer) $w[] = "o.event_id IN ({$ph})";
                return $w ? 'WHERE ' . implode(' AND ', $w) : '';
            };
            $one = function (string $sql, array $p = []) use ($db) {
                $stmt = $db->prepare($sql);
                $stmt->execute($p);
                return $stmt->fetch();
            };

            $stats = [
                'total_events' => (int)($one("SELECT COUNT(*) as c FROM events {$evWhere}", $evParams)['c'] ?? 0),
                'active_events' => (int)($one("SELECT COUNT(*) as c FROM events {$evActiveWhere}", $evParams)['c'] ?? 0),
                'total_orders' => (int)($one("SELECT COUNT(*) as c FROM orders o " . $ordWhere(), $isOrganizer ? $ids : [])['c'] ?? 0),
                'pending_orders' => (int)($one("SELECT COUNT(*) as c FROM orders o " . $ordWhere("o.status = 'pending'"), $isOrganizer ? $ids : [])['c'] ?? 0),
                'paid_orders' => (int)($one("SELECT COUNT(*) as c FROM orders o " . $ordWhere("o.status = 'paid'"), $isOrganizer ? $ids : [])['c'] ?? 0),
                'total_revenue' => (float)($one("SELECT COALESCE(SUM(total_amount), 0) as s FROM orders o " . $ordWhere("o.status = 'paid'"), $isOrganizer ? $ids : [])['s'] ?? 0),
                'pending_payments' => (int)($one(
                    "SELECT COUNT(*) as c FROM payments p JOIN orders o ON p.order_id = o.id " . $ordWhere("p.status = 'pending'"),
                    $isOrganizer ? $ids : []
                )['c'] ?? 0),
            ];

            // Recent orders (scoped)
            $recentSql = "
                SELECT o.*, e.name as event_name, u.name as user_name
                FROM orders o
                JOIN events e ON o.event_id = e.id
                JOIN users u ON o.user_id = u.id
                " . $ordWhere() . "
                ORDER BY o.created_at DESC LIMIT 10
            ";
            $stmt = $db->prepare($recentSql);
            $stmt->execute($isOrganizer ? $ids : []);
            $recentOrders = $stmt->fetchAll();

            // Event list (all statuses incl. draft, scoped)
            $upSql = "SELECT * FROM events";
            $upParams = [];
            if ($isOrganizer) {
                $upSql .= " WHERE id IN ({$ph})";
                $upParams = $ids;
            }
            $upSql .= " ORDER BY created_at DESC LIMIT 5";
            $stmt = $db->prepare($upSql);
            $stmt->execute($upParams);
            $eventList = $stmt->fetchAll();

            // Recent PO codes (scoped to organizer events)
            $poSql = "
                SELECT p.code, p.max_quota, p.used_count, p.expires_at, p.is_active, e.name as event_name
                FROM po_accesses p
                JOIN events e ON p.event_id = e.id
            ";
            $poParams = [];
            if ($isOrganizer) {
                $poSql .= " WHERE p.event_id IN ({$ph})";
                $poParams = $ids;
            }
            $poSql .= " ORDER BY p.created_at DESC LIMIT 5";
            $stmt = $db->prepare($poSql);
            $stmt->execute($poParams);
            $recentPOs = $stmt->fetchAll();

            // Venue list with event counts (admin only; venues are global/shared)
            $venueList = [];
            if (!$isOrganizer) {
                $venueList = $db->query("
                    SELECT v.id, v.name, v.city, v.capacity, COUNT(e.id) as event_count
                    FROM venues v
                    LEFT JOIN events e ON e.venue_id = v.id
                    GROUP BY v.id, v.name, v.city, v.capacity
                    ORDER BY v.name ASC LIMIT 5
                ")->fetchAll();
            }
        }

        $this->view('admin/dashboard', [
            'title' => $isOrganizer ? 'Dashboard Organizer' : 'Dashboard Admin',
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'eventList' => $eventList,
            'recentPOs' => $recentPOs,
            'venueList' => $venueList,
            'isOrganizer' => $isOrganizer,
        ]);
    }
}