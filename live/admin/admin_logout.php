<?
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc';

// 생방송 접속자 목록에서 제거
$DB->exec( "DELETE FROM live_entry WHERE `name` = '$_SESSION[admin_username]'" ) ;

// 로그아웃 로그 기록
$DB->exec( "INSERT INTO system_log ( log_lev, `message`, insert_date ) VALUES ( 'INFO', '[로그아웃] 관리자 : $_SESSION[admin_username].', NOW() )" ) ;

// 세션 삭제
unset ( $_SESSION['admin_username'] ) ;

?>

<script>
    // alert('로그아웃되었습니다.') ;
    location.href = '<?=SITE_LIVE?>/admin/' ;
</script>