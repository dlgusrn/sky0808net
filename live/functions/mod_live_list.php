<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;
require_admin('live_list');
require_post();
require_csrf();
$idx = input_int('idx', $_GET);

// 영상 링크 변환
$youtubeUrl = explode ( '/', $_POST['link'] ) ;

// 수정사항 저장
$statement = $DB->prepare('UPDATE live_list SET church = :church, worship = :worship, title = :title, link = :link, passwd = :password, date_update = NOW() WHERE idx = :idx');
$statement->execute(['church' => $_POST['church'], 'worship' => $_POST['worship'], 'title' => $_POST['title'], 'link' => $youtubeUrl[3], 'password' => $_POST['password'], 'idx' => $idx]);
?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/live/live_read.html?idx=<?=$_GET['idx']?>'>
