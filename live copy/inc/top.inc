<?
    $url = explode ( '/', $_SERVER['PHP_SELF'] );
    switch ( $url[1] ) :
        case 'live':
            switch ( $url[2] ) :
                case 'index.html':
                    $title = '생방송';
                break;

                case 'admin':
                    switch ( $url[3] ) :
                        case 'list.html':
                        case 'read.html':
                        case 'history.html':
                            $title = '관리자 페이지';

                            if( ! isset ( $_SESSION['username'] ) ){
                                echo "<script>alert('잘못된 접근입니다. 로그인 후 이용해주세요.')</script>";
                                echo "<script>location.href='<?=SITE_LIVE?>index.html'</script>";
                                return;
                            }
                        break;

                        default:
                            $title = '생방송';
                        break;
                    endswitch;
                break;

                default:
                    $title = '생방송';
                break;
            endswitch;
        break;

        case 'www':
        break;

        default:
            $title = '생방송';
        break;
    endswitch;
?>

<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?=$title?> | 하늘문생방송예배</title>
        <? include_once 'header.inc'; ?>
    </head>
    <body>