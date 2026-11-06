<div class="container" style="max-width: 640px;">
    <nav aria-label="Breadcrumb" class="mb-4" style="font-size: 0.875rem; color: var(--color-text-muted);">
        <a href="<?= url('/') ?>" style="color: inherit;">Beranda</a> /
        <span>Pengaturan Akun</span>
    </nav>

    <h1 class="mb-6">Pengaturan Akun</h1>

    <div class="card mb-6">
        <div class="card-header"><h3>Profil</h3></div>
        <div class="card-body">
            <form action="<?= url('/user/settings') ?>" method="POST" data-validate>
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-input" required
                           value="<?= h($user['name'] ?? '') ?>" autocomplete="name">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" class="form-input" value="<?= h($user['email'] ?? '') ?>" disabled>
                    <p class="form-help">Email tidak dapat diubah.</p>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon <span class="text-muted">(opsional)</span></label>
                    <input type="tel" id="phone" name="phone" class="form-input"
                           value="<?= h($user['phone'] ?? '') ?>" placeholder="08xx-xxxx-xxxx" autocomplete="tel">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Profil</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Ganti Password</h3></div>
        <div class="card-body">
            <form action="<?= url('/user/settings/password') ?>" method="POST" data-validate>
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label" for="current_password">Password Lama</label>
                    <input type="password" id="current_password" name="current_password" class="form-input" required
                           placeholder="••••••••" autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password Baru</label>
                    <input type="password" id="password" name="password" class="form-input" required
                           placeholder="Minimal 8 karakter" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirm">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" required
                           placeholder="Ulangi password baru" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">Ubah Password</button>
            </form>
        </div>
    </div>
</div>
