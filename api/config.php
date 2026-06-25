<?php
/**
 * Central configuration for Happy Bangladesh Dashboard
 * Contains API keys, database settings, and external API config
 */

// ─── OpenAI ───────────────────────────────────────────────────
define('OPENAI_API_KEY', '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');
define('OPENAI_MODEL', 'gpt-4o-mini'); // cheapest capable model

// ─── HappyCRM External API ───────────────────────────────────
define('CRM_API_BASE', 'https://happycrm.site/happyreports_api/index.php');

// ─── Database Configuration (Dynamic Local/Live) ──────────────
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = (strpos($host, 'localhost') !== false || $host === '127.0.0.1' || (empty($host) && PHP_SAPI === 'cli'));

if ($isLocal) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'happy_bangladesh');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'rasedwwq_hr');
    define('DB_USER', 'rasedwwq_hr');
    define('DB_PASS', ';(wtQK3qEA#XUL!L');
}
define('DB_CHARSET', 'utf8mb4');
