<div class="container">
    <div class="d-flex justify-between items-center mb-6">
        <h1>Kelola Kelas Tiket</h1>
        <a href="<?= url('/admin/ticket-classes/create') ?>" class="btn btn-primary">Tambah Kelas Tiket</a>
    </div>
    
    <div class="card mb-6">
        <div class="card-body">
            <form action="<?= url('/admin/ticket-classes') ?>" method="GET" class="grid grid-4 gap-4" style="align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="event_id">Acara</label>
                    <select id="event_id" name="event_id" class="form-select">
                        <option value="">Semua Acara</option>
                        <?php require __DIR__ . '/../../../components/event_options.php'; ?>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="type">Tipe</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="cat" <?= ($filters['type'] ?? '') === 'cat' ? 'selected' : '' ?>>CAT (Duduk)</option>
                        <option value="standing" <?= ($filters['type'] ?? '') === 'standing' ? 'selected' : '' ?>>Festival/Standing</option>
                        <option value="vip" <?= ($filters['type'] ?? '') === 'vip' ? 'selected' : '' ?>>VIP</option>
                        <option value="vvip" <?= ($filters['type'] ?? '') === 'vvip' ? 'selected' : '' ?>>VVIP</option>
                    </select>
                </div>
                
                <div class="d-flex gap-2" style="margin: 0;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?= url('/admin/ticket-classes') ?>" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($ticketClasses)): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: var(--color-text-muted);" opacity="0.5">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
            <h3>Belum Ada Kelas Tiket</h3>
            <p class="text-muted">Buat kelas tiket untuk acara Anda.</p>
            <a href="<?= url('/admin/ticket-classes/create') ?>" class="btn btn-primary mt-4">Tambah Kelas Tiket</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Acara</th>
                            <th>Nama Kelas</th>
                            <th>Tipe</th>
                            <th>Seksi</th>
                            <th>Harga</th>
                            <th>Kapasitas</th>
                            <th>Terjual</th>
                            <th>Tersedia</th>
                            <th>Status</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ticketClasses as $tc): 
                            $available = max(0, $tc['capacity'] - $tc['sold_count']);
                        ?>
                            <tr>
                                <td><?= h($tc['event_name']) ?></td>
                                <td class="font-medium"><?= h($tc['name']) ?></td>
                                <td><span class="badge badge-primary"><?= classTypeLabel($tc['class_type']) ?></span></td>
                                <td><?= h($tc['section'] ?? '-') ?></td>
                                <td><?= formatRupiah($tc['price']) ?></td>
                                <td><?= number_format($tc['capacity']) ?></td>
                                <td><?= number_format($tc['sold_count']) ?></td>
                                <td class="<?= $available <= 0 ? 'text-danger font-semibold' : '' ?>"><?= number_format($available) ?></td>
                                <td>
                                    <span class="badge badge-<?= $tc['is_active'] ? 'success' : 'secondary' ?>">
                                        <?= $tc['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('/admin/ticket-classes/' . $tc['id'] . '/edit') ?>" class="btn btn-sm btn-outline">Edit</a>
                                    <form action="<?= url('/admin/ticket-classes/' . $tc['id'] . '/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Hapus kelas tiket ini?')">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pagination['last_page'] > 1): ?>
                <div class="card-footer d-flex justify-between items-center flex-wrap gap-2">
                    <span class="text-sm text-muted">
                        Menampilkan <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?>-<?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> dari <?= $pagination['total'] ?> kelas tiket
                    </span>
                    <nav class="d-flex gap-2" aria-label="Pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= url('/admin/ticket-classes?page=' . ($pagination['current_page'] - 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="<?= url('/admin/ticket-classes?page=' . $i . '&' . http_build_query(array_filter($filters))) ?>" 
                               class="btn btn-sm btn-<?= $i === $pagination['current_page'] ? 'primary' : 'outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/admin/ticket-classes?page=' . ($pagination['current_page'] + 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Selanjutnya</a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>