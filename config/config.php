<?php
// Each deployment supplies its own private settings; environment values take priority.
$databaseSettings = is_file(__DIR__ . '/database.local.php')
    ? require __DIR__ . '/database.local.php' : [];
if (!is_array($databaseSettings)) throw new RuntimeException('Invalid database configuration.');
foreach (['DB_HOST'=>['SKY_DB_HOST','host','localhost'], 'DB_NAME'=>['SKY_DB_NAME','database',''],
    'DB_USER'=>['SKY_DB_USER','username',''], 'DB_PASS'=>['SKY_DB_PASSWORD','password','']] as $constant=>$setting) {
    $environment = getenv($setting[0]);
    define($constant, $environment !== false ? $environment : ($databaseSettings[$setting[1]] ?? $setting[2]));
}
unset($databaseSettings, $constant, $setting, $environment);
define('BASE_URL', getenv('SKY_BASE_URL') ?: 'http://live.sky0808.net');
date_default_timezone_set('Asia/Seoul');
define('SITE_WWW', $_SERVER['HTTP_HOST'] ?? 'localhost');
define('SITE_LIVE', '/live');
define('SITE_ADMIN', '/admin');
