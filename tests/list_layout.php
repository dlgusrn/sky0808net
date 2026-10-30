<?php
require_once __DIR__.'/../app/views/layout/auth.inc';
require_once __DIR__.'/../app/helpers/pagination_helper.php';
require_once __DIR__.'/../config/config.php';
$count=0;
function layout_check($ok,$message){global $count;if(!$ok)throw new RuntimeException($message);$count++;}
function layout_page($kind){
    $_SERVER['REQUEST_URI']='/admin/'.$kind;$_GET=['url'=>'admin/'.$kind,'q'=>'test','per_page'=>20];
    $_SESSION=['admin_user_name'=>'테스트','csrf_token'=>'test','admin_info'=>[]];
    $list_kind=$kind;$filters=Admin_filters::for_kind($kind,['q'=>'test','per_page'=>20]);
    $list=[];$admin_list=[];$video_list=[];$preacher_list=[];$pass_row=['passwd'=>'test'];
    $total=260;$page=2;$page_set=20;$total_page=13;$block_start=1;$block_end=5;
    $yoil=['일','월','화','수','목','금','토'];
    ob_start();include __DIR__.'/../app/views/admin/'.$kind.'.php';return ob_get_clean();
}
foreach(['live_list','video_list','history','black_list','admin_list','preacher_list','notice']as $kind){
    $html=layout_page($kind);
    layout_check(substr_count($html,'aria-label="목록 도구"')===1,'One toolbar: '.$kind);
    layout_check(substr_count($html,'aria-label="Page navigation"')===1,'Pagination appears once: '.$kind);
    layout_check(strpos($html,'aria-label="목록 도구"')<strpos($html,'<table'),'Toolbar before table: '.$kind);
    layout_check(strpos($html,'class="admin-list-toolbar-left"')<strpos($html,'class="admin-list-toolbar-right"'),'Count and actions precede pagination: '.$kind);
    layout_check(strpos($html,'aria-label="Page navigation"')<strpos($html,'id="list-per-page"'),'Size follows pagination: '.$kind);
    layout_check(strpos($html,'form="admin-list-filter-form"')!==false,'Size select belongs to filter form: '.$kind);
    layout_check(strpos($html,'name="page"')===false,'Filter changes reset page: '.$kind);
    if(Admin_filters::uses_dates($kind)){
        layout_check(strpos($html,'<select name="range"')!==false&&strpos($html,'data-date-range=')===false,'Period is select: '.$kind);
        layout_check(strpos($html,'value="'.($kind==='history'?'today':'week').'" selected')!==false,'Default period preserved: '.$kind);
    }else{
        layout_check(strpos($html,'name="range"')===false&&strpos($html,'name="start_date"')===false,'No dates for keyword-only menus: '.$kind);
    }
    layout_check((strpos($html,'>등록하기</button>')!==false)===($kind!=='history'),'Appropriate add action: '.$kind);
    if(in_array($kind,['live_list','video_list'],true))layout_check(strpos($html,'id="list-church"')!==false&&strpos($html,'id="church_select"')===false,'Single church filter: '.$kind);
    if($kind==='video_list')layout_check(strpos($html,'id="list-preacher"')!==false&&strpos($html,'id="preacher_select"')===false,'Single preacher filter');
    if($kind==='notice'){
        $modal=strpos($html,'<div class="modal fade" id="notice-modal"');
        $form=strpos($html,'id="notice-form"');
        layout_check($modal!==false&&$form!==false&&$modal>strpos($html,'</main>')&&$form>$modal,'Notice form belongs to a modal after the main list');
        layout_check(substr_count($html,'id="notice-modal"')===1&&substr_count($html,'id="notice-form"')===1,'Exactly one notice modal and form');
    }
}
echo 'PASS: ',$count,' compact list layout, action placement, period and filter association checks.',PHP_EOL;
