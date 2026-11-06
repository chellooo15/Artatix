<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= h($meta_description ?? 'Artatix — platform tiket konser terpercaya di Indonesia. Beli CAT, Standing, VIP, VVIP dengan aman.') ?>">
    <meta name="theme-color" content="#6366f1" id="metaThemeColor">
    <script>
    (function(){try{var t=localStorage.getItem('artatix-theme');if(!t){t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';}document.documentElement.setAttribute('data-theme',t);var m=document.getElementById('metaThemeColor');if(m){m.setAttribute('content',t==='dark'?'#0f172a':'#6366f1');}}catch(e){}})();
    </script>
    <meta property="og:title" content="<?= h(($title ?? 'Jual Beli Tiket Konser') . ' — ' . ($app_name ?? 'Artatix')) ?>">
    <meta property="og:description" content="<?= h($meta_description ?? 'Platform tiket konser terpercaya') ?>">
    <meta property="og:type" content="website">
    <title><?= h($app_name ?? 'Artatix') ?> - <?= h($title ?? 'Jual Beli Tiket Konser') ?></title>
    <link rel="stylesheet" href="<?= asset('styles/app.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <a href="#main" class="skip-link">Lewati ke konten</a>
    <?php require __DIR__ . '/../../components/navbar.php'; ?>
    
    <main class="main-content <?= $main_class ?? '' ?>" id="main" tabindex="-1">
        <?php require __DIR__ . '/../../components/flash.php'; ?>
        <?= $content ?? '' ?>
    </main>
    
    <?php require __DIR__ . '/../../components/footer.php'; ?>
    <script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>