<?php
/**
 * KrBank - Application Configuration
 */

// Application
define('APP_NAME', 'KrBank');
define('APP_TAGLINE', 'Your Future. Your Bank.');
define('APP_URL', 'http://localhost/krbankQ');
define('APP_VERSION', '1.0.0');
define('APP_EMAIL', 'support@krbank.com');
define('APP_CURRENCY', 'USD');
define('APP_TIMEZONE', 'America/New_York');

// Paths
define('BASE_PATH', dirname(__DIR__));
define('CONFIG_PATH', BASE_PATH . '/config');
define('CONTROLLERS_PATH', BASE_PATH . '/controllers');
define('MODELS_PATH', BASE_PATH . '/models');
define('VIEWS_PATH', BASE_PATH . '/views');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// Session
define('SESSION_LIFETIME', 3600); // 1 hour
define('SESSION_NAME', 'KRBANK_SESSION');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('BCRYPT_COST', 12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 900); // 15 minutes

// Pagination
define('ITEMS_PER_PAGE', 15);

// Timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');
