<? 
// 기본 설정 파일을 불러온다
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/config.inc';

$data = $DB->query( "SELECT * FROM live_entry WHERE church = 'sky' ORDER BY branch ASC, idx DESC" )->fetchAll() ;

echo json_encode($data);
?>