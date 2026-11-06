<section class="hero">
    <div class="container">
        <div style="max-width: 700px;">
            <h1 style="font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 700; margin-bottom: 1rem; line-height: 1.2;">
                Temukan & Beli Tiket Konser Favoritmu
            </h1>
            <p style="font-size: 1.125rem; opacity: 0.9; margin-bottom: 2rem; max-width: 500px;">
                Platform tiket konser terpercaya di Indonesia. Beli tiket CAT, Standing, VIP, atau VVIP dengan mudah dan aman.
            </p>
            <div class="d-flex gap-3 flex-wrap">
                <a href="<?= url('/events') ?>" class="btn btn-lg" style="background: white; color: var(--color-primary);">
                    Lihat Semua Acara
                </a>
                <a href="#features" class="btn btn-lg btn-outline" style="border-color: rgba(255,255,255,0.5); color: white;">
                    Cara Kerja
                </a>
            </div>
        </div>
    </div>
</section>

<section class="container" id="features">
    <div class="grid grid-3 mb-6">
        <div class="card text-center p-4">
            <div style="width: 64px; height: 64px; border-radius: var(--radius-lg); background: var(--color-primary-light); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--color-primary); font-size: 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </div>
            <h3>Pilih Kelas Tiket</h3>
            <p class="text-muted text-sm">CAT, Festival/Standing, VIP, atau VVIP - pilih yang sesuai budget & preferensi kamu.</p>
        </div>
        <div class="card text-center p-4">
            <div style="width: 64px; height: 64px; border-radius: var(--radius-lg); background: #d1fae5; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--color-success); font-size: 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            </div>
            <h3>Flash Sale Eksklusif</h3>
            <p class="text-muted text-sm">Dapatkan akses beli tiket sebelum dibuka untuk umum dengan kode Flash Sale.</p>
        </div>
        <div class="card text-center p-4">
            <div style="width: 64px; height: 64px; border-radius: var(--radius-lg); background: #fef3c7; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: var(--color-warning); font-size: 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <h3>Pembayaran Aman</h3>
            <p class="text-muted text-sm">Integrasi Midtrans untuk pembayaran via VA, e-wallet, kartu kredit, dan retail outlet.</p>
        </div>
    </div>
    
    <div class="d-flex justify-between items-center mb-4">
        <h2>Acara Mendatang</h2>
        <a href="<?= url('/events') ?>" class="btn btn-outline">Lihat Semua</a>
    </div>
    
    <?php if (empty($events)): ?>
        <div class="card empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--color-text-muted);">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <h3>Belum Ada Acara</h3>
            <p class="text-muted">Tidak ada acara yang tersedia saat ini. Cek kembali nanti!</p>
        </div>
    <?php else: ?>
        <div class="grid grid-3" id="eventsGrid">
            <?php foreach ($events as $event): ?>
                <?php require __DIR__ . '/../components/event_card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

