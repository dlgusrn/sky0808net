<div class="nav-menu-mobile">
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
        <a href="/admin/main" alt="하늘문교회기도원"><img class="admin-logo" src="/img/main_logo.png"></a>
        <button class="navbar-toggler position-absolute d-md-none collapsed mobile-nav" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </header>
    <div class="div-mobile-nav" style="display: none;">
        <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['live_manage'] == 'Y' ) : ?>
        <a class="live-nav-top" href="#">생방송</a>
        <div class="live-nav-bottom">
            <a class="nav-side" href="/admin/main">　생방송 보기</a>
            <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['live_list'] == 'Y' ) : ?>
            <a class="nav-side" href="/admin/live_list">　생방송 링크 관리</a>
            <? endif ; ?>
            <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['history'] == 'Y' ) : ?>
            <a class="nav-side" href="/admin/history">　생방송 접속 이력</a>
            <? endif ; ?>
            <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['black_list'] == 'Y' ) : ?>
            <a class="nav-side" href="/admin/black_list">　블랙리스트 관리</a>
            <? endif ; ?>
        </div>
        <? endif ; ?>
        <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['video_list'] == 'Y' ) : ?>
        <a class="video-nav-top" href="#">다시보기</a>
        <div class="video-nav-bottom">
            <a class="nav-side" href="/admin/video_list">　연도별</a>
            <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['event_video'] == 'Y' ) : ?>
            <!-- <a class="nav-side" href="/admin/event_list">　행사</a> -->
            <? endif ; ?>
        </div>
        <? endif ; ?>
        <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['level'] == 1 ) : ?>
        <a class="member-nav-top" href="#">회원 관리</a>
        <div class="member-nav-bottom">
            <a class="nav-side" href="/admin/admin_list">　관리자 관리</a>
            <a class="nav-side" href="/admin/preacher_list">　설교자 관리</a>
            <!-- <a class="nav-side" href="/admin/saint_list">　성도 관리</a> -->
        </div>
        <? endif ; ?>
        <a class="nav-logout" href="/admin/admin_logout_proc">로그아웃</a>
    </div>
</div>
<div class="nav-menu">
    <div class="text-center">
        <a href="/admin/" alt="하늘문교회기도원"><img class="admin-logo" src="/img/main_logo.png"></a>
    </div>
    <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['live_manage'] == 'Y' ) : ?>
    <a class="live-nav-top" href="#">생방송</a>
    <div class="live-nav-bottom">
        <a class="nav-side" href="/admin/main">　생방송 보기</a>
        <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['live_list'] == 'Y' ) : ?>
        <a class="nav-side" href="/admin/live_list">　생방송 링크 관리</a>
        <? endif ; ?>
        <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['history'] == 'Y' ) : ?>
        <a class="nav-side" href="/admin/history">　생방송 접속 이력</a>
        <? endif ; ?>
        <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['black_list'] == 'Y' ) : ?>
        <a class="nav-side" href="/admin/black_list">　블랙리스트 관리</a>
        <? endif ; ?>
    </div>
    <? endif ; ?>
    <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['video_list'] == 'Y' ) : ?>
    <a class="video-nav-top" href="#">다시보기</a>
    <div class="video-nav-bottom">
        <a class="nav-side" href="/admin/video_list">　연도별</a>
        <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['event_video'] == 'Y' ) : ?>
        <!-- <a class="nav-side" href="/admin/event_list">　행사</a> -->
        <? endif ; ?>
    </div>
    <? endif ; ?>
    <? if ( ! empty ( $_SESSION['admin_info'] ) && $_SESSION['admin_info']['level'] == 1 ) : ?>
    <a class="member-nav-top" href="#">회원 관리</a>
    <div class="member-nav-bottom">
        <a class="nav-side" href="/admin/admin_list">　관리자 관리</a>
        <a class="nav-side" href="/admin/preacher_list">　설교자 관리</a>
        <!-- <a class="nav-side" href="/admin/saint_list">　성도 관리</a> -->
    </div>
    <? endif ; ?>
    <a class="nav-logout" href="/admin/admin_logout_proc">로그아웃</a>
</div>