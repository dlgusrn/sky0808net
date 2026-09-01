<?
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc';

require_post();
require_csrf();
$branch = 'MEMBER';

// 비밀번호 체크
$row = $DB->query( "SELECT passwd FROM live_list WHERE `use` = 'Y' ORDER BY idx DESC" )->fetch() ;
$livePasswd = ! empty ( $row['passwd'] ) ? $row['passwd'] : '019108' ;

if ( $_POST['live_password'] != $livePasswd ) {
    echo "<script>alert('비밀번호가 맞지 않습니다.'); history.back();</script>" ;
    exit ;
}

// 이름 글자수 체크 (2-4자)
$username = trim((string) ($_POST['username'] ?? ''));
$pattern = '/^[가-힣]{2,4}$/u' ;

if ( ! preg_match ( $pattern, $username ) ) {
	echo "<script>alert('이름 규칙에 맞지 않습니다.'); history.back();</script>" ;
    exit ;
}

// 블랙리스트 체크
$blacklist = $DB->prepare('SELECT 1 FROM black_list WHERE nickname = :username LIMIT 1');
$blacklist->execute(['username' => $username]);
$num = $blacklist->fetchColumn();

if ( $num > 0 ) {
    echo "<script>alert('실명만 입력 가능합니다.'); history.back();</script>" ;
    exit ;
}

// 세션 생성
session_regenerate_id(true);
$_SESSION['username'] = $username ;
$_SESSION['branch'] = $branch ;
?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>'>
