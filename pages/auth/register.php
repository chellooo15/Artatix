<div class="container" style="max-width: 440px;">
    <div class="card">
        <div class="card-header">
            <h2 class="text-center">Daftar Artatix</h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/register') ?>" method="POST" data-validate>
                <?= csrfField() ?>
                <?php if (!empty($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?= h($_GET['redirect']) ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" class="form-input" required 
                           value="<?= old('name') ?>" placeholder="Nama lengkap" autocomplete="name">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           value="<?= old('email') ?>" placeholder="email@contoh.com" autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon <span class="text-muted">(opsional)</span></label>
                    <input type="tel" id="phone" name="phone" class="form-input" 
                           value="<?= old('phone') ?>" placeholder="08xx-xxxx-xxxx" autocomplete="tel">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required 
                           placeholder="Minimal 8 karakter" autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password_confirm">Konfirmasi Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-input" required 
                           placeholder="Ulangi password" autocomplete="new-password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-4">Daftar</button>
            </form>
            
            <p class="text-center mt-4 text-muted">
                Sudah punya akun? <a href="<?= url('/login') ?>">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>