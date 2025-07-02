<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;

$row = $DB->query( "SELECT church FROM live_list WHERE idx = " . $_GET['idx'] )->fetch() ;

$DB->exec( "UPDATE live_list SET `use` = 'Y' WHERE idx = " . $_GET['idx'] ) ;
$DB->exec( "UPDATE live_list SET `use` = 'N' WHERE idx <> " . $_GET['idx'] . " AND church = '" . $row['church'] . "'" ) ;
?>