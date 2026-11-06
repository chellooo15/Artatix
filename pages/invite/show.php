<div class="container" style="max-width: 560px;">
    <div class="card">
        <div class="card-body text-center" style="padding: 3rem 2rem;">
            <?php if (!$usable): ?>
                <div style="width: 80px; height: 80px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--color-danger); font-size: 2rem;">✕</div>
                <h2 style="margin-bottom: 0.5rem;">Undangan Tidak Berlaku</h2>
                <p class="text-muted mb-4"><?= h($reason) ?></p>
                <a href="<?= url('/') ?>" class="btn btn-outline">Kembali ke Beranda</a>
            <?php else: ?>
                <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--color-primary-light); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: var(--color-primary); font-size: 2rem;">✉</div>
                <h2 style="margin-bottom: 0.5rem;">Undangan Organizer</h2>
                <p class="text-muted mb-4">Anda diundang menjadi <strong>organizer</strong> untuk acara:</p>
                <div class="alert alert-info mb-4" style="text-align: left;">
                    <div class="font-semibold text-lg"><?= h($event->name) ?></div>
                    <div class="text-sm mt-1"><?= formatDate($event->event_date) ?> · <?= date('H:i', strtotime($event->event_time)) ?></div>
                    <div class="text-sm mt-2">
                        Izin: <span class="badge badge-<?= $invite->can_edit_info ? 'warning' : 'primary' ?>">
                            <?= $invite->can_edit_info ? 'Kelola + Edit Info Acara' : 'Kelola Tiket, Flash Sale, Pesanan' ?>
                        </span>
                    </div>
                    <?php if ($invite->expires_at): ?>
                        <div class="text-sm text-muted mt-1">Berlaku sampai <?= formatDateTime($invite->expires_at) ?> · Sisa klaim: <?= max(0, (int)$invite->max_uses - (int)$invite->used_count) ?></div>
                    <?php endif; ?>
                </div>
                <?php if ($alreadyOrganizer): ?>
                    <div class="alert alert-success mb-4">Anda sudah menjadi organizer acara ini.</div>
                    <a href="<?= url('/admin/events') ?>" class="btn btn-primary btn-lg btn-block">Ke Acara Saya</a>
                <?php elseif (Auth::check() && Auth::isAdmin()): ?>
                    <div class="alert alert-info mb-4">Anda login sebagai admin — tidak perlu klaim. Kelola organizer di halaman edit acara.</div>
                    <a href="<?= url('/admin/events/' . $event->id . '/edit') ?>" class="btn btn-primary btn-lg btn-block">Kelola Acara</a>
                <?php elseif (Auth::guest()): ?>
                    <p class="text-muted mb-4">Silakan login dulu (atau daftar bila belum punya akun), lalu kembali ke link ini untuk klaim.</p>
                    <div class="d-flex gap-2">
                        <a href="<?= url('/login?redirect=' . urlencode('/invite/' . $invite->token)) ?>" class="btn btn-outline flex-1">Login</a>
                        <a href="<?= url('/register?redirect=' . urlencode('/invite/' . $invite->token)) ?>" class="btn btn-primary flex-1">Daftar</a>
                    </div>
                <?php else: ?>
                    <form action="<?= url('/invite/' . $invite->token . '/accept') ?>" method="POST">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Terima & Jadi Organizer</button>
                    </form>
                    <p class="text-sm text-muted mt-3">Setelah klaim, Anda hanya bisa mengelola acara ini (tidak bisa buat acara baru).</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
