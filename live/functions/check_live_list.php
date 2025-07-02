<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;

$data = $DB->query( "SELECT * FROM live_list WHERE link = '".$_GET['link']."' ORDER BY idx DESC" )->fetch() ;

$DB->exec( "UPDATE live_entry SET `session` = '".session_id()."', date_update = NOW() WHERE `name` = '$_SESSION[username]' AND `branch` = '$_SESSION[branch]'" ) ;

echo json_encode($data);
?>