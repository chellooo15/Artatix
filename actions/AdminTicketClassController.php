<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\TicketClass;
use App\Repositories\Event;

class AdminTicketClassController extends Controller
{
    public function index(): void
    {
        Auth::requireAdminOrOrganizer();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $eventId = (int)($_GET['event_id'] ?? 0);
        $type = $_GET['type'] ?? '';

        // Organizer: force scope to own events
        if (Auth::isOrganizer()) {
            $ids = Auth::organizerEventIds();
            if ($eventId && !in_array($eventId, $ids, true)) {
                $eventId = 0;
            }
        }

        $query = TicketClass::query()
            ->join('events', 'ticket_classes.event_id', '=', 'events.id')
            ->select('ticket_classes.*, events.name as event_name')
            ->orderBy('ticket_classes.created_at', 'DESC');

        if (Auth::isOrganizer()) {
            $query->whereIn('ticket_classes.event_id', Auth::organizerEventIds());
        }

        if ($eventId) {
            $query->where('ticket_classes.event_id', '=', $eventId);
        }

        if ($type) {
            $query->where('ticket_classes.class_type', '=', $type);
        }

        $pagination = $query->paginate(20, $page);
        $events = Auth::isAdmin() ? Event::all([], 'name ASC') : $this->organizerEvents();

        $this->view('admin/ticket_classes/index', [
            'title' => 'Kelola Kelas Tiket',
            'ticketClasses' => $pagination['data'],
            'pagination' => $pagination,
            'events' => $events,
            'filters' => compact('eventId', 'type'),
        ]);
    }

    public function create(): void
    {
        Auth::requireAdminOrOrganizer();

        $eventId = (int)($_GET['event_id'] ?? 0);
        $events = Auth::isAdmin()
            ? Event::all(['status' => ['po_open', 'public']], 'name ASC')
            : $this->organizerEvents();

        $this->view('admin/ticket_classes/create', [
            'title' => 'Tambah Kelas Tiket',
            'events' => $events,
            'selectedEventId' => $eventId,
        ]);
    }

