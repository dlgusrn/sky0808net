<? 
// 기본 설정 파일을 불러온다
include_once '../config.inc' ;

$where = '';
if ( ! empty ( $_GET['church'] ) ){
    $where = "WHERE church = '$_GET[church]'";
}

$data = $DB->query( "SELECT * FROM live_entry $where ORDER BY branch ASC, idx DESC" )->fetchAll() ;

echo json_encode($data);
?>