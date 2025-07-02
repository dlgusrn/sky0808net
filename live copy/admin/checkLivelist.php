<!-- 기본 설정 파일을 불러온다 -->
<? include_once '../config.inc' ; ?>
<?
$row = $DB->query( "SELECT * FROM live_list WHERE link = '".$_POST['skylink']."'" )->fetch() ;

if ( $_POST['skyview'] == $row['view'] ){
    echo 'true' ;
} else {
    echo 'false' ;
}
?>