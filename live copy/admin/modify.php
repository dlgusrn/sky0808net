<!-- 기본 설정 파일을 불러온다 -->
<? include_once '../config.inc' ?>
<?
// 유튜브 고유번호 저장
if ( strpos ( $_POST['link'], '/' ) == FALSE ) {
    $youtubeUrl = $_POST['link'] ; // 고유번호만 입력할 시 고유번호 그대로 저장
} else {
    // 링크 전체를 입력할 시 고유번호 추출해서 저장
    $link = explode ( '/', $_POST['link'] ) ;
    if ( 'live' != $link[3] ){
        $youtubeUrl = explode ( '?', $link[3] )[0] ;
    } else {
        $youtubeUrl = explode ( '?', $link[4] )[0] ;
    }
}

// 생방송 정보 업데이트
$DB->exec( "UPDATE live_list SET church = '$_POST[church]', worship = '$_POST[worship]', title = '$_POST[title]', link = '$youtubeUrl', passwd = '$_POST[password]', date_update = NOW() WHERE idx = " . $_GET['idx'] ) ;
?>
<meta http-equiv='refresh' content='0;url=read.html?idx=<?=$_GET['idx']?>'>