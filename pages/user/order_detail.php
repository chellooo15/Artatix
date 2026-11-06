<div class="container order-detail">
    <nav aria-label="Breadcrumb" class="breadcrumb mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/') ?>" style="color: inherit;">Beranda</a> / 
        <a href="<?= url('/user/orders') ?>" style="color: inherit;">Pesanan Saya</a> / 
        <span>#<?= str_pad($order->id, 8, '0', STR_PAD_LEFT) ?></span>
    </nav>
    
    <div class="show-layout">
        <div>
            <div class="card mb-6">
                <div class="card-header d-flex justify-between items-center flex-wrap gap-2">
                    <h3><?= h($event->name) ?></h3>
                    <span class="badge badge-<?= $event->status === 'po_open' ? 'warning' : ($event->status === 'public' ? 'success' : 'secondary') ?>">
                        <?= eventStatusLabel($event->status) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-4 flex-wrap text-sm text-muted mb-4">
                        <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg><?= formatDate($event->event_date) ?></span>
                        <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.25rem;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg><?= date('H:i', strtotime($event->event_time)) ?></span>
                    </div>
                    
                    <?php if (!empty($event->venue_id)): 
                        $v = \App\Repositories\Venue::find((int)$event->venue_id);
                        if ($v): ?>
                            <div class="d-flex items-center gap-2 text-sm text-muted">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                <span><?= h($v->name) ?>, <?= h($v->address) ?>, <?= h($v->city) ?></span>
                            </div>
                    <?php endif; endif; ?>
                </div>
            </div>
            
            <div class="card mb-6">
                <div class="card-header"><h3>Detail Pesanan</h3></div>
                <div class="card-body">
                    <div class="kv-row mb-3">
                        <span class="text-muted">Nomor Pesanan</span>
                        <span class="font-mono font-semibold">#<?= str_pad($order->id, 8, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="kv-row mb-3">
                        <span class="text-muted">Tanggal Pesan</span>
                        <span><?= formatDateTime($order->created_at) ?></span>
                    </div>
                    <div class="kv-row mb-3">
                        <span class="text-muted">Status</span>
                        <span class="badge badge-<?= match($order->status) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'cancelled', 'expired' => 'danger',
                            default => 'secondary'
                        } ?>">
                            <?= $order->getStatusLabel() ?>
                        </span>
                    </div>
                    <?php if ($order->is_po): ?>
                        <div class="kv-row mb-3">
                            <span class="text-muted">Kode Flash Sale</span>
                            <span class="font-mono"><?= h($order->po_code_used) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="kv-row font-semibold">
                        <span>Total</span>
                        <span><?= formatRupiah($order->total_amount) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="card mb-6">
                <div class="card-header"><h3>Tiket</h3></div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <p class="text-muted text-center" style="padding: 2rem;">Belum ada tiket. Tiket akan muncul setelah pembayaran dikonfirmasi.</p>
                        <?php if ($order->isPending() && $order->canPay()): ?>
                            <form action="<?= url('/checkout/' . $order->id . '/pay') ?>" method="POST" class="text-center mt-4">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-primary btn-lg">Bayar Sekarang</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="grid grid-2 gap-3">
                            <?php foreach ($tickets as $ticket): ?>
                                <div class="card" style="border: 2px solid var(--color-primary); background: var(--color-primary-light);">
                                    <div class="card-body text-center p-3">
                                        <div class="font-mono font-semibold text-lg" style="color: var(--color-primary);"><?= h($ticket->ticket_code) ?></div>
                                        <?php if (!empty($ticket->seat_label)): ?>
                                            <div class="font-bold" style="font-size: 1.25rem;">Kursi <?= h($ticket->seat_label) ?></div>
                                        <?php endif; ?>
                                        <div class="text-sm text-muted mt-1"><?= h($ticket->holder_name) ?></div>
                                        <a href="<?= url('/user/tickets/' . $ticket->ticket_code) ?>" class="btn btn-sm btn-primary mt-2" style="width: 100%;">Lihat Tiket</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header"><h3>Item Pesanan</h3></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table item-table">
                            <thead>
                                <tr>
                                    <th>Kelas Tiket</th>
                                    <th>Tipe</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): 
                                    $tc = $item->ticketClass();
                                ?>
                                    <tr>
                                        <td data-label="Kelas Tiket"><?= h($tc->name) ?></td>
                                        <td data-label="Tipe"><span class="badge badge-primary"><?= $tc->getTypeLabel() ?></span></td>
                                        <td data-label="Harga"><?= formatRupiah($item->unit_price) ?></td>
                                        <td data-label="Qty"><?= $item->quantity ?></td>
                                        <td data-label="Subtotal" class="font-semibold"><?= formatRupiah($item->subtotal) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="sticky-top order-summary" style="top: 90px;">
            <div class="card">
                <div class="card-header">
                    <h3>Ringkasan Pembayaran</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-between mb-2">
                        <span>Subtotal</span>
                        <span><?= formatRupiah($order->total_amount) ?></span>
                    </div>
                    <div class="d-flex justify-between mb-2">
                        <span>Biaya Admin</span>
                        <span>Rp 0</span>
                    </div>
                    <hr style="margin: 0.75rem 0; border-color: var(--color-border);">
                    <div class="d-flex justify-between font-semibold text-lg">
                        <span>Total</span>
                        <span><?= formatRupiah($order->total_amount) ?></span>
                    </div>
                    
                    <?php if ($order->isPending()): ?>
                        <?php if ($order->canPay()): ?>
                            <div class="alert alert-warning mt-4" style="font-size: 0.875rem;">
                                <strong>⏰ Pesanan belum dibayar</strong>
                                <div class="mt-1">Batas pembayaran: <strong><?= formatDateTime($order->expires_at) ?></strong></div>
                                <div class="mt-2 text-center">
                                    <div class="text-sm text-muted">Sisa waktu</div>
                                    <div class="countdown" data-countdown="<?= h($order->expires_at) ?>" style="font-size:1.5rem; font-weight:700;"><?= h($order->expires_at) ?></div>
                                </div>
                            </div>
                            <form action="<?= url('/checkout/' . $order->id . '/pay') ?>" method="POST">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-primary btn-block btn-lg mt-4" data-pay-btn>Bayar Sekarang</button>
                            </form>
                            <form action="<?= url('/user/orders/' . $order->id . '/cancel') ?>" method="POST" style="margin-top: 0.75rem;" onsubmit="return confirm('Batalkan pesanan ini?')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-outline btn-block btn-danger">Batalkan Pesanan</button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-error mt-4" style="font-size: 0.875rem;">
                                <strong>❌ Pesanan Kedaluwarsa</strong>
                                <div class="mt-1">Batas pembayaran telah lewat. Pesanan dibatalkan otomatis.</div>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($order->isPaid()): ?>
                        <div class="alert alert-success mt-4" style="font-size: 0.875rem;">
                            <strong>✅ Pembayaran Dikonfirmasi</strong>
                            <div class="mt-1">Tiket Anda sudah tersedia di atas.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>