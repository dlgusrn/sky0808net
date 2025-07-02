<?
// 세션 시작
session_start();

// DB 접속
include_once 'db_conf.php';

// 상수 선언
define ( 'SITE_WWW' , $_SERVER['HTTP_HOST'] ) ;
define ( 'SITE_LIVE' , '/live' ) ;
define ( 'SITE_ADMIN' , '/admin' ) ;


// 로그인 체크
$fileName = basename ( $_SERVER['PHP_SELF'] ) ;
$phpself = $_SERVER['PHP_SELF'] ;
$pageUrl = explode ( '/', $phpself ) ;

// 로그인 체크 예외 처리 파일 배열
$dir = $_SERVER['DOCUMENT_ROOT'] . "/live/functions/" ;
$files = array_diff ( scandir ( $dir ) , array ( '.' , '..' ) ) ;
$exception_file_array = [];
foreach ( $files as $file ) {
    if ( is_file ( $dir . $file ) ) {
        $exception_file_array[] = $file; // 파일명+확장자
    }
}

array_push ( $exception_file_array , 'index.html' , 'login.php' , 'logout.php' , 'admin_login.php' , 'admin_logout.php' ) ;

if ( $pageUrl[2] == 'admin' ) {
    if ( false == in_array ( $fileName , $exception_file_array ) && empty ( $_SESSION['admin_username'] ) ) {
        echo "<script>alert('잘못된 접근입니다.'); location.href = '".SITE_LIVE."/admin/'; </script> " ;
    }
} else {
    if ( false == in_array ( $fileName , $exception_file_array ) && empty ( $_SESSION['username'] ) ) {
        echo "<script>alert('로그인 후에 생방송 시청이 가능합니다.\\n\\n로그인 페이지로 이동합니다.'); location.href = '".SITE_LIVE."'; </script> " ;
    }
}
?>