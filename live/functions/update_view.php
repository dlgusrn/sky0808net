<? 
// 기본 설정 파일을 불러온다
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/config.inc';

$DB->exec( "UPDATE live_list SET `view` = '" . ( $_GET['view'] == 'Y' ? 'N' : 'Y' ) . "' WHERE idx = " . $_GET['idx'] ) ; ?>