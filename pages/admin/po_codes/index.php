<div class="container">
    <div class="d-flex justify-between items-center mb-6">
        <h1>Kelola Kode Flash Sale</h1>
        <a href="<?= url('/admin/po-codes/create') ?>" class="btn btn-primary">Buat Kode Flash Sale</a>
    </div>
    
    <div class="card mb-6">
        <div class="card-body">
            <form action="<?= url('/admin/po-codes') ?>" method="GET" class="grid grid-3 gap-4" style="align-items: end;">
                <div class="form-group" style="margin: 0; flex: 1;">
                    <label class="form-label" for="event_id">Acara</label>
                    <select id="event_id" name="event_id" class="form-select">
                        <option value="">Semua Acara</option>
                        <?php require __DIR__ . '/../../../components/event_options.php'; ?>
                    </select>
                </div>
                
                <div class="d-flex gap-2" style="margin: 0;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?= url('/admin/po-codes') ?>" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($poCodes)): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: var(--color-text-muted);" opacity="0.5">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
            <h3>Belum Ada Kode Flash Sale</h3>
            <p class="text-muted">Buat kode Flash Sale untuk memberikan akses beli tiket sebelum dibuka umum.</p>
            <a href="<?= url('/admin/po-codes/create') ?>" class="btn btn-primary mt-4">Buat Kode Flash Sale</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kode Flash Sale</th>
                            <th>Acara</th>
                            <th>Kuota Maks</th>
                            <th>Terpakai</th>
                            <th>Sisa</th>
                            <th>Kadaluarsa</th>
                            <th>Dibuat Oleh</th>
                            <th>Status</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($poCodes as $po): 
                            $remaining = $po['max_quota'] - $po['used_count'];
                            $isExpired = $po['expires_at'] && strtotime($po['expires_at']) < time();
                        ?>
                            <tr>
                                <td class="font-mono font-medium"><?= h($po['code']) ?></td>
                                <td><?= h($po['event_name']) ?></td>
                                <td><?= $po['max_quota'] ?></td>
                                <td><?= $po['used_count'] ?></td>
                                <td class="<?= $remaining <= 0 ? 'text-danger font-semibold' : '' ?>"><?= $remaining ?></td>
                                <td>
                                    <?php if ($po['expires_at']): ?>
                                        <span class="<?= $isExpired ? 'text-danger' : '' ?>">
                                            <?= formatDateTime($po['expires_at']) ?>
                                            <?php if ($isExpired): ?><span class="badge badge-danger ms-1">Kadaluarsa</span><?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak terbatas</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($po['creator_name']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $po['is_active'] && !$isExpired ? 'success' : 'secondary' ?>">
                                        <?= $po['is_active'] && !$isExpired ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="<?= url('/admin/po-codes/' . $po['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Hapus kode Flash Sale ini?')">
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
                        Menampilkan <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?>-<?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> dari <?= $pagination['total'] ?> kode Flash Sale
                    </span>
                    <nav class="d-flex gap-2" aria-label="Pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= url('/admin/po-codes?page=' . ($pagination['current_page'] - 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="<?= url('/admin/po-codes?page=' . $i . '&' . http_build_query(array_filter($filters))) ?>" 
                               class="btn btn-sm btn-<?= $i === $pagination['current_page'] ? 'primary' : 'outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/admin/po-codes?page=' . ($pagination['current_page'] + 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Selanjutnya</a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>