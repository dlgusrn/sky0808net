<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;
require_admin('black_list');
require_post();
require_csrf();
$nickname = trim((string) ($_POST['nickname'] ?? ''));
// 블랙리스트는 로그인에 쓰일 수 있는 모든 이름/닉네임을 등록할 수 있어야 한다.
if ($nickname === '') { http_response_code(422); exit; }

// DB 저장
$statement = $DB->prepare('INSERT INTO black_list (nickname, insert_date) VALUES (:nickname, NOW())');
$statement->execute(['nickname' => $nickname]);

?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/live/black_list.html'>
