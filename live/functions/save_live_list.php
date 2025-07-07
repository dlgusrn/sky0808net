<? 
// 기본 설정 파일을 불러온다
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/config.inc';

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

// DB 저장
$DB->exec( "INSERT INTO live_list ( church, worship, title, link, passwd, date_insert ) VALUES ( '$_POST[church]', '$_POST[worship]', '$_POST[title]', '$youtubeUrl', '$_POST[password]', NOW() )" ) ;

// 최근 등록된 영상 외에 송출 안되도록 변경
$row = $DB->query( "SELECT * FROM live_list WHERE church = '$_POST[church]' ORDER BY idx DESC LIMIT 1" )->fetch() ;

$DB->exec( "UPDATE live_list SET `use` = 'N' WHERE church = '$_POST[church]' AND idx != " . $row['idx'] ) ;
?>
<meta http-equiv='refresh' content='0;url=<?=SITE_LIVE?>/admin/live/live_list.html'>