<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;

$data = $DB->query( "SELECT idx, date(`start_date`) AS `start_date`, date(`end_date`) AS `end_date`, `title`, `contents`, `attached`
                        FROM notice WHERE idx = ".$_POST['idx'] )->fetchAll() ;

echo json_encode($data);
?>