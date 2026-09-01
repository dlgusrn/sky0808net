<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;
require_admin('member_admin');
require_post();
require_csrf();

$get_idx = array_filter(array_map('intval', explode(',', (string) ($_POST['idx'] ?? ''))));
if (!$get_idx) { http_response_code(422); exit; }

// 해당 데이터 삭제
$placeholders = implode(',', array_fill(0, count($get_idx), '?'));
$statement = $DB->prepare("DELETE FROM member_admin WHERE idx IN ($placeholders)");
$statement->execute(array_values($get_idx));
