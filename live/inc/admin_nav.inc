<?
// 기본 설정 파일을 불러온다
include_once 'config.inc' ;

if ( ! empty ( $_SESSION['admin_username'] ) ) {
    $admin_info = $DB->query( "SELECT * FROM `member_admin` WHERE `username` = '" . $_SESSION['admin_username'] . "'" )->fetch() ;
}
?>

<div class="nav-menu-mobile">
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a href="<?=SITE_LIVE?>/admin/live/live_list.html" alt="하늘문교회기도원"><img class="admin-logo" src="/img/main_logo.png"></a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </header>
</div>
<div class="nav-menu">
    <div class="text-center">
        <a href="<?=SITE_LIVE?>/admin/" alt="하늘문교회기도원"><img class="admin-logo" src="/img/main_logo.png"></a>
    </div>
    <? if ( ! empty ( $admin_info ) && $admin_info['live_manage'] == 'Y' ) : ?>
    <a class="live-nav-top" href="#">생방송</a>
    <div class="live-nav-bottom">
        <a class="nav-side" href="<?=SITE_LIVE?>/admin/">　생방송 보기</a>
        <? if ( ! empty ( $admin_info ) && $admin_info['live_list'] == 'Y' ) : ?>
        <a class="nav-side" href="<?=SITE_LIVE?>/admin/live/live_list.html">　생방송 링크 관리</a>
        <? endif ; ?>
        <? if ( ! empty ( $admin_info ) && $admin_info['history'] == 'Y' ) : ?>
        <a class="nav-side" href="<?=SITE_LIVE?>/admin/live/history.html">　생방송 접속 이력</a>
        <? endif ; ?>
        <? if ( ! empty ( $admin_info ) && $admin_info['black_list'] == 'Y' ) : ?>
        <a class="nav-side" href="<?=SITE_LIVE?>/admin/live/black_list.html">　블랙리스트 관리</a>
        <? endif ; ?>
    </div>
    <? endif ; ?>
    <? if ( ! empty ( $admin_info ) && $admin_info['video_list'] == 'Y' ) : ?>
    <a class="video-nav-top" href="#">다시보기</a>
    <div class="video-nav-bottom">
        <a class="nav-side" href="<?=SITE_LIVE?>/admin/video/video_list.html">　연도별</a>
        <? if ( ! empty ( $admin_info ) && $admin_info['event_video'] == 'Y' ) : ?>
        <a class="nav-side" href="<?=SITE_LIVE?>/admin/video/event_video.html">　행사</a>
        <? endif ; ?>
    </div>
    <? endif ; ?>
    <? if ( ! empty ( $admin_info ) && $admin_info['level'] == 1 ) : ?>
    <a class="member-nav-top" href="#">회원 관리</a>
    <div class="member-nav-bottom">
        <a class="nav-side" href="<?=SITE_LIVE?>/admin/member/member_admin.html">　관리자 관리</a>
        <a class="nav-side" href="<?=SITE_LIVE?>/admin/member/member_saint.html">　성도 관리</a>
    </div>
    <? endif ; ?>
    <a class="nav-logout" href="<?=SITE_LIVE?>/admin/admin_logout.php?name=<?=$_SESSION['admin_username']?>">로그아웃</a>
</div>