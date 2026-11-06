<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\Venue;

class AdminVenueController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        
        $query = Venue::query()->orderBy('name', 'ASC');
        
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        $pagination = $query->paginate(20, $page);
        
        $this->view('admin/venues/index', [
            'title' => 'Kelola Venue',
            'venues' => $pagination['data'],
            'pagination' => $pagination,
            'filters' => compact('search'),
        ]);
    }
    
    public function create(): void
    {
        Auth::requireAdmin();
        
        $this->view('admin/venues/create', [
            'title' => 'Tambah Venue',
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
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'capacity' => (int)($_POST['capacity'] ?? 0),
        ];
        
        $errors = [];
        if (!$data['name']) $errors[] = 'Nama venue wajib diisi';
        if (!$data['city']) $errors[] = 'Kota wajib diisi';
        
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $upload = $this->handleImageUpload($_FILES['image']);
            if ($upload['success']) {
                $data['image'] = $upload['filename'];
            } else {
                $errors[] = $upload['error'];
            }
        }
        
        if ($errors) {
            Flash::error(implode('<br>', $errors));
            setOld($_POST);
            $this->back();
        }
        
        Venue::create($data);
        Flash::success('Venue berhasil ditambahkan');
        $this->redirect('/admin/venues');
    }
    
    public function edit(int $id): void
    {
        Auth::requireAdmin();
        
        $venue = Venue::find($id);
        if (!$venue) {
            $this->abort(404);
        }
        
        $this->view('admin/venues/edit', [
            'title' => 'Edit Venue: ' . $venue->name,
            'venue' => $venue,
        ]);
    }
    
    public function update(int $id): void
    {
        Auth::requireAdmin();
        
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }
        
        $venue = Venue::find($id);
        if (!$venue) {
            $this->abort(404);
        }
        
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'capacity' => (int)($_POST['capacity'] ?? 0),
        ];
        
        $errors = [];
        if (!$data['name']) $errors[] = 'Nama venue wajib diisi';
        if (!$data['city']) $errors[] = 'Kota wajib diisi';
        
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $upload = $this->handleImageUpload($_FILES['image']);
            if ($upload['success']) {
                $data['image'] = $upload['filename'];
            } else {
                $errors[] = $upload['error'];
            }
        }
        
        if ($errors) {
            Flash::error(implode('<br>', $errors));
            setOld($_POST);
            $this->back();
        }
        
        $venue->update($data);
        Flash::success('Venue berhasil diperbarui');
        $this->redirect('/admin/venues');
    }
    
    public function destroy(int $id): void
    {
        Auth::requireAdmin();
        
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->back();
        }
        
        $venue = Venue::find($id);
        if (!$venue) {
            $this->abort(404);
        }
        
        // Check if has events
        $db = \App\Database\Database::getInstance();
        $hasEvents = $db->prepare("SELECT COUNT(*) as c FROM events WHERE venue_id = ?");
        $hasEvents->execute([$id]);
        
        if ($hasEvents->fetch()['c'] > 0) {
            Flash::error('Tidak bisa menghapus venue yang sudah memiliki acara');
            $this->back();
        }
        
        $venue->delete();
        Flash::success('Venue berhasil dihapus');
        $this->redirect('/admin/venues');
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
        
        $filename = 'venue_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath = $GLOBALS['config']['upload']['path'] . '/' . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['success' => false, 'error' => 'Gagal menyimpan file'];
        }
        
        return ['success' => true, 'filename' => $filename];
    }
}