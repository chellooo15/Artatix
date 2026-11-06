<div class="container" style="max-width: 400px; text-align: center; padding: 4rem 1rem;">
    <div style="font-size: 6rem; color: var(--color-primary-light); margin-bottom: 1rem; font-weight: 700;">404</div>
    <h1 style="margin-bottom: 1rem;">Halaman Tidak Ditemukan</h1>
    <p class="text-muted mb-6"><?= h($message ?? 'Halaman yang Anda cari tidak ada atau telah dipindahkan.') ?></p>
    <a href="<?= url('/') ?>" class="btn btn-primary">Kembali ke Beranda</a>
</div>