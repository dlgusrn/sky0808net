<?
// 기본 설정 파일을 불러온다
include_once $_SERVER['DOCUMENT_ROOT'].'/live/inc/config.inc';

// 생방송 저장
$DB->exec( "INSERT INTO live_history ( branch, `name`, login_date ) VALUES ( '$_SESSION[branch]', '$_SESSION[username]', NOW() )" ) ;

$name_check = $DB->query( "SELECT * FROM live_entry WHERE `name` LIKE '%$_SESSION[username]%' AND `branch` <> 'ADMIN'" )->fetch() ;
if ( ! empty ( $name_check ) ){
    // 이전 접속 기록 삭제
    $DB->exec( "DELETE FROM live_entry WHERE `name` LIKE '%$_SESSION[username]%' AND `branch` <> 'ADMIN'" ) ;
}
$DB->exec( "INSERT INTO live_entry ( `session`, `branch`, `church`, `name`, date_insert ) VALUES ( '".session_id()."', '$_SESSION[branch]', '$_POST[church]', '$_SESSION[username]', NOW() )" ) ;
?>