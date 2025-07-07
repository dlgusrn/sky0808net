<? 
// 기본 설정 파일을 불러온다
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/config.inc';

// DB 저장
$DB->exec( "INSERT INTO black_list ( `nickname`, insert_date ) VALUES ( '$_POST[nickname]', NOW() )" ) ;

?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/live/black_list.html'>