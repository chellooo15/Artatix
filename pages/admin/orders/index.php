<div class="container">
    <div class="d-flex justify-between items-center mb-6">
        <h1>Kelola Pesanan</h1>
    </div>
    
    <div class="card mb-6">
        <div class="card-body">
            <form action="<?= url('/admin/orders') ?>" method="GET" class="grid grid-5 gap-4" style="align-items: end;">
                <div class="form-group" style="margin: 0; flex: 1;">
                    <label class="form-label" for="search">Cari</label>
                    <input type="text" id="search" name="search" class="form-input" 
                           placeholder="ID Pesanan, Nama, Email..." value="<?= h($filters['search'] ?? '') ?>">
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Bayar</option>
                        <option value="paid" <?= ($filters['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Lunas</option>
                        <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                        <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Kedaluwarsa</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="event_id">Acara</label>
                    <select id="event_id" name="event_id" class="form-select">
                        <option value="">Semua Acara</option>
                        <?php require __DIR__ . '/../../../components/event_options.php'; ?>
                    </select>
                </div>
                
                <div class="d-flex gap-2" style="margin: 0;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?= url('/admin/orders') ?>" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: var(--color-text-muted);" opacity="0.5">
                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3>Tidak Ada Pesanan</h3>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Acara</th>
                            <th>Pembeli</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>FS</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="font-mono">#<?= str_pad($order['id'], 8, '0', STR_PAD_LEFT) ?></td>
                                <td><?= h($order['event_name']) ?></td>
                                <td>
                                    <div><?= h($order['user_name']) ?></div>
                                    <div class="text-sm text-muted"><?= h($order['user_email']) ?></div>
                                </td>
                                <td><?= formatDateTime($order['created_at']) ?></td>
                                <td class="font-semibold"><?= formatRupiah($order['total_amount']) ?></td>
                                <td>
                                    <span class="badge badge-<?= match($order['status']) {
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'cancelled', 'expired' => 'danger',
                                        default => 'secondary'
                                    } ?>">
                                        <?= orderStatusLabel($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= $order['is_po'] ? '<span class="badge badge-primary">FS</span>' : '<span class="text-muted">-</span>' ?></td>
                                <td>
                                    <a href="<?= url('/admin/orders/' . $order['id']) ?>" class="btn btn-sm btn-outline">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pagination['last_page'] > 1): ?>
                <div class="card-footer d-flex justify-between items-center flex-wrap gap-2">
                    <span class="text-sm text-muted">
                        Menampilkan <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?>-<?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> dari <?= $pagination['total'] ?> pesanan
                    </span>
                    <nav class="d-flex gap-2" aria-label="Pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= url('/admin/orders?page=' . ($pagination['current_page'] - 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="<?= url('/admin/orders?page=' . $i . '&' . http_build_query(array_filter($filters))) ?>" 
                               class="btn btn-sm btn-<?= $i === $pagination['current_page'] ? 'primary' : 'outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/admin/orders?page=' . ($pagination['current_page'] + 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Selanjutnya</a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>