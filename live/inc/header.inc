<!-- 부트스트랩 -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <script src="/js/bootstrap.bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.28.0/dist/feather.min.js" integrity="sha384-uO3SXW5IuS1ZpFPKugNNWqTZRRglnUJK6UAZ/gxOX80nxEkN9NcGZTftn6RzhGWE" crossorigin="anonymous"></script><script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js" integrity="sha384-zNy6FEbO50N+Cg5wap8IKA4M/ZnLJgzc6w2NqACZaK0u0FXfOWRRJOnQtpZun8ha" crossorigin="anonymous"></script>

    <!-- CSS -->
    <link href="/css/main.css?<?=date('Y-m-d h:i:s')?>" rel="stylesheet">
    <link href="/css/dashboard.css?<?=date('Y-m-d h:i:s')?>" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@100..900&display=swap" rel="stylesheet">

    <!-- JS -->
    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/common.js?<?=date('Y-m-d h:i:s')?>"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script> 
        var userAgent = navigator.userAgent.toLowerCase(); // 접속 핸드폰 정보
        
        // 모바일 홈페이지 바로가기 링크 생성
        if(userAgent.match('iphone')) {
            document.write('<link rel="apple-touch-icon" href="/img/quick_logo.png" />')
        } else if(userAgent.match('ipad')) {
            document.write('<link rel="apple-touch-icon" sizes="72*72" href="/img/quick_logo.png" />')
        } else if(userAgent.match('ipod')) {
            document.write('<link rel="apple-touch-icon" href="/img/quick_logo.png" />')
        } else if(userAgent.match('android')) { 
            document.write('<link rel="shortcut icon" href="/img/quick_logo.png" />')
        }
    </script>
