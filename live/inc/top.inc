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
                        case 'index.html':
                        case 'live':
                        case 'video':
                        case 'member':
                            $title = '관리자 페이지';
                        break;
                        
                        if( ! isset ( $_SESSION['admin_username'] ) ){
                            echo "<script>alert('잘못된 접근입니다. 로그인 후 이용해주세요.')</script>";
                            echo "<script>location.href='".SITE_LIVE."/admin'</script>";
                            return;
                        }

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

<html lang="ko">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta property="og:image" content="/dev/img/link.jpg">
        <title><?=$title?> | 하늘문생방송예배</title>
        <? include_once 'header.inc'; ?>
    </head>
    <body>