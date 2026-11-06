<?php

session_start();

// Autoloader untuk namespace App\...
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../';

    // Cek apakah class menggunakan namespace App\
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    // Ambil relative class name (misal: Controllers\HomeController)
    $relativeClass = substr($class, strlen($prefix));

    // Pisahkan direktori dan nama file
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    
    // Ubah nama folder menjadi lowercase (Controllers -> controllers, Core -> core, Models -> models)
    $folderPath = strtolower(implode('/', $parts));
    
    $file = $baseDir . ($folderPath ? $folderPath . '/' : '') . $className . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Database\Database;

require_once __DIR__ . '/config.php';
$GLOBALS['config'] = require __DIR__ . '/config.php';

date_default_timezone_set($GLOBALS['config']['app']['timezone']);

require_once __DIR__ . '/helpers.php';

Database::init($GLOBALS['config']);

// Alias class core agar bisa dipanggil langsung di file View / Partial
class_alias(\App\Middleware\Auth::class, 'Auth');
class_alias(\App\Config\Flash::class, 'Flash');