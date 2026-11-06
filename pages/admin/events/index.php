<div class="container">
    <div class="d-flex justify-between items-center mb-6">
        <h1><?= h($title ?? 'Kelola Acara') ?></h1>
        <?php if (Auth::isAdmin()): ?>
        <a href="<?= url('/admin/events/create') ?>" class="btn btn-primary">Tambah Acara</a>
        <?php endif; ?>
    </div>
    
    <div class="card mb-6">
        <div class="card-body">
            <form action="<?= url('/admin/events') ?>" method="GET" class="grid grid-4 gap-4" style="align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="search">Cari Acara</label>
                    <input type="text" id="search" name="search" class="form-input" 
                           placeholder="Nama acara..." value="<?= h($filters['search'] ?? '') ?>">
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="po_open" <?= ($filters['status'] ?? '') === 'po_open' ? 'selected' : '' ?>>Flash Sale</option>
                        <option value="public" <?= ($filters['status'] ?? '') === 'public' ? 'selected' : '' ?>>For Sale</option>
                        <option value="sold_out" <?= ($filters['status'] ?? '') === 'sold_out' ? 'selected' : '' ?>>Sold Out</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>
                
                <div class="d-flex gap-2" style="margin: 0;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?= url('/admin/events') ?>" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($events)): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: var(--color-text-muted);" opacity="0.5">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle>
            </svg>
            <h3>Belum Ada Acara</h3>
            <p class="text-muted"><?= Auth::isOrganizer() ? 'Belum ada acara yang mengundang Anda.' : 'Mulai dengan membuat acara pertama Anda.' ?></p>
            <?php if (Auth::isAdmin()): ?>
            <a href="<?= url('/admin/events/create') ?>" class="btn btn-primary mt-4">Buat Acara</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Banner</th>
                            <th>Nama Acara</th>
                            <th>Tanggal & Waktu</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Kuota Flash Sale</th>
                            <th>Kelas Tiket</th>
                            <?php if (Auth::isAdmin()): ?>
                            <th>Organizer</th>
                            <th>Invite</th>
                            <?php endif; ?>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): 
                            $isObj = is_object($event);
                            $id = $isObj ? $event->id : $event['id'];
                            $name = $isObj ? $event->name : $event['name'];
                            $banner = $isObj ? $event->banner_image : $event['banner_image'];
                            $event_date = $isObj ? $event->event_date : $event['event_date'];
                            $event_time = $isObj ? $event->event_time : $event['event_time'];
                            $status = $isObj ? $event->status : $event['status'];
                            $po_quota = $isObj ? $event->po_quota : $event['po_quota'];
                            $venue_id = $isObj ? $event->venue_id : $event['venue_id'];
                            
                            $tcCount = 0;
                            $venue = null;
                            if ($isObj) {
                                $tcCount = count($event->ticketClasses());
                                $venue = $event->venue();
                            } else {
                                $db = \App\Database\Database::getInstance();
                                $stmt = $db->prepare("SELECT COUNT(*) as c FROM ticket_classes WHERE event_id = ?");
                                $stmt->execute([$id]);
                                $tcCount = $stmt->fetch()['c'];
                                
                                if ($venue_id) {
                                    $stmt = $db->prepare("SELECT name FROM venues WHERE id = ?");
                                    $stmt->execute([$venue_id]);
                                    $venue = $stmt->fetch();
                                }
                            }
                        ?>
                            <tr>
                                <td>
                                    <?php if ($banner): ?>
                                        <img src="<?= asset('assets/uploads/' . $banner) ?>" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 40px; background: var(--color-bg); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 0.75rem;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="font-medium"><?= h($name) ?></div>
                                    <div class="text-sm text-muted">#<?= $id ?></div>
                                </td>
                                <td>
                                    <div><?= formatDate($event_date) ?></div>
                                    <div class="text-sm text-muted"><?= date('H:i', strtotime($event_time)) ?></div>
                                </td>
                                <td><?= $venue ? h($venue['name'] ?? $venue->name ?? '') : '<span class="text-muted">-</span>' ?></td>
                                <td>
                                    <span class="badge badge-<?= match($status) {
                                        'draft' => 'secondary',
                                        'po_open' => 'warning',
                                        'public' => 'success',
                                        'sold_out' => 'danger',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    } ?>">
                                        <?= eventStatusLabel($status) ?>
                                    </span>
                                </td>
                                <td><?= $po_quota > 0 ? $po_quota : '<span class="text-muted">-</span>' ?></td>
                                <td><?= $tcCount ?> kelas</td>
                                <?php if (Auth::isAdmin()): ?>
                                <td><?= ($event['organizer_count'] ?? 0) > 0 ? '<span class="badge badge-primary">' . (int)$event['organizer_count'] . ' org</span>' : '<span class="text-muted">-</span>' ?></td>
                                <td><?= ($event['invite_count'] ?? 0) > 0 ? '<span class="badge badge-warning">' . (int)$event['invite_count'] . ' aktif</span>' : '<span class="text-muted">-</span>' ?></td>
                                <?php endif; ?>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?= url('/admin/events/' . $id . '/edit') ?>" class="btn btn-sm btn-outline" title="Edit">Edit</a>
                                        <?php if (Auth::isAdmin()): ?>
                                        <form action="<?= url('/admin/events/' . $id . '/delete') ?>" method="POST" onsubmit="return confirm('Hapus acara ini? Tindakan ini tidak bisa dibatalkan.')">
                                            <?= csrfField() ?>
                                            <button type="submit" class="btn btn-sm btn-outline btn-danger" title="Hapus">Hapus</button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pagination['last_page'] > 1): ?>
                <div class="card-footer d-flex justify-between items-center flex-wrap gap-2">
                    <span class="text-sm text-muted">
                        Menampilkan <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?>-<?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> dari <?= $pagination['total'] ?> acara
                    </span>
                    <nav class="d-flex gap-2" aria-label="Pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= url('/admin/events?page=' . ($pagination['current_page'] - 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="<?= url('/admin/events?page=' . $i . '&' . http_build_query(array_filter($filters))) ?>" 
                               class="btn btn-sm btn-<?= $i === $pagination['current_page'] ? 'primary' : 'outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/admin/events?page=' . ($pagination['current_page'] + 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Selanjutnya</a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>