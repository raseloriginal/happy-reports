<?php
// Detect if running on localhost
$isLocalhost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']);

if ($isLocalhost) {
    // Local DB Params
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'happy_reports');
    
    // Local URL Root
    define('URLROOT', 'http://localhost/HappyReports');
} else {
    // Live Server DB Params
    define('DB_HOST', 'localhost'); // Usually localhost for live servers like cPanel
    define('DB_USER', 'rasedwwq_hr');
    define('DB_PASS', ';(wtQK3qEA#XUL!L');
    define('DB_NAME', 'rasedwwq_hr');
    
    // Live URL Root (dynamically builds based on the live domain)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    define('URLROOT', $protocol . '://' . $_SERVER['HTTP_HOST']); // Kept dynamic. Add '/public' if your live server doesn't point directly to the public folder.
}

// App Root
define('APPROOT', dirname(dirname(__FILE__)));
// Site Name
define('SITENAME', 'Happy Reports CEO Panel');
