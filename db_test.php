<?
    // DB 연결
    $hostname = 'localhost';
    $user = 'sky0808net';
    $password = 'qkdthdtlf08!';
    $dbname = 'sky0808';

    $DB = mysqli_connect($hostname, $user, $password, $dbname);

    // try {
    //     $DB = new PDO ( 'mysql:host=localhost; dbname=sky0808;', 'sky0808net', 'qkdthdtlf08!' ) ;
    //     $DB->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ) ;
    // }
    // // 연결 실패시 원인 출력
    // catch ( PDOException $ex ) {
    //     echo 'DB 연결 실패... 이유는 : ' . $ex->getMessage () ;
    // }

    if($DB){
        echo 'DB connect Success';
    } else{
        echo 'Failed..';
    }
?>