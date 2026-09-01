<?php
// 세션 시작
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// DB 접속
include_once __DIR__ . '/db_conf.php';
include_once __DIR__ . '/auth.inc';

// 상수 선언
define ( 'SITE_WWW' , $_SERVER['HTTP_HOST'] ) ;
define ( 'SITE_LIVE' , '/live' ) ;
define ( 'SITE_ADMIN' , '/admin' ) ;


// 페이지 로그인 체크. 데이터 변경 API는 각 엔드포인트에서 권한과 CSRF를 검사한다.
$fileName = basename ( $_SERVER['PHP_SELF'] ) ;
$phpself = $_SERVER['PHP_SELF'] ;
$pageUrl = explode ( '/', $phpself ) ;

if (($pageUrl[2] ?? '') === 'admin') {
    $adminLoginPage = in_array($fileName, ['admin', 'index.html', 'admin_login.php', 'admin_logout.php'], true);
    if (!$adminLoginPage && empty($_SESSION['admin_username'])) {
        echo "<script>alert('잘못된 접근입니다.'); location.href = '".SITE_LIVE."/admin/'; </script> " ;
    }
} elseif (($pageUrl[2] ?? '') === 'view.html' && empty($_SESSION['username'])) {
    echo "<script>alert('로그인 후에 생방송 시청이 가능합니다.\\n\\n로그인 페이지로 이동합니다.'); location.href = '".SITE_LIVE."'; </script> " ;
}
