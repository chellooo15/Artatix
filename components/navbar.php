<nav class="navbar">
    <div class="container navbar-inner">
        <a href="<?= url('/') ?>" class="navbar-brand">
            <svg width="32" height="32" viewBox="0 0 32 32" fill="none" class="brand-icon">
                <rect width="32" height="32" rx="8" fill="#6366f1"/>
                <path d="M8 16L14 22L24 10" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Artatix</span>
        </a>
        
        <div class="navbar-menu" id="navbarMenu" role="navigation" aria-label="Utama">
            <a href="<?= url('/') ?>" class="nav-link <?= isCurrentPath('/') ? 'active' : '' ?>" <?= isCurrentPath('/') ? 'aria-current="page"' : '' ?>>Beranda</a>
            <a href="<?= url('/events') ?>" class="nav-link <?= isCurrentPath('/events') ? 'active' : '' ?>" <?= isCurrentPath('/events') ? 'aria-current="page"' : '' ?>>Acara</a>
            
            <?php if (Auth::check()): ?>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= url('/admin') ?>" class="nav-link <?= isCurrentPath('/admin') ? 'active' : '' ?>">Admin</a>
                <?php endif; ?>
                <?php if (Auth::isOrganizer()): ?>
                    <a href="<?= url('/admin/events') ?>" class="nav-link <?= isCurrentPath('/admin/events') ? 'active' : '' ?>">Acara Saya</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="navbar-actions">
            <?php if (Auth::guest()): ?>
                <a href="<?= url('/login') ?>" class="nav-link">Login</a>
                <a href="<?= url('/register') ?>" class="btn btn-primary">Daftar</a>
            <?php endif; ?>
            <?php if (Auth::check()): ?>
            <div class="nav-dropdown" id="accountDropdown">
                <button type="button" class="nav-dropdown-toggle" id="accountToggle" aria-haspopup="menu" aria-expanded="false" aria-controls="accountMenu">
                    <span class="avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr(Auth::user()['name'] ?? '?', 0, 1))) ?></span>
                    <span class="user-name"><?= e(Auth::user()['name']) ?></span>
                    <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="nav-dropdown-menu" id="accountMenu" role="menu" aria-label="Akun">
                    <div class="nav-dropdown-header">
                        <div class="nav-dropdown-name"><?= e(Auth::user()['name']) ?></div>
                        <div class="nav-dropdown-email"><?= e(Auth::user()['email']) ?></div>
                    </div>
                    <a href="<?= url('/user/orders') ?>" class="nav-dropdown-item <?= isCurrentPath('/user/orders') ? 'active' : '' ?>" role="menuitem">Tiket Saya</a>
                    <a href="<?= url('/user/settings') ?>" class="nav-dropdown-item <?= isCurrentPath('/user/settings') ? 'active' : '' ?>" role="menuitem">Pengaturan Akun</a>
                    <div class="nav-dropdown-divider"></div>
                    <form action="<?= url('/logout') ?>" method="POST">
                        <?= csrfField() ?>
                        <button type="submit" class="nav-dropdown-item nav-dropdown-logout" role="menuitem">Logout</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <button type="button" class="theme-toggle" aria-label="Ganti ke tema gelap" aria-pressed="false" title="Ganti tema">
                <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="4"></line><line x1="12" y1="20" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="4" y2="12"></line><line x1="20" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            </button>
            <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="navbarMenu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</nav>