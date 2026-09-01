<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;
$unused = require_admin('live_list');
require_post();
require_csrf();
$idx = input_int('idx', $_POST);
$view = ($_POST['view'] ?? '') === 'Y' ? 'N' : 'Y';

$statement = $DB->prepare('UPDATE live_list SET view = :view WHERE idx = :idx');
$statement->execute(['view' => $view, 'idx' => $idx]);
