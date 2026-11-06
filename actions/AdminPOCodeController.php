<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\POAccess;
use App\Repositories\Event;

class AdminPOCodeController extends Controller
{
    public function index(): void
    {
        Auth::requireAdminOrOrganizer();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $eventId = (int)($_GET['event_id'] ?? 0);

        if (Auth::isOrganizer()) {
            $ids = Auth::organizerEventIds();
            if ($eventId && !in_array($eventId, $ids, true)) {
                $eventId = 0;
            }
        }

        $query = POAccess::query()
            ->join('events', 'po_accesses.event_id', '=', 'events.id')
            ->join('users', 'po_accesses.created_by', '=', 'users.id')
            ->select('po_accesses.*, events.name as event_name, users.name as creator_name')
            ->orderBy('po_accesses.created_at', 'DESC');

        if (Auth::isOrganizer()) {
            $query->whereIn('po_accesses.event_id', Auth::organizerEventIds());
        }

        if ($eventId) {
            $query->where('po_accesses.event_id', '=', $eventId);
        }

        $pagination = $query->paginate(20, $page);
        $events = Auth::isAdmin() ? Event::all([], 'name ASC') : $this->organizerEvents();

        $this->view('admin/po_codes/index', [
            'title' => 'Kelola Kode Flash Sale',
            'poCodes' => $pagination['data'],
            'pagination' => $pagination,
            'events' => $events,
            'filters' => compact('eventId'),
        ]);
    }

    public function create(): void
    {
        Auth::requireAdminOrOrganizer();

        $events = Auth::isAdmin()
            ? Event::all(['status' => ['draft', 'po_open']], 'name ASC')
            : array_values(array_filter(
                $this->organizerEvents(),
                fn($e) => in_array($e->status, ['draft', 'po_open'], true)
            ));

        $this->view('admin/po_codes/create', [
            'title' => 'Buat Kode Flash Sale',
            'events' => $events,
        ]);
    }

    public function store(): void
    {
        Auth::requireAdminOrOrganizer();
        
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }
        
        $data = [
            'event_id' => (int)($_POST['event_id'] ?? 0),
            'code' => strtoupper(trim($_POST['code'] ?? '')),
            'max_quota' => (int)($_POST['max_quota'] ?? 1),
            'expires_at' => $_POST['expires_at'] ?: null,
            'created_by' => Auth::user()['id'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        
        $errors = [];
        if (!$data['event_id']) $errors[] = 'Acara wajib dipilih';
        if (!$data['code']) $errors[] = 'Kode Flash Sale wajib diisi';
        if ($data['max_quota'] <= 0) $errors[] = 'Kuota maksimal harus lebih dari 0';
        if ($data['event_id'] && !Auth::canManageEvent($data['event_id'])) $errors[] = 'Anda tidak punya akses ke acara ini';

        // Check if code already exists
        if ($data['code']) {
            $existing = POAccess::first(['code' => $data['code']]);
            if ($existing) {
                $errors[] = 'Kode Flash Sale sudah digunakan';
            }
        }

        if ($errors) {
            Flash::error(implode('<br>', $errors));
            setOld($_POST);
            $this->back();
        }

        POAccess::create($data);
        Flash::success('Kode Flash Sale berhasil dibuat');
        $this->redirect('/admin/po-codes');
    }

    public function destroy(int $id): void
    {
        Auth::requireAdminOrOrganizer();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $poCode = POAccess::find($id);
        if (!$poCode || !Auth::canManageEvent((int)$poCode->event_id)) {
            $this->abort(404);
        }

        $poCode->delete();
        Flash::success('Kode Flash Sale berhasil dihapus');
        $this->redirect('/admin/po-codes');
    }

    private function organizerEvents(): array
    {
        $ids = Auth::organizerEventIds();
        if (!$ids) return [];
        $all = Event::all([], 'name ASC');
        return array_values(array_filter($all, fn($e) => in_array((int)$e->id, $ids, true)));
    }
}