<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;
require_admin('live_list');
require_post();
require_csrf();
$idx = input_int('idx', $_POST);

// 해당 레코드 삭제
$statement = $DB->prepare('DELETE FROM live_list WHERE idx = :idx');
$statement->execute(['idx' => $idx]);
