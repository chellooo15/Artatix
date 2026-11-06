<div class="container">
    <div class="d-flex justify-between items-center mb-6">
        <h1><?= h($title ?? 'Dashboard Admin') ?></h1>
        <span class="text-muted">Selamat datang, <?= h(Auth::user()['name']) ?><?= !empty($isOrganizer) ? ' (Organizer)' : '' ?></span>
    </div>
    
    <div class="grid grid-4 gap-4 mb-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-between items-start">
                    <div>
                        <div class="text-sm text-muted">Total Acara</div>
                        <div class="font-bold text-2xl" style="color: var(--color-primary);"><?= number_format($stats['total_events']) ?></div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: var(--radius); background: var(--color-primary-light); display: flex; align-items: center; justify-content: center; color: var(--color-primary);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-between items-start">
                    <div>
                        <div class="text-sm text-muted">Acara Aktif</div>
                        <div class="font-bold text-2xl" style="color: var(--color-success);"><?= number_format($stats['active_events']) ?></div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: var(--radius); background: #d1fae5; display: flex; align-items: center; justify-content: center; color: var(--color-success);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-between items-start">
                    <div>
                        <div class="text-sm text-muted">Total Pesanan</div>
                        <div class="font-bold text-2xl" style="color: var(--color-secondary);"><?= number_format($stats['total_orders']) ?></div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: var(--radius); background: #dbeafe; display: flex; align-items: center; justify-content: center; color: var(--color-secondary);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-between items-start">
                    <div>
                        <div class="text-sm text-muted">Total Pendapatan</div>
                        <div class="font-bold text-2xl" style="color: var(--color-warning);"><?= formatRupiah($stats['total_revenue']) ?></div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: var(--radius); background: #fef3c7; display: flex; align-items: center; justify-content: center; color: var(--color-warning);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-2 gap-6" style="grid-template-columns: 1fr 1fr;">
        <div class="card">
            <div class="card-header d-flex justify-between items-center">
                <h3>Pesanan Terbaru</h3>
                <a href="<?= url('/admin/orders') ?>" class="btn btn-sm btn-outline">Lihat Semua</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($recentOrders)): ?>
                    <p class="text-muted text-center" style="padding: 2rem;">Belum ada pesanan</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Acara</th>
                                    <th>Pembeli</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $ro): ?>
                                    <tr>
                                        <td class="font-mono">#<?= str_pad($ro['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                        <td><?= h($ro['event_name']) ?></td>
                                        <td><?= h($ro['user_name']) ?></td>
                                        <td><?= formatRupiah($ro['total_amount']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= match($ro['status']) {
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'cancelled', 'expired' => 'danger',
                                                default => 'secondary'
                                            } ?>">
                                                <?= orderStatusLabel($ro['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-between items-center">
                <h3>List Acara</h3>
                <a href="<?= url('/admin/events') ?>" class="btn btn-sm btn-outline">Lihat Semua</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($eventList)): ?>
                    <p class="text-muted text-center" style="padding: 2rem;">Belum ada acara</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Acara</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Kuota Flash Sale</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventList as $ue): ?>
                                    <tr>
                                        <td class="font-medium"><?= h($ue['name']) ?></td>
                                        <td><?= formatDate($ue['event_date']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= match($ue['status']) {
                                                'draft' => 'secondary',
                                                'po_open' => 'warning',
                                                'public' => 'success',
                                                'sold_out' => 'danger',
                                                default => 'secondary'
                                            } ?>">
                                                <?= eventStatusLabel($ue['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= $ue['po_quota'] > 0 ? $ue['po_quota'] : '<span class="text-muted">-</span>' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="grid grid-2 gap-6 mt-6" style="grid-template-columns: 1fr 1fr;">
        <div class="card">
            <div class="card-header d-flex justify-between items-center">
                <h3>Kode Flash Sale Terbaru</h3>
                <a href="<?= url('/admin/po-codes') ?>" class="btn btn-sm btn-outline">Lihat Semua</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($recentPOs)): ?>
                    <p class="text-muted text-center" style="padding: 2rem;">Belum ada kode Flash Sale</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Kode Flash Sale</th>
                                    <th>Acara</th>
                                    <th>Sisa</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentPOs as $po):
                                    $remaining = max(0, (int)$po['max_quota'] - (int)$po['used_count']);
                                    $isExpired = !empty($po['expires_at']) && strtotime($po['expires_at']) < time();
                                    $isActive = (int)$po['is_active'] && !$isExpired;
                                ?>
                                    <tr>
                                        <td class="font-mono font-medium"><?= h($po['code']) ?></td>
                                        <td><?= h($po['event_name']) ?></td>
                                        <td class="<?= $remaining <= 0 ? 'text-danger font-semibold' : '' ?>"><?= $remaining ?></td>
                                        <td>
                                            <span class="badge badge-<?= $isActive ? 'success' : 'secondary' ?>">
                                                <?= $isActive ? 'Aktif' : 'Nonaktif' ?>
                                            </span>
                                            <?php if ($isExpired): ?><span class="badge badge-danger ms-1">Kadaluarsa</span><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (Auth::isAdmin()): ?>
        <div class="card">
            <div class="card-header d-flex justify-between items-center">
                <h3>Venue</h3>
                <a href="<?= url('/admin/venues') ?>" class="btn btn-sm btn-outline">Lihat Semua</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($venueList)): ?>
                    <p class="text-muted text-center" style="padding: 2rem;">Belum ada venue</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table" style="margin: 0;">
                            <thead>
                                <tr>
                                    <th>Nama Venue</th>
                                    <th>Kapasitas</th>
                                    <th>Acara</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($venueList as $vn): ?>
                                    <tr>
                                        <td>
                                            <div class="font-medium"><?= h($vn['name']) ?></div>
                                            <div class="text-sm text-muted"><?= h($vn['city']) ?></div>
                                        </td>
                                        <td><?= (int)$vn['capacity'] > 0 ? number_format((int)$vn['capacity']) : '<span class="text-muted">-</span>' ?></td>
                                        <td><?= (int)$vn['event_count'] ?> acara</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-2 gap-6 mt-6" style="grid-template-columns: 1fr 1fr;">
        <div class="card">
            <div class="card-header"><h3>Pesanan Perlu Tindakan</h3></div>
            <div class="card-body">
                <div class="d-flex gap-4 flex-wrap">
                    <a href="<?= url('/admin/orders?status=pending') ?>" class="btn btn-warning flex-1">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Menunggu Pembayaran (<?= $stats['pending_orders'] ?>)
                    </a>
                    <a href="<?= url('/admin/payments?status=pending') ?>" class="btn btn-primary flex-1">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        Verifikasi Pembayaran (<?= $stats['pending_payments'] ?>)
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header"><h3>Menu Cepat</h3></div>
            <div class="card-body">
                <?php if (!empty($isOrganizer)): ?>
                    <div class="grid grid-2 gap-3">
                        <a href="<?= url('/admin/events') ?>" class="btn btn-outline text-left"><div>Acara Saya</div></a>
                        <a href="<?= url('/admin/ticket-classes') ?>" class="btn btn-outline text-left"><div>Kelas Tiket</div></a>
                        <a href="<?= url('/admin/orders') ?>" class="btn btn-outline text-left"><div>Pesanan</div></a>
                        <a href="<?= url('/admin/po-codes') ?>" class="btn btn-outline text-left"><div>Kode Flash Sale</div></a>
                    </div>
                <?php else: ?>
                <div class="grid grid-2 gap-3">
                    <a href="<?= url('/admin/events/create') ?>" class="btn btn-outline text-left">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <div>Tambah Acara</div>
                    </a>
                    <a href="<?= url('/admin/ticket-classes/create') ?>" class="btn btn-outline text-left">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        <div>Tambah Kelas Tiket</div>
                    </a>
                    <a href="<?= url('/admin/venues/create') ?>" class="btn btn-outline text-left">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <div>Tambah Venue</div>
                    </a>
                    <a href="<?= url('/admin/po-codes/create') ?>" class="btn btn-outline text-left">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        <div>Buat Kode Flash Sale</div>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>