<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;

// 영상 링크 변환
$youtubeUrl = explode ( '/', $_POST['link'] ) ;

// 수정사항 저장
$DB->exec( "UPDATE live_list SET church = '$_POST[church]', worship = '$_POST[worship]', title = '$_POST[title]', link = '$youtubeUrl[3]', passwd = '$_POST[password]', date_update = NOW() WHERE idx = " . $_GET['idx'] ) ;
?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/live/live_read.html?idx=<?=$_GET['idx']?>'>