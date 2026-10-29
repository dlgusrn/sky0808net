<?php
// Dummy environment values only; never connect or print actual credentials.
putenv('SKY_DB_HOST=test-host');
putenv('SKY_DB_NAME=test-database');
putenv('SKY_DB_USER=test-user');
putenv('SKY_DB_PASSWORD=test-password');
$_SERVER['HTTP_HOST']='dev.example';
require_once __DIR__ . '/../config/config.php';
if (DB_HOST!=='test-host'||DB_NAME!=='test-database'||DB_USER!=='test-user'||DB_PASS!=='test-password'||SITE_WWW!=='dev.example')throw new RuntimeException('Database environment precedence failed');
if(date_default_timezone_get()!=='Asia/Seoul')throw new RuntimeException('Timezone mismatch');
echo 'PASS: private database configuration supports environment overrides.',PHP_EOL;
