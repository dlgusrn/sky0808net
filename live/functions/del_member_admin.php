<? 
// 기본 설정 파일을 불러온다
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/config.inc';

$get_idx = $_GET['idx'];

// 해당 데이터 삭제
$DB->exec( "DELETE FROM member_admin WHERE idx IN ( $get_idx )" ) ;

?>
<script>alert('관리자가 정상적으로 삭제 되었습니다.')</script>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/member/member_admin.html'>