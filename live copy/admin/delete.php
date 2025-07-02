<!-- 기본 설정 파일을 불러온다 -->
<? include_once '../config.inc' ?>
<?
// 해당 레코드 삭제
$DB->exec( "DELETE FROM live_list WHERE idx = '$_GET[idx]'" ) ;
?>
<meta http-equiv='refresh' content='0;url=list.html'>