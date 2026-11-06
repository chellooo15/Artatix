<div class="container" style="max-width: 440px;">
    <div class="card">
        <div class="card-header">
            <h2 class="text-center">Masuk ke Artatix</h2>
        </div>
        <div class="card-body">
            <form action="<?= url('/login') ?>" method="POST" data-validate>
                <?= csrfField() ?>
                <?php if (!empty($_GET['redirect'])): ?>
                    <input type="hidden" name="redirect" value="<?= h($_GET['redirect']) ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required 
                           value="<?= old('email') ?>" placeholder="email@contoh.com" autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required 
                           placeholder="••••••••" autocomplete="current-password">
                </div>
                
                <div class="form-group d-flex justify-between items-center">
                    <label class="form-label" style="margin:0; font-weight:400; font-size:0.875rem;">
                        <input type="checkbox" name="remember" style="width:auto; margin-right:0.5rem;"> Ingat saya
                    </label>
                    <a href="#" class="text-sm" style="color: var(--color-primary);">Lupa password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-4">Masuk</button>
            </form>
            
            <p class="text-center mt-4 text-muted">
                Belum punya akun? <a href="<?= url('/register' . (!empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '')) ?>">Daftar di sini</a>
            </p>
        </div>
    </div>
</div>