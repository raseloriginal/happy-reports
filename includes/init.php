<?php
// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require Config
require_once __DIR__ . '/config.php';

// Require Database Class
require_once __DIR__ . '/Database.php';

// Autoload Models
spl_autoload_register(function($className){
    $file = __DIR__ . '/Models/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
