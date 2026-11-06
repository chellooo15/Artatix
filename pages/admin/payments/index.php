<div class="container">
    <div class="d-flex justify-between items-center mb-6">
        <h1>Verifikasi Pembayaran</h1>
    </div>
    
    <div class="card mb-6">
        <div class="card-body">
            <form action="<?= url('/admin/payments') ?>" method="GET" class="grid grid-4 gap-4" style="align-items: end;">
                <div class="form-group" style="margin: 0; flex: 1;">
                    <label class="form-label" for="event_id">Acara</label>
                    <select id="event_id" name="event_id" class="form-select">
                        <option value="">Semua Acara</option>
                        <?php require __DIR__ . '/../../../components/event_options.php'; ?>
                    </select>
                </div>

                <div class="form-group" style="margin: 0; flex: 1;">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Semua</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                        <option value="approved" <?= ($filters['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Diverifikasi</option>
                        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                
                <div class="d-flex gap-2" style="margin: 0;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="<?= url('/admin/payments') ?>" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($payments)): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: var(--color-text-muted);" opacity="0.5">
                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path><path d="M3 20h18"></path><path d="M5 14a2 2 0 0 1 0 4h14"></path>
            </svg>
            <h3>Tidak Ada Pembayaran</h3>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pesanan</th>
                            <th>Acara</th>
                            <th>Pembeli</th>
                            <th>Metode</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td class="font-mono">#<?= str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                <td>#<?= str_pad($payment['order_id'], 8, '0', STR_PAD_LEFT) ?></td>
                                <td><?= h($payment['event_name']) ?></td>
                                <td><?= h($payment['user_name']) ?></td>
                                <td><?= h($payment['method']) ?></td>
                                <td class="font-semibold"><?= formatRupiah($payment['amount']) ?></td>
                                <td>
                                    <span class="badge badge-<?= match($payment['status']) {
                                        'approved' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    } ?>">
                                        <?= paymentStatusLabel($payment['status']) ?>
                                    </span>
                                </td>
                                <td><?= formatDateTime($payment['created_at']) ?></td>
                                <td>
                                    <a href="<?= url('/admin/orders/' . $payment['order_id']) ?>" class="btn btn-sm btn-outline">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($pagination['last_page'] > 1): ?>
                <div class="card-footer d-flex justify-between items-center flex-wrap gap-2">
                    <span class="text-sm text-muted">
                        Menampilkan <?= (($pagination['current_page'] - 1) * $pagination['per_page']) + 1 ?>-<?= min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) ?> dari <?= $pagination['total'] ?> pembayaran
                    </span>
                    <nav class="d-flex gap-2" aria-label="Pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="<?= url('/admin/payments?page=' . ($pagination['current_page'] - 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="<?= url('/admin/payments?page=' . $i . '&' . http_build_query(array_filter($filters))) ?>" 
                               class="btn btn-sm btn-<?= $i === $pagination['current_page'] ? 'primary' : 'outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/admin/payments?page=' . ($pagination['current_page'] + 1) . '&' . http_build_query(array_filter($filters))) ?>" class="btn btn-sm btn-outline">Selanjutnya</a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>