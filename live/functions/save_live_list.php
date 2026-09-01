<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;
require_admin('live_list');
require_post();
require_csrf();

$church = $_POST['church'] ?? '';
if (!in_array($church, ['sky', 'beer'], true)) { http_response_code(422); exit; }
$worship = trim((string) ($_POST['worship'] ?? ''));
$title = trim((string) ($_POST['title'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

// 유튜브 고유번호 저장
if ( strpos ( $_POST['link'], '/' ) == FALSE ) {
    $youtubeUrl = $_POST['link'] ; // 고유번호만 입력할 시 고유번호 그대로 저장
} else {
    // 링크 전체를 입력할 시 고유번호 추출해서 저장
    $link = explode ( '/', $_POST['link'] ) ;
    if ( 'live' != $link[3] ){
        $youtubeUrl = explode ( '?', $link[3] )[0] ;
    } else {
        $youtubeUrl = explode ( '?', $link[4] )[0] ;
    }
}

if (!preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeUrl) || $worship === '' || $title === '') { http_response_code(422); exit; }

// DB 저장
$DB->beginTransaction();
try {
    $statement = $DB->prepare('INSERT INTO live_list (church, worship, title, link, passwd, date_insert) VALUES (:church, :worship, :title, :link, :password, NOW())');
    $statement->execute(['church' => $church, 'worship' => $worship, 'title' => $title, 'link' => $youtubeUrl, 'password' => $password]);
    $idx = (int) $DB->lastInsertId();

// 최근 등록된 영상 외에 송출 안되도록 변경
$statement = $DB->prepare("UPDATE live_list SET `use` = 'N' WHERE church = :church AND idx != :idx");
$statement->execute(['church' => $church, 'idx' => $idx]);
    $DB->commit();
} catch (Throwable $error) { $DB->rollBack(); throw $error; }
?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/live/live_list.html'>