    public function store(): void
    {
        Auth::requireAdminOrOrganizer();
        
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $seatMode = $_POST['seat_mode'] ?? 'quantity';
        if (!in_array($seatMode, ['quantity', 'seated'], true)) $seatMode = 'quantity';
        $layout = $this->parseLayout($_POST);
        $capacity = $seatMode === 'seated' ? count(TicketClass::expandSeats($layout)) : (int)($_POST['capacity'] ?? 0);

        $data = [
            'event_id' => (int)($_POST['event_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'class_type' => $_POST['class_type'] ?? 'cat',
            'section' => trim($_POST['section'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'benefits' => trim($_POST['benefits'] ?? ''),
            'capacity' => $capacity,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'seat_mode' => $seatMode,
            'seat_layout' => $seatMode === 'seated' ? json_encode($layout) : null,
        ];
        
        $errors = [];
        if (!$data['event_id']) $errors[] = 'Acara wajib dipilih';
        if (!$data['name']) $errors[] = 'Nama kelas tiket wajib diisi';
        if ($data['price'] < 0) $errors[] = 'Harga tidak boleh negatif';
        if ($data['capacity'] <= 0) $errors[] = $seatMode === 'seated' ? 'Layout kursi kosong — isi baris dan kursi per baris' : 'Kapasitas harus lebih dari 0';
        if ($data['event_id'] && !Auth::canManageEvent($data['event_id'])) $errors[] = 'Anda tidak punya akses ke acara ini';

        if ($errors) {
            Flash::error(implode('<br>', $errors));
            setOld($_POST);
            $this->back();
        }

        TicketClass::create($data);
        Flash::success('Kelas tiket berhasil dibuat');
        $this->redirect('/admin/ticket-classes');
    }

    public function edit(int $id): void
    {
        Auth::requireAdminOrOrganizer();

        $tc = TicketClass::find($id);
        if (!$tc || !Auth::canManageEvent((int)$tc->event_id)) {
            $this->abort(404);
        }

        $events = Auth::isAdmin() ? Event::all([], 'name ASC') : $this->organizerEvents();

        $this->view('admin/ticket_classes/edit', [
            'title' => 'Edit Kelas Tiket: ' . $tc->name,
            'ticketClass' => $tc,
            'events' => $events,
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireAdminOrOrganizer();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $tc = TicketClass::find($id);
        if (!$tc || !Auth::canManageEvent((int)$tc->event_id)) {
            $this->abort(404);
        }

        $seatMode = $_POST['seat_mode'] ?? ($tc->seat_mode ?? 'quantity');
        if (!in_array($seatMode, ['quantity', 'seated'], true)) $seatMode = 'quantity';
        $layout = $this->parseLayout($_POST);
        $capacity = $seatMode === 'seated' ? count(TicketClass::expandSeats($layout)) : (int)($_POST['capacity'] ?? 0);

        $data = [
            'event_id' => (int)($_POST['event_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'class_type' => $_POST['class_type'] ?? 'cat',
            'section' => trim($_POST['section'] ?? ''),
            'price' => (float)($_POST['price'] ?? 0),
            'description' => trim($_POST['description'] ?? ''),
            'benefits' => trim($_POST['benefits'] ?? ''),
            'capacity' => $capacity,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'seat_mode' => $seatMode,
            'seat_layout' => $seatMode === 'seated' ? json_encode($layout) : null,
        ];
        
        $errors = [];
        if (!$data['event_id']) $errors[] = 'Acara wajib dipilih';
        if (!$data['name']) $errors[] = 'Nama kelas tiket wajib diisi';
        if ($data['price'] < 0) $errors[] = 'Harga tidak boleh negatif';
        if ($data['capacity'] <= 0) $errors[] = $seatMode === 'seated' ? 'Layout kursi kosong — isi baris dan kursi per baris' : 'Kapasitas harus lebih dari 0';
        if ($data['event_id'] && !Auth::canManageEvent($data['event_id'])) $errors[] = 'Anda tidak punya akses ke acara ini';

        // Never drop seats that already sold tickets
        if ($seatMode === 'seated' && (int)$tc->sold_count > 0) {
            $sold = $tc->soldSeats();
            $missing = array_diff($sold, TicketClass::expandSeats($layout));
            if ($missing) $errors[] = 'Layout menghapus kursi terjual: ' . implode(', ', array_slice($missing, 0, 5));
        }

        if ($errors) {
            Flash::error(implode('<br>', $errors));
            setOld($_POST);
            $this->back();
        }

        $tc->update($data);
        Flash::success('Kelas tiket berhasil diperbarui');
        $this->redirect('/admin/ticket-classes');
    }

    public function destroy(int $id): void
    {
        Auth::requireAdminOrOrganizer();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $tc = TicketClass::find($id);
        if (!$tc || !Auth::canManageEvent((int)$tc->event_id)) {
            $this->abort(404);
        }
        
        // Check if has orders
        $db = \App\Database\Database::getInstance();
        $hasOrders = $db->prepare("SELECT COUNT(*) as c FROM order_items WHERE ticket_class_id = ?");
        $hasOrders->execute([$id]);
        
        if ($hasOrders->fetch()['c'] > 0) {
            Flash::error('Tidak bisa menghapus kelas tiket yang sudah memiliki pesanan');
            $this->back();
        }
        
        $tc->delete();
        Flash::success('Kelas tiket berhasil dihapus');
        $this->redirect('/admin/ticket-classes');
    }

    private function organizerEvents(): array
    {
        $ids = Auth::organizerEventIds();
        if (!$ids) return [];
        $all = Event::all([], 'name ASC');
        return array_values(array_filter($all, fn($e) => in_array((int)$e->id, $ids, true)));
    }

    private function parseLayout(array $post): array
    {
        $rows = array_values(array_filter(array_map(
            fn($r) => strtoupper(trim((string)$r)),
            preg_split('/[\s,]+/', (string)($post['seat_rows'] ?? ''))
        )));
        $cols = max(0, min(100, (int)($post['seat_cols'] ?? 0)));
        $blocked = array_values(array_filter(array_map(
            fn($b) => strtoupper(trim((string)$b)),
            preg_split('/[\s,]+/', (string)($post['seat_blocked'] ?? ''))
        )));
        $prefix = trim((string)($post['seat_prefix'] ?? ''));
        return ['rows' => $rows, 'cols' => $cols, 'blocked' => $blocked, 'prefix' => $prefix];
    }
}