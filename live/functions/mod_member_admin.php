<? 
// 기본 설정 파일을 불러온다
include_once '../inc/config.inc' ;

// 비밀번호 체크
if($_POST['password'] != $_POST['password_check']){
    echo "<script>alert('비밀번호가 일치하지 않습니다.'); history.back();</script>" ;
    exit ;
}

// 비밀번호 암호화
$password = hash ( "sha256", $_POST['password'] ) ;

// 권한 세팅
$live_manage = isset ( $_POST['live_manage'] ) ? 'Y' : 'N' ;   // 생방송 보기
$live_list = isset ( $_POST['live_list'] ) ? 'Y' : 'N' ;       // 생방송 링크 관리
$history = isset ( $_POST['history'] ) ? 'Y' : 'N' ;           // 생방송 접속 이력
$black_list = isset ( $_POST['black_list'] ) ? 'Y' : 'N' ;     // 블랙리스트 관리
$video_list = isset ( $_POST['video_list'] ) ? 'Y' : 'N' ;     // 다시보기 - 연도별
$event_video = isset ( $_POST['event_video'] ) ? 'Y' : 'N' ;   // 다시보기 - 행사
$member_admin = isset ( $_POST['member_admin'] ) ? 'Y' : 'N' ; // 관리자 관리
$member_saint = isset ( $_POST['member_saint'] ) ? 'Y' : 'N' ; // 성도 관리


// DB 저장
$DB->exec(
    "UPDATE member_admin
    SET
        `username` = '$_POST[username]' ,
        `password` = '$password' ,
        `level` = '$_POST[level]' ,
        `church` = '$_POST[church]' ,
        `live_manage` = '$live_manage',
        `live_list` = '$live_list' ,
        `history` = '$history' ,
        `black_list` = '$black_list' ,
        `video_list` = '$video_list' ,
        `event_video` = '$event_video' ,
        `member_admin` = '$member_admin' ,
        `member_saint` = '$member_saint' ,
        `update_date` = NOW()
    WHERE `idx` = $_GET[idx]
" ) ;
?>
<script>alert('관리자 정보가 수정 되었습니다.')</script>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/member/member_admin.html'>