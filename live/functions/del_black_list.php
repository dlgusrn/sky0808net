<? 
// 기본 설정 파일을 불러온다
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/config.inc';

$get_idx = $_GET['idx'];

// 해당 데이터 삭제
$DB->exec( "DELETE FROM black_list WHERE idx IN ( $get_idx )" ) ;

?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/live/black_list.html'>