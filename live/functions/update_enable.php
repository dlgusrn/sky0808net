<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;
$unused = require_admin('live_list');
require_post();
require_csrf();
$idx = input_int('idx', $_POST);
$enable = ($_POST['enable'] ?? '') === 'Y' ? 'N' : 'Y';

$statement = $DB->prepare('UPDATE notice SET enable = :enable WHERE idx = :idx');
$statement->execute(['enable' => $enable, 'idx' => $idx]);
