<div class="container">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/admin') ?>" style="color: inherit;">Dashboard</a> / 
        <a href="<?= url('/admin/orders') ?>" style="color: inherit;">Pesanan</a> / 
        <span>#<?= str_pad($order['id'], 8, '0', STR_PAD_LEFT) ?></span>
    </nav>
    
    <div class="grid grid-2 gap-6" style="grid-template-columns: 1fr 420px; align-items: start;">
        <div>
            <div class="card mb-6">
                <div class="card-header d-flex justify-between items-center flex-wrap gap-2">
                    <h3><?= h($order['event_name']) ?></h3>
                    <span class="badge badge-<?= $order['is_po'] ? 'primary' : 'secondary' ?>">
                        <?= $order['is_po'] ? 'Flash Sale' : 'Reguler' ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-between mb-2">
                        <span class="text-muted">Nomor Pesanan</span>
                        <span class="font-mono font-semibold">#<?= str_pad($order['id'], 8, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span class="text-muted">Tanggal Pesan</span>
                        <span><?= formatDateTime($order['created_at']) ?></span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge badge-<?= match($order['status']) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'cancelled', 'expired' => 'danger',
                            default => 'secondary'
                        } ?>">
                            <?= orderStatusLabel($order['status']) ?>
                        </span>
                    </div>
                    <?php if ($order['is_po']): ?>
                        <div class="d-flex justify-between mb-2">
                            <span class="text-muted">Kode Flash Sale</span>
                            <span class="font-mono"><?= h($order['po_code_used']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-between font-semibold">
                        <span>Total</span>
                        <span><?= formatRupiah($order['total_amount']) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card mb-6">
                <div class="card-header"><h3>Pembeli</h3></div>
                <div class="card-body">
                    <div class="d-flex justify-between mb-2">
                        <span class="text-muted">Nama</span>
                        <span><?= h($order['user_name']) ?></span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span class="text-muted">Email</span>
                        <span><?= h($order['user_email']) ?></span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span class="text-muted">Telepon</span>
                        <span><?= h($order['user_phone'] ?? '-') ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card mb-6">
                <div class="card-header"><h3>Item Pesanan</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kelas Tiket</th>
                                    <th>Tipe</th>
                                    <th>Seksi</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= h($item['class_name']) ?></td>
                                        <td><span class="badge badge-primary"><?= classTypeLabel($item['class_type']) ?></span></td>
                                        <td><?= h($item['section'] ?? '-') ?></td>
                                        <td><?= formatRupiah($item['unit_price']) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="font-semibold"><?= formatRupiah($item['subtotal']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><h3>Tiket Terbit</h3></div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <p class="text-muted text-center" style="padding: 2rem;">Belum ada tiket terbit untuk pesanan ini.</p>
                    <?php else: ?>
                        <div class="grid grid-2 gap-3">
                            <?php foreach ($tickets as $ticket): ?>
                                <div class="card" style="border: 2px solid var(--color-primary); background: var(--color-primary-light);">
                                    <div class="card-body text-center p-3">
                                        <div class="font-mono font-semibold text-lg" style="color: var(--color-primary);"><?= h($ticket['ticket_code']) ?></div>
                                        <div class="text-sm text-muted mt-1"><?= h($ticket['holder_name']) ?></div>
                                        <span class="badge badge-<?= $ticket['is_checked_in'] ? 'success' : 'secondary' ?> mt-2">
                                            <?= $ticket['is_checked_in'] ? 'Sudah Check-in' : 'Belum Check-in' ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="sticky-top" style="top: 90px;">
            <div class="card mb-4">
                <div class="card-header"><h3>Pembayaran</h3></div>
                <div class="card-body">
                    <?php if ($payment): ?>
                        <div class="d-flex justify-between mb-2">
                            <span class="text-muted">Metode</span>
                            <span><?= h($payment['method']) ?></span>
                        </div>
                        <div class="d-flex justify-between mb-2">
                            <span class="text-muted">Jumlah</span>
                            <span><?= formatRupiah($payment['amount']) ?></span>
                        </div>
                        <div class="d-flex justify-between mb-2">
                            <span class="text-muted">Status</span>
                            <span class="badge badge-<?= match($payment['status']) {
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'secondary'
                            } ?>">
                                <?= paymentStatusLabel($payment['status']) ?>
                            </span>
                        </div>
                        
                        <?php if ($payment['proof_image']): ?>
                            <div class="mb-3">
                                <label class="form-label">Bukti Pembayaran</label>
                                <a href="<?= asset('assets/uploads/' . $payment['proof_image']) ?>" target="_blank">
                                    <img src="<?= asset('assets/uploads/' . $payment['proof_image']) ?>" alt="Bukti pembayaran" style="max-width: 100%; border-radius: var(--radius); border: 1px solid var(--color-border);">
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($payment['status'] === 'pending'): ?>
                            <div class="d-flex gap-2">
                                <form action="<?= url('/admin/orders/' . $order['id'] . '/verify-payment') ?>" method="POST">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                    <button type="submit" class="btn btn-success flex-1" onclick="return confirm('Verifikasi & approve pembayaran ini?')">Approve</button>
                                </form>
                                <form action="<?= url('/admin/orders/' . $order['id'] . '/verify-payment') ?>" method="POST">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">
                                    <button type="submit" class="btn btn-danger flex-1" onclick="return confirm('Tolak pembayaran ini?')">Tolak</button>
                                </form>
                            </div>
                        <?php elseif ($payment['verified_by']): ?>
                            <div class="text-sm text-muted mt-3">
                                Diverifikasi oleh admin ID: <?= $payment['verified_by'] ?> pada <?= formatDateTime($payment['verified_at']) ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center text-muted" style="padding: 2rem;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; opacity: 0.5;">
                                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"></path><path d="M3 20h18"></path><path d="M5 14a2 2 0 0 1 0 4h14"></path>
                            </svg>
                            <p>Belum ada data pembayaran</p>
                            <p class="text-sm">User belum upload bukti transfer</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><h3>Aksi Cepat</h3></div>
                <div class="card-body">
                    <?php if ($order['status'] === 'pending'): ?>
                        <form action="<?= url('/admin/orders/' . $order['id'] . '/cancel') ?>" method="POST" onsubmit="return confirm('Batalkan pesanan ini?')">
                            <?= csrfField() ?>
                            <button type="submit" class="btn btn-danger btn-block mb-2">Batalkan Pesanan</button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="<?= url('/admin/orders') ?>" class="btn btn-outline btn-block">Kembali ke Daftar</a>
                </div>
            </div>
        </div>
    </div>
</div>