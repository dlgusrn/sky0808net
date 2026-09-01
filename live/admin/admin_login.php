<?
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc';
require_post();
require_csrf();

// 관리자 정보 불러오기
$adminName = trim((string) ($_POST['admin_name'] ?? ''));
$statement = $DB->prepare('SELECT * FROM member_admin WHERE username = :username LIMIT 1');
$statement->execute(['username' => $adminName]);
$row = $statement->fetch(PDO::FETCH_ASSOC);

// 관리자 이름 유무 체크
if ( empty ( $_POST['admin_name'] ) ){
    echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>" ;
    $DB->exec( "INSERT INTO system_log ( log_lev, `message`, insert_date ) VALUES ( 'INFO', '[로그인 실패] 관리자 이름 미입력.', NOW() )" ) ;
    exit ;
}
if ( false == $row || empty ( $row ) || $_POST['admin_name'] != $row['username'] ) {
    echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>" ;
    $DB->exec( "INSERT INTO system_log ( log_lev, `message`, insert_date ) VALUES ( 'INFO', '[로그인 실패] 일치하는 관리자의 이름이 없음. 입력된 관리자 이름 : $_POST[admin_name]', NOW() )" ) ;
    exit ;
}

// 비밀번호 체크
$submittedPassword = (string) ($_POST['admin_password'] ?? '');

if ( empty ( $_POST['admin_password'] ) ){
    echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>" ;
    $DB->exec( "INSERT INTO system_log ( log_lev, `message`, insert_date ) VALUES ( 'INFO', '[로그인 실패] 관리자 비밀번호 미입력.', NOW() )" ) ;
    exit ;
}
// 기존 SHA-256 계정은 첫 성공 로그인 때 안전한 해시로 자동 전환한다.
$validPassword = $row && (password_verify($submittedPassword, $row['password']) || hash_equals($row['password'], hash('sha256', $submittedPassword)));
if (!$validPassword) {
    echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>" ;
    $DB->exec( "INSERT INTO system_log ( log_lev, `message`, insert_date ) VALUES ( 'INFO', '[로그인 실패] 입력한 비밀번호가 일치하지 않음. 입력된 관리자 이름 : $_POST[admin_name]', NOW() )" ) ;
    exit ;
}

if (!password_get_info($row['password'])['algo']) {
    $statement = $DB->prepare('UPDATE member_admin SET password = :password WHERE idx = :idx');
    $statement->execute(['password' => password_hash($submittedPassword, PASSWORD_DEFAULT), 'idx' => $row['idx']]);
}

// 세션 생성
session_regenerate_id(true);
$_SESSION['admin_username'] = $adminName;

// 생방송 접속
$name_check = $DB->query( "SELECT * FROM live_entry WHERE `name` LIKE '%$_SESSION[admin_username]%' AND `branch` = 'ADMIN'" )->fetch() ;
if ( ! empty ( $name_check ) ){
    // 이전 접속 기록 삭제
    $DB->exec( "DELETE FROM live_entry WHERE `name` LIKE '%$_SESSION[admin_username]%' AND `branch` = 'ADMIN'" ) ;
}
$DB->exec( "INSERT INTO live_entry ( `session`, branch, church, `name`, date_insert ) VALUES ( '" . session_id() . "', 'ADMIN', '$row[church]', '$_SESSION[admin_username]', NOW() )" ) ;

// 로그인 로그 기록
$DB->exec( "INSERT INTO system_log ( log_lev, `message`, insert_date ) VALUES ( 'INFO', '[로그인] 관리자 : $_SESSION[admin_username].', NOW() )" ) ;
?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/'>
