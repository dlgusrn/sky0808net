<?
// DB 연결
$host = 'localhost';
$database = 'sky0808';
$user = 'sky0808net';
$password = 'qkdthdtlf08!';

try {
    $DB = new PDO ( "mysql:host=$host; dbname=$database;", $user, $password ) ;
    $DB->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ) ;
}
// 연결 실패시 원인 출력
catch ( PDOException $ex ) {
    echo 'DB 연결 실패... 이유는 : ' . $ex->getMessage () ;
}

?>