<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\Event;
use App\Repositories\Venue;
use App\Repositories\TicketClass;
use App\Repositories\User;
use App\Repositories\EventInvite;
use App\Repositories\EventOrganizer;

class AdminEventController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();
        if (!Auth::isAdminOrOrganizer()) {
            Flash::set('error', 'Akses ditolak');
            header('Location: /');
            exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');

        $query = Event::query()->orderBy('created_at', 'DESC');

        if ($status) {
            $query->where('status', '=', $status);
        }

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Organizer sees only invited events
        if (Auth::isOrganizer()) {
            $ids = Auth::organizerEventIds();
            $query->whereIn('id', $ids);
        }

        $pagination = $query->paginate(15, $page);

        // Attach organizer/invite counts for admin list (single extra query per event is fine at 15/page)
        $events = $pagination['data'];
        if (Auth::isAdmin() && $events) {
            $db = \App\Database\Database::getInstance();
            $ids = array_map(fn($e) => (int)$e['id'], $events);
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT event_id, COUNT(*) as c FROM event_organizers WHERE event_id IN ({$ph}) GROUP BY event_id");
            $stmt->execute($ids);
            $orgCounts = [];
            foreach ($stmt->fetchAll() as $r) $orgCounts[(int)$r['event_id']] = (int)$r['c'];
            $stmt = $db->prepare("SELECT event_id, COUNT(*) as c FROM event_invites WHERE event_id IN ({$ph}) AND is_active = 1 GROUP BY event_id");
            $stmt->execute($ids);
            $invCounts = [];
            foreach ($stmt->fetchAll() as $r) $invCounts[(int)$r['event_id']] = (int)$r['c'];
            foreach ($events as &$e) {
                $e['organizer_count'] = $orgCounts[(int)$e['id']] ?? 0;
                $e['invite_count'] = $invCounts[(int)$e['id']] ?? 0;
            }
            unset($e);
            $pagination['data'] = $events;
        }

        $this->view('admin/events/index', [
            'title' => Auth::isOrganizer() ? 'Acara Saya' : 'Kelola Acara',
            'events' => $pagination['data'],
            'pagination' => $pagination,
            'filters' => compact('status', 'search'),
        ]);
    }
    
    public function create(): void
    {
        Auth::requireAdmin();
        
        $venues = Venue::all([], 'name ASC');
        
        $this->view('admin/events/create', [
            'title' => 'Tambah Acara',
            'venues' => $venues,
        ]);
    }
    
    public function store(): void
    {
        Auth::requireAdmin();
        
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'event_date' => $_POST['event_date'] ?? '',
            'event_time' => $_POST['event_time'] ?? '',
            'venue_id' => (int)($_POST['venue_id'] ?? 0) ?: null,
            'status' => $_POST['status'] ?? 'draft',
            'po_quota' => (int)($_POST['po_quota'] ?? 0),
            'po_open_at' => $_POST['po_open_at'] ?: null,
            'public_open_at' => $_POST['public_open_at'] ?: null,
        ];
        
        $errors = [];
        if (!$data['name']) $errors[] = 'Nama acara wajib diisi';
        if (!$data['event_date']) $errors[] = 'Tanggal acara wajib diisi';
        if (!$data['event_time']) $errors[] = 'Waktu acara wajib diisi';
        
        // Handle banner upload
        if (!empty($_FILES['banner_image']['name'])) {
            $upload = $this->handleImageUpload($_FILES['banner_image']);
            if ($upload['success']) {
                $data['banner_image'] = $upload['filename'];
            } else {
                $errors[] = $upload['error'];
            }
        }
        
        if ($errors) {
            Flash::error(implode('<br>', $errors));
            setOld($_POST);
            $this->redirect('/admin/events/create');
        }
        
        $event = Event::create($data);
        Flash::success('Acara berhasil dibuat');
        $this->redirect('/admin/events/' . $event->id . '/edit');
    }
    
    public function edit(int $id): void
    {
        Auth::requireAuth();
        if (!Auth::isAdminOrOrganizer()) {
            Flash::set('error', 'Akses ditolak');
            header('Location: /');
            exit;
        }

        $event = Event::find($id);
        if (!$event || !Auth::canManageEvent($id)) {
            $this->abort(404);
        }

        $venues = Venue::all([], 'name ASC');
        $ticketClasses = $event->ticketClasses();
        $isAdmin = Auth::isAdmin();
        $canEditInfo = Auth::canEditEventInfo($id);
        $organizers = $isAdmin ? $event->organizers() : [];
        $invites = $isAdmin ? EventInvite::forEvent($id) : [];

        $this->view('admin/events/edit', [
            'title' => 'Edit Acara: ' . $event->name,
            'event' => $event,
            'venues' => $venues,
            'ticketClasses' => $ticketClasses,
            'canEditInfo' => $canEditInfo,
            'organizers' => $organizers,
            'invites' => $invites,
        ]);
    }

    public function update(int $id): void
    {
        Auth::requireAuth();
        if (!Auth::isAdminOrOrganizer()) {
            Flash::set('error', 'Akses ditolak');
            header('Location: /');
            exit;
        }

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $event = Event::find($id);
        if (!$event || !Auth::canManageEvent($id)) {
            $this->abort(404);
        }

        if (!Auth::canEditEventInfo($id)) {
            Flash::error('Anda tidak punya izin edit info acara ini');
            $this->redirect('/admin/events/' . $id . '/edit');
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'event_date' => $_POST['event_date'] ?? '',
            'event_time' => $_POST['event_time'] ?? '',
            'venue_id' => (int)($_POST['venue_id'] ?? 0) ?: null,
            'status' => $_POST['status'] ?? 'draft',
            'po_quota' => (int)($_POST['po_quota'] ?? 0),
            'po_open_at' => $_POST['po_open_at'] ?: null,
            'public_open_at' => $_POST['public_open_at'] ?: null,
        ];
        
        $errors = [];
        if (!$data['name']) $errors[] = 'Nama acara wajib diisi';
        if (!$data['event_date']) $errors[] = 'Tanggal acara wajib diisi';
        if (!$data['event_time']) $errors[] = 'Waktu acara wajib diisi';
        
        // Handle banner upload
        if (!empty($_FILES['banner_image']['name'])) {
            $upload = $this->handleImageUpload($_FILES['banner_image']);
            if ($upload['success']) {
                $data['banner_image'] = $upload['filename'];
            } else {
                $errors[] = $upload['error'];
            }
        }
        
        if ($errors) {
            Flash::error(implode('<br>', $errors));
            setOld($_POST);
            $this->back();
        }
        
        $event->update($data);
        Flash::success('Acara berhasil diperbarui');
        $this->redirect('/admin/events/' . $event->id . '/edit');
    }
    
    public function destroy(int $id): void
    {
        Auth::requireAdmin();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $event = Event::find($id);
        if (!$event) {
            $this->abort(404);
        }

        $event->delete();
        Flash::success('Acara berhasil dihapus');
        $this->redirect('/admin/events');
    }

    public function addOrganizer(int $id): void
    {
        Auth::requireAdmin();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $event = Event::find($id);
        if (!$event) {
            $this->abort(404);
        }

        $email = strtolower(trim($_POST['organizer_email'] ?? ''));
        $canEdit = !empty($_POST['can_edit_info']);

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Email organizer tidak valid');
            $this->redirect('/admin/events/' . $id . '/edit');
        }

        $target = User::findByEmail($email);

        if (!$target) {
            Flash::error('Email belum terdaftar. Minta user daftar dulu, atau gunakan link invite.');
            $this->redirect('/admin/events/' . $id . '/edit');
        }

        if ($target->role === 'admin') {
            Flash::error('Admin tidak perlu diinvite sebagai organizer');
            $this->redirect('/admin/events/' . $id . '/edit');
        }

        if ($target->role === 'user') {
            $target->update(['role' => 'organizer']);
        }

        EventOrganizer::add($id, (int)$target->id, $canEdit);
        Flash::success('Organizer ditambahkan: ' . $target->name);
        $this->redirect('/admin/events/' . $id . '/edit');
    }

    public function removeOrganizer(int $id, int $userId): void
    {
        Auth::requireAdmin();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $event = Event::find($id);
        if (!$event) {
            $this->abort(404);
        }

        if (EventOrganizer::remove($id, $userId)) {
            Flash::success('Organizer dihapus dari acara ini');
        } else {
            Flash::error('Organizer tidak ditemukan');
        }
        $this->redirect('/admin/events/' . $id . '/edit');
    }

    public function setOrganizerPermission(int $id, int $userId): void
    {
        Auth::requireAdmin();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $event = Event::find($id);
        if (!$event) {
            $this->abort(404);
        }

        $canEdit = !empty($_POST['can_edit_info']);
        EventOrganizer::setCanEdit($id, $userId, $canEdit);
        Flash::success($canEdit ? 'Izin edit info diberikan' : 'Izin edit info dicabut');
        $this->redirect('/admin/events/' . $id . '/edit');
    }

    public function createInvite(int $id): void
    {
        Auth::requireAdmin();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $event = Event::find($id);
        if (!$event) {
            $this->abort(404);
        }

        $canEdit = !empty($_POST['can_edit_info']);
        $maxUses = max(1, min(100, (int)($_POST['max_uses'] ?? 5)));
        $days = max(1, min(90, (int)($_POST['expires_days'] ?? 7)));

        $invite = EventInvite::create([
            'event_id' => $id,
            'token' => bin2hex(random_bytes(32)),
            'can_edit_info' => $canEdit ? 1 : 0,
            'max_uses' => $maxUses,
            'used_count' => 0,
            'expires_at' => date('Y-m-d H:i:s', strtotime("+{$days} days")),
            'created_by' => Auth::user()['id'],
            'is_active' => 1,
        ]);

        Flash::success('Link invite dibuat: ' . url('/invite/' . $invite->token));
        $this->redirect('/admin/events/' . $id . '/edit');
    }

    public function revokeInvite(int $id, int $inviteId): void
    {
        Auth::requireAdmin();

        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }

        $invite = EventInvite::find($inviteId);
        if (!$invite || (int)$invite->event_id !== $id) {
            $this->abort(404);
        }

        $invite->update(['is_active' => 0]);
        Flash::success('Link invite dinonaktifkan');
        $this->redirect('/admin/events/' . $id . '/edit');
    }
    
    private function handleImageUpload(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload gagal'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'Ukuran file maksimal 5MB'];
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowedTypes)) {
            return ['success' => false, 'error' => 'Format file tidak didukung (JPG, PNG, WebP)'];
        }
        
        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };
        
        $filename = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $GLOBALS['config']['upload']['path'] . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Gagal menyimpan file'];
        }
        
        return ['success' => true, 'filename' => $filename];
    }
}