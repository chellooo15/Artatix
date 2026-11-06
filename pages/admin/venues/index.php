<div class="container">
    <div class="d-flex justify-between items-center mb-6">
        <h1>Kelola Venue</h1>
        <a href="<?= url('/admin/venues/create') ?>" class="btn btn-primary">Tambah Venue</a>
    </div>
    
    <div class="card mb-6">
        <div class="card-body">
            <form action="<?= url('/admin/venues') ?>" method="GET" class="grid grid-3 gap-4" style="align-items: end;">
                <div class="form-group" style="margin: 0; flex: 1;">
                    <label class="form-label" for="search">Cari Venue</label>
                    <input type="text" id="search" name="search" class="form-input" 
                           placeholder="Nama venue, kota..." value="<?= h($filters['search'] ?? '') ?>">
                </div>
                
                <div class="d-flex gap-2" style="margin: 0;">
                    <button type="submit" class="btn btn-primary">Cari</button>
                    <a href="<?= url('/admin/venues') ?>" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($venues)): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: var(--color-text-muted);" opacity="0.5">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle>
            </svg>
            <h3>Belum Ada Venue</h3>
            <p class="text-muted">Tambahkan venue untuk acara Anda.</p>
            <a href="<?= url('/admin/venues/create') ?>" class="btn btn-primary mt-4">Tambah Venue</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Venue</th>
                            <th>Alamat</th>
                            <th>Kota</th>
                            <th>Kapasitas</th>
                            <th>Acara</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($venues as $venue): 
                            $db = \App\Database\Database::getInstance();
                            $eventCount = $db->prepare("SELECT COUNT(*) as c FROM events WHERE venue_id = ?");
                            $eventCount->execute([$venue['id']]);
                            $count = $eventCount->fetch()['c'];
                        ?>
                            <tr>
                                <td>
                                    <?php if ($venue['image']): ?>
                                        <img src="<?= asset('assets/uploads/' . $venue['image']) ?>" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 40px; background: var(--color-bg); border-radius: 4px; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 0.75rem;">No Image</div>
                                    <?php endif; ?>
                                </td>
                                <td class="font-medium"><?= h($venue['name']) ?></td>
                                <td><?= h($venue['address'] ?? '-') ?></td>
                                <td><?= h($venue['city']) ?></td>
                                <td><?= $venue['capacity'] > 0 ? number_format($venue['capacity']) : '<span class="text-muted">-</span>' ?></td>
                                <td><?= $count ?> acara</td>
                                <td>
                                    <a href="<?= url('/admin/venues/' . $venue['id'] . '/edit') ?>" class="btn btn-sm btn-outline">Edit</a>
                                    <form action="<?= url('/admin/venues/' . $venue['id'] . '/delete') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Hapus venue ini?')">
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
                        Menampilkan <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?>-<?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> dari <?= $pagination['total'] ?> venue
                    </span>
                    <nav class="d-flex gap-2" aria-label="Pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= url('/admin/venues?page=' . ($pagination['current_page'] - 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="<?= url('/admin/venues?page=' . $i . '&' . http_build_query(array_filter($filters))) ?>" 
                               class="btn btn-sm btn-<?= $i === $pagination['current_page'] ? 'primary' : 'outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/admin/venues?page=' . ($pagination['current_page'] + 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Selanjutnya</a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>