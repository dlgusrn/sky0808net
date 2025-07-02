<!-- 기본 설정 파일을 불러온다 -->
<? include_once '../config.inc' ?>
<? $DB->exec( "UPDATE live_list SET `view` = '" . ( $_GET['view'] == 'Y' ? 'N' : 'Y' ) . "' WHERE idx = " . $_GET['idx'] ) ; ?>