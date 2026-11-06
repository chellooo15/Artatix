<div class="container" style="max-width: 600px;">
    <div class="card">
        <div class="card-body text-center" style="padding: 3rem 2rem;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #d1fae5; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--color-success); font-size: 2rem;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            
            <h2 style="margin-bottom: 0.5rem;">Pembelian Berhasil!</h2>
            <p class="text-muted mb-4">Pesanan Anda telah dibuat dan menunggu pembayaran.</p>
            
            <div class="alert alert-info text-left mb-4" style="text-align: left;">
                <div class="font-semibold mb-2">Detail Pesanan</div>
                <div class="d-flex justify-between mb-1">
                    <span>Nomor Pesanan</span>
                    <span class="font-mono">#<?= str_pad($order->id, 8, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="d-flex justify-between mb-1">
                    <span>Acara</span>
                    <span><?= h($event->name) ?></span>
                </div>
                <div class="d-flex justify-between mb-1">
                    <span>Tanggal</span>
                    <span><?= formatDate($event->event_date) ?></span>
                </div>
                <?php foreach ($items as $item): 
                    $tc = $item->ticketClass();
                ?>
                    <div class="d-flex justify-between mb-1">
                        <span><?= h($tc->name) ?> (<?= $item->quantity ?>x)</span>
                        <span><?= formatRupiah($item->subtotal) ?></span>
                    </div>
                <?php endforeach; ?>
                <hr style="margin: 0.75rem 0; border-color: var(--color-border);">
                <div class="d-flex justify-between font-semibold">
                    <span>Total</span>
                    <span><?= formatRupiah($order->total_amount) ?></span>
                </div>
                <?php if ($order->is_po): ?>
                    <div class="mt-2 p-2" style="background: #fef3c7; border-radius: var(--radius); font-size: 0.875rem; color: #92400e;">
                        <strong>Flash Sale:</strong> Kode Flash Sale <code><?= h($order->po_code_used) ?></code> telah digunakan
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($order->isPending()): ?>
                <div class="alert alert-warning mb-4 text-center" style="font-size:0.9375rem;">
                    <div class="text-sm text-muted">Sisa waktu pembayaran</div>
                    <div class="countdown" data-countdown="<?= h($order->expires_at) ?>" style="font-size:1.75rem; color: var(--color-warning);"><?= formatDateTime($order->expires_at) ?></div>
                    <div class="text-sm text-muted mt-1">Kedaluwarsa: <?= formatDateTime($order->expires_at) ?></div>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= url('/user/orders') ?>" class="btn btn-outline flex-1">Lihat Pesanan</a>
                    <form action="<?= url('/checkout/' . $order->id . '/pay') ?>" method="POST" style="flex: 1;">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-primary btn-lg btn-block" data-pay-btn>Bayar Sekarang</button>
                    </form>
                </div>
                <p class="text-sm text-muted mt-3">
                    Pesanan akan kedaluwarsa pada <strong><?= formatDateTime($order->expires_at) ?></strong>.
                    Silakan selesaikan pembayaran sebelum waktu habis.
                </p>
            <?php else: ?>
                <a href="<?= url('/user/orders/' . $order->id) ?>" class="btn btn-primary btn-lg">Lihat Tiket</a>
            <?php endif; ?>
        </div>
    </div>
</div>