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
$exception_file_array = array(
    'index.html' , 'login.php' , 'logout.php' , 'admin_login.php' , 'admin_logout.php' , 'get_sky_live_user.php' , 'get_beer_live_user.php'
    , 'save_member_admin.php' , 'mod_member_admin.php'
);

if ( $pageUrl[2] == 'admin' ) {
    if ( false == in_array( $fileName , $exception_file_array ) && empty ( $_SESSION['admin_username'] ) ) {
        echo "<script>alert('잘못된 접근입니다.'); location.href = '".SITE_LIVE."/admin/'; </script> " ;
    }
} else {
    if ( false == in_array( $fileName , $exception_file_array ) && empty ( $_SESSION['username'] ) ) {
        echo "<script>alert('로그인 후에 생방송 시청이 가능합니다.\\n\\n로그인 페이지로 이동합니다.'); location.href = '".SITE_LIVE."'; </script> " ;
    }
}
?>