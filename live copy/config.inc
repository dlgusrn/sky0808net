<?
header("Content-type:text/html;charset=utf-8");

// 세션 시작
session_start();

// DB 연결
try {
    $DB = new PDO ( 'mysql:host=localhost; dbname=sky0808;', 'sky0808net', 'qkdthdtlf08!' ) ;
    $DB->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ) ;
}
// 연결 실패시 원인 출력
catch ( PDOException $ex ) {
    echo 'DB 연결 실패... 이유는 : ' . $ex->getMessage () ;
}

// 상수 선언
define ( 'SITE_WWW' , $_SERVER['HTTP_HOST'] ) ;
define ( 'SITE_LIVE' , '/live/' ) ;


// 로그인 체크
$fileName = basename ( $_SERVER['PHP_SELF'] ) ;

if ( $fileName != 'index.html' && $fileName != 'login.php' && empty ( $_SESSION['username'] ) )
    echo "<script>alert('잘못된 접근입니다.'); location.href = '/live/index.html'; </script> " ;

// 관리자 체크
$phpself = $_SERVER['PHP_SELF'] ;
$pageUrl = explode ( '/', $phpself ) ;

if ( $pageUrl[1] == 'admin' && $_SESSION['branch'] != 'ADMIN' )
    echo "<script>alert('잘못된 접근입니다.'); location.href = '/live/index.html'; </script> " ;
?>