<!-- 부트스트랩 -->
<link href="/css/bootstrap.min.css" rel="stylesheet">
<script src="/js/bootstrap.bundle.js"></script>

<!-- CSS -->
<link href="/css/main.css?<?=date('Y-m-d h:i:s')?>" rel="stylesheet">

<!-- JS -->
<script src="/js/jquery-3.6.0.min.js"></script>
<script src="<?=SITE_LIVE?>/js/common.js?<?=date('Y-m-d h:i:s')?>"></script>

<script> 
    var userAgent = navigator.userAgent.toLowerCase(); // 접속 핸드폰 정보
       
    // 모바일 홈페이지 바로가기 링크 생성
    if(userAgent.match('iphone')) {
        document.write('<link rel="apple-touch-icon" href="<?=SITE_LIVE?>img/quick_logo.png" />')
    } else if(userAgent.match('ipad')) {
        document.write('<link rel="apple-touch-icon" sizes="72*72" href="<?=SITE_LIVE?>img/quick_logo.png" />')
    } else if(userAgent.match('ipod')) {
        document.write('<link rel="apple-touch-icon" href="<?=SITE_LIVE?>img/quick_logo.png" />')
    } else if(userAgent.match('android')) { 
        document.write('<link rel="shortcut icon" href="<?=SITE_LIVE?>img/quick_logo.png" />')
    }
</script>