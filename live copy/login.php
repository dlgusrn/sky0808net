<!-- 기본 설정 파일을 불러온다 -->
<? include_once 'config.inc' ?>

<?
// 일반 회원, 관리자 구분
$adminPasswd = 'qkdthdtlf08!' ;
$branch = ( $_POST['live_password'] == $adminPasswd ) ? 'ADMIN' : "MEMBER" ;

// 비밀번호 체크
$row = $DB->query( "SELECT passwd FROM live_list WHERE `use` = 'Y' ORDER BY idx DESC" )->fetch() ;
$livePasswd = ! empty ( $row['passwd'] ) ? $row['passwd'] : '019108' ;

if ( $_POST['live_password'] != $adminPasswd ) {
    if ( $_POST['live_password'] != $livePasswd ) {
        echo "<script>alert('비밀번호가 맞지 않습니다.'); history.back();</script>" ;
        exit ;
    }
}

// 이름 글자수 체크 (2-4자)
$username = $_POST['username'] ;
$pattern = '/^[가-힣]{6,12}$/' ;

if ( ! preg_match ( $pattern, $username ) ) {
	echo "<script>alert('이름 규칙에 맞지 않습니다.'); history.back();</script>" ;
    exit ;
}

// 블랙리스트 체크
$num = $DB->query( "SELECT * FROM black_list WHERE `nickname` = '$username'" )->rowCount() ;

if ( $num > 0 ) {
    echo "<script>alert('실명만 입력 가능합니다.'); history.back();</script>" ;
    exit ;
}

// 세션 생성
$_SESSION['username'] = $_POST['username'] ;
$_SESSION['branch'] = $branch ;

// DB 저장
$DB->exec( "INSERT INTO live_history ( branch, `name`, login_date ) VALUES ( '$branch', '$_POST[username]', NOW() )" ) ;

// 생방송 저장
$name_check = $DB->query( "SELECT * FROM live_entry WHERE `name` LIKE '%$_SESSION[username]%'" )->fetch() ;
if ( ! empty ( $name_check ) ){
    // 이전 접속 기록 삭제
    $DB->exec( "DELETE FROM live_entry WHERE `name` LIKE '%$_SESSION[username]%'" ) ;
}
$DB->exec( "INSERT INTO live_entry ( branch, `name`, date_insert ) VALUES ( '$branch', '$_POST[username]', NOW() )" ) ;
?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>index.html'>