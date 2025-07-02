<?
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc';

session_destroy() ;

// 로그아웃 기록
$row = $DB->query( "SELECT idx FROM live_history WHERE `branch` = '$_GET[branch]' AND `name` = '$_GET[name]' AND logout_date IS NULL ORDER BY idx DESC LIMIT 1" )->fetch() ;
$DB->exec( "UPDATE live_history SET logout_date = NOW() WHERE `idx` = '$row[idx]'" ) ;

// 실시간 방송 나가기
$DB->exec( "DELETE FROM live_entry WHERE `branch` = '$_GET[branch]' AND `name` = '$_GET[name]'" ) ;
?>

<script>
    // alert('로그아웃되었습니다.') ;
    location.href = '<?=SITE_LIVE?>' ;
</script>