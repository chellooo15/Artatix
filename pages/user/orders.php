<div class="container">
    <div class="d-flex justify-between items-center mb-6">
        <h1>Pesanan Saya</h1>
    </div>
    
    <?php if (empty($pagination['data'])): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; color: var(--color-text-muted);" opacity="0.5">
                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3>Belum Ada Pesanan</h3>
            <p class="text-muted">Anda belum memiliki pesanan tiket.</p>
            <a href="<?= url('/events') ?>" class="btn btn-primary mt-4">Cari Acara</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nomor Pesanan</th>
                            <th>Acara</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagination['data'] as $order): 
                            $event = $order->event();
                            $items = $order->items();
                            $totalQty = array_sum(array_map(fn($it)=>(int)($it->quantity ?? 0), $items));
                            if (!$event) continue;
                        ?>
                            <tr data-href="<?= url('/user/orders/' . $order->id) ?>" tabindex="0" aria-label="Lihat detail pesanan #<?= str_pad($order->id, 8, '0', STR_PAD_LEFT) ?>">
                                <td class="font-mono">#<?= str_pad($order->id, 8, '0', STR_PAD_LEFT) ?></td>
                                <td>
                                    <div class="font-medium"><?= h($event->name ?? '—') ?></div>
                                    <?php if (!empty($event->venue_id)) {
                                        $venueTmp = \App\Repositories\Venue::find((int)$event->venue_id);
                                        if ($venueTmp) echo '<div class="text-sm text-muted">'.h($venueTmp->name).'</div>';
                                    } ?>
                                </td>
                                <td><?= formatDate($event->event_date) ?></td>
                                <td><?= $totalQty ?> tiket</td>
                                <td class="font-semibold"><?= formatRupiah($order->total_amount) ?></td>
                                <td>
                                    <span class="badge badge-<?= match($order->status) {
                                        'paid' => 'success',
                                        'pending' => 'warning',
                                        'cancelled', 'expired' => 'danger',
                                        default => 'secondary'
                                    } ?>">
                                        <?= $order->getStatusLabel() ?>
                                    </span>
                                    <?php if ($order->is_po): ?>
                                        <span class="badge badge-primary ms-1">FS</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('/user/orders/' . $order->id) ?>" class="btn btn-sm btn-outline">Detail</a>
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
                            <a href="<?= url('/user/orders?page=' . ($pagination['current_page'] - 1)) ?>" class="btn btn-sm btn-outline">Sebelumnya</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++): ?>
                            <a href="<?= url('/user/orders?page=' . $i) ?>" 
                               class="btn btn-sm btn-<?= $i === $pagination['current_page'] ? 'primary' : 'outline' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
                            <a href="<?= url('/user/orders?page=' . ($pagination['current_page'] + 1)) ?>" class="btn btn-sm btn-outline">Selanjutnya</a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>