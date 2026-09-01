<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;
$unused = require_admin('live_list');
require_post();
require_csrf();
$idx = input_int('idx', $_POST);

$statement = $DB->prepare('SELECT church FROM live_list WHERE idx = :idx');
$statement->execute(['idx' => $idx]);
$row = $statement->fetch(PDO::FETCH_ASSOC);
if (!$row) { http_response_code(404); exit; }

$DB->beginTransaction();
try {
    $statement = $DB->prepare("UPDATE live_list SET `use` = 'Y' WHERE idx = :idx");
    $statement->execute(['idx' => $idx]);
    $statement = $DB->prepare("UPDATE live_list SET `use` = 'N' WHERE idx <> :idx AND church = :church");
    $statement->execute(['idx' => $idx, 'church' => $row['church']]);
    $DB->commit();
} catch (Throwable $error) { $DB->rollBack(); throw $error; }
?>
