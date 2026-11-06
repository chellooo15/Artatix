<div class="container" style="max-width: 800px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/admin') ?>" style="color: inherit;">Dashboard</a> / 
        <a href="<?= url('/admin/events') ?>" style="color: inherit;">Acara</a> / 
        <span>Edit: <?= h($event->name) ?></span>
    </nav>
    
    <div class="d-flex justify-between items-center mb-4">
        <h1>Edit Acara</h1>
        <a href="<?= url('/events/' . $event->id) ?>" target="_blank" class="btn btn-outline btn-sm">Lihat di Frontend</a>
    </div>
    
    <div class="card mb-6">
        <div class="card-header"><h3>Informasi Utama</h3></div>
        <div class="card-body">
            <?php $canEdit = $canEditInfo ?? true; $dis = $canEdit ? '' : 'disabled'; ?>
            <?php if (!$canEdit): ?>
                <div class="alert alert-warning mb-4">Anda hanya punya akses kelola (tiket, Flash Sale, pesanan). Edit info acara dinonaktifkan oleh admin.</div>
            <?php endif; ?>
            <form action="<?= url('/admin/events/' . $event->id) ?>" method="POST" enctype="multipart/form-data" data-validate>
                <?= csrfField() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Nama Acara <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-input" required <?= $dis ?>
                           value="<?= h($event->name) ?>" placeholder="Contoh: Konser Superstar 2024">
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-textarea" rows="4" <?= $dis ?>
                              placeholder="Deskripsi acara, lineup artis, dll."><?= h($event->description ?? '') ?></textarea>
                </div>

                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="event_date">Tanggal Acara <span class="text-danger">*</span></label>
                        <input type="date" id="event_date" name="event_date" class="form-input" required <?= $dis ?>
                               value="<?= $event->event_date ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="event_time">Waktu Acara <span class="text-danger">*</span></label>
                        <input type="time" id="event_time" name="event_time" class="form-input" required <?= $dis ?>
                               value="<?= date('H:i', strtotime($event->event_time)) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="venue_id">Venue</label>
                    <select id="venue_id" name="venue_id" class="form-select" <?= $dis ?>>
                        <option value="">-- Pilih Venue --</option>
                        <?php foreach ($venues as $v): ?>
                            <option value="<?= $v->id ?>" <?= (int)$event->venue_id === (int)$v->id ? 'selected' : '' ?>>
                                <?= h($v->name) ?> - <?= h($v->city) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Banner Saat Ini</label>
                    <?php if ($event->banner_image): ?>
                        <div class="d-flex items-center gap-3 mb-2">
                            <img src="<?= asset('assets/uploads/' . $event->banner_image) ?>" alt="" style="width: 120px; height: 68px; object-fit: cover; border-radius: 4px;">
                            <div>
                                <div class="text-sm"><?= h($event->banner_image) ?></div>
                                <?php if ($canEdit): ?>
                                <label class="form-label" style="margin: 0; font-weight: 400; font-size: 0.8125rem;">
                                    <input type="checkbox" name="remove_banner" value="1"> Hapus banner
                                </label>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-muted">Belum ada banner</span>
                    <?php endif; ?>
                </div>

                <?php if ($canEdit): ?>
                <div class="form-group">
                    <label class="form-label" for="banner_image">Ganti Banner (Opsional)</label>
                    <input type="file" id="banner_image" name="banner_image" class="form-input" accept="image/jpeg,image/png,image/webp">
                    <p class="form-help">Format: JPG, PNG, WebP. Maksimal 5MB. Akan mengganti banner lama.</p>
                </div>
                <?php endif; ?>

                <hr style="margin: 1.5rem 0; border-color: var(--color-border);">
                <h4 class="mb-4">Pengaturan Flash Sale & For Sale</h4>

                <div class="form-group">
                    <label class="form-label" for="status">Status Acara</label>
                    <select id="status" name="status" class="form-select" <?= $dis ?>>
                        <option value="draft" <?= $event->status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="po_open" <?= $event->status === 'po_open' ? 'selected' : '' ?>>Flash Sale Dibuka</option>
                        <option value="public" <?= $event->status === 'public' ? 'selected' : '' ?>>For Sale</option>
                        <option value="cancelled" <?= $event->status === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                </div>

                <div class="grid grid-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="po_quota">Kuota Flash Sale</label>
                        <input type="number" id="po_quota" name="po_quota" class="form-input" min="0" <?= $dis ?>
                               value="<?= $event->po_quota ?>">
                        <p class="form-help">Jumlah tiket yang dialokasikan untuk Flash Sale</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="po_open_at">Flash Sale Dibuka</label>
                        <input type="datetime-local" id="po_open_at" name="po_open_at" class="form-input" <?= $dis ?>
                               value="<?= $event->po_open_at ? date('Y-m-d\TH:i', strtotime($event->po_open_at)) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="public_open_at">Mulai For Sale</label>
                    <input type="datetime-local" id="public_open_at" name="public_open_at" class="form-input" <?= $dis ?>
                           value="<?= $event->public_open_at ? date('Y-m-d\TH:i', strtotime($event->public_open_at)) : '' ?>">
                    <p class="form-help">Kosongkan = langsung terbuka setelah Flash Sale selesai/dibatalkan</p>
                </div>

                <div class="d-flex gap-2 mt-6">
                    <a href="<?= url('/admin/events') ?>" class="btn btn-outline">Batal</a>
                    <?php if ($canEdit): ?>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-6">
        <div class="card-header d-flex justify-between items-center">
            <h3>Kelas Tiket</h3>
            <a href="<?= url('/admin/ticket-classes/create?event_id=' . $event->id) ?>" class="btn btn-sm btn-primary">Tambah Kelas Tiket</a>
        </div>
        <div class="card-body">
            <?php if (empty($ticketClasses)): ?>
                <p class="text-muted text-center" style="padding: 2rem;">Belum ada kelas tiket. Tambahkan minimal satu kelas tiket.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Seksi</th>
                                <th>Harga</th>
                                <th>Kapasitas</th>
                                <th>Terjual</th>
                                <th>Tersedia</th>
                                <th>Status</th>
                                <th style="width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ticketClasses as $tc): 
                                $available = max(0, $tc->capacity - $tc->sold_count);
                            ?>
                                <tr>
                                    <td><?= h($tc->name) ?></td>
                                    <td><span class="badge badge-primary"><?= $tc->getTypeLabel() ?></span></td>
                                    <td><?= h($tc->section ?? '-') ?></td>
                                    <td><?= formatRupiah($tc->price) ?></td>
                                    <td><?= number_format($tc->capacity) ?></td>
                                    <td><?= number_format($tc->sold_count) ?></td>
                                    <td class="<?= $available <= 0 ? 'text-danger font-semibold' : '' ?>"><?= number_format($available) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $tc->is_active ? 'success' : 'secondary' ?>">
                                            <?= $tc->is_active ? 'Aktif' : 'Nonaktif' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= url('/admin/ticket-classes/' . $tc->id . '/edit') ?>" class="btn btn-sm btn-outline">Edit</a>
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
    <div class="card mb-6">
        <div class="card-header"><h3>Organizer Acara</h3></div>
        <div class="card-body">
            <form action="<?= url('/admin/events/' . $event->id . '/organizers') ?>" method="POST" class="d-flex gap-2 flex-wrap mb-4" style="align-items: end;">
                <?= csrfField() ?>
                <div class="form-group" style="margin:0; flex:1; min-width:220px;">
                    <label class="form-label" for="organizer_email">Invite via Email</label>
                    <input type="email" id="organizer_email" name="organizer_email" class="form-input" placeholder="organizer@mail.com" required>
                    <p class="form-help">Email harus sudah terdaftar. Role user otomatis jadi organizer.</p>
                </div>
                <label class="form-label" style="margin:0; font-weight:400; font-size:0.875rem;">
                    <input type="checkbox" name="can_edit_info" value="1" style="width:auto; margin-right:0.5rem;"> Boleh edit info acara
                </label>
                <button type="submit" class="btn btn-primary">Tambah Organizer</button>
            </form>

            <?php if (empty($organizers)): ?>
                <p class="text-muted">Belum ada organizer. Tambahkan via email di atas atau bagikan link invite di bawah.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Nama</th><th>Email</th><th>Edit Info</th><th style="width:180px;">Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach ($organizers as $o): ?>
                            <tr>
                                <td class="font-medium"><?= h($o['name']) ?></td>
                                <td class="text-sm"><?= h($o['email']) ?></td>
                                <td>
                                    <form action="<?= url('/admin/events/' . $event->id . '/organizers/' . $o['id'] . '/permission') ?>" method="POST" style="display:inline;">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="can_edit_info" value="<?= $o['can_edit_info'] ? '0' : '1' ?>">
                                        <button type="submit" class="btn btn-sm <?= $o['can_edit_info'] ? 'btn-success' : 'btn-outline' ?>">
                                            <?= $o['can_edit_info'] ? 'Ya' : 'Tidak' ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form action="<?= url('/admin/events/' . $event->id . '/organizers/' . $o['id'] . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Hapus organizer ini dari acara?')">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-header"><h3>Link Invite Organizer</h3></div>
        <div class="card-body">
            <form action="<?= url('/admin/events/' . $event->id . '/invites') ?>" method="POST" class="grid grid-3 gap-4 mb-4" style="align-items: end;">
                <?= csrfField() ?>
                <label class="form-label" style="margin:0; font-weight:400; font-size:0.875rem;">
                    <input type="checkbox" name="can_edit_info" value="1" style="width:auto; margin-right:0.5rem;"> Boleh edit info acara
                </label>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="max_uses">Maks. Klaim</label>
                    <input type="number" id="max_uses" name="max_uses" class="form-input" value="5" min="1" max="100">
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" for="expires_days">Berlaku (hari)</label>
                    <input type="number" id="expires_days" name="expires_days" class="form-input" value="7" min="1" max="90">
                </div>
                <div><button type="submit" class="btn btn-primary">Buat Link Invite</button></div>
            </form>
            <p class="form-help mb-4">Default: 5x klaim, 7 hari, tanpa izin edit info. Bagikan link ke calon organizer — mereka daftar/login lalu klaim.</p>

            <?php if (!empty($invites)): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Link</th><th>Izin</th><th>Terpakai</th><th>Kedaluarsa</th><th>Status</th><th style="width:120px;">Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach ($invites as $inv): ?>
                            <tr>
                                <td class="font-mono text-sm" style="word-break: break-all;"><?= url('/invite/' . $inv->token) ?></td>
                                <td><?= $inv->can_edit_info ? '<span class="badge badge-warning">Edit info</span>' : '<span class="badge badge-primary">Kelola</span>' ?></td>
                                <td><?= (int)$inv->used_count ?>/<?= (int)$inv->max_uses ?></td>
                                <td class="text-sm"><?= $inv->expires_at ? formatDateTime($inv->expires_at) : '-' ?></td>
                                <td><?= $inv->is_active ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-secondary">Nonaktif</span>' ?></td>
                                <td>
                                    <?php if ($inv->is_active): ?>
                                    <form action="<?= url('/admin/events/' . $event->id . '/invites/' . $inv->id . '/revoke') ?>" method="POST" style="display:inline;">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-outline btn-danger">Revoke</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex gap-2">
        <?php if (Auth::isAdmin()): ?>
        <form action="<?= url('/admin/events/' . $event->id . '/delete') ?>" method="POST" onsubmit="return confirm('PERHATIAN: Menghapus acara akan menghapus semua kelas tiket dan data terkait. Lanjutkan?')">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-danger">Hapus Acara</button>
        </form>
        <?php endif; ?>
        <a href="<?= url('/admin/events') ?>" class="btn btn-outline">Kembali ke Daftar</a>
    </div>
</div>