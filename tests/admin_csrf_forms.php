<?php
require_once __DIR__.'/../app/views/layout/auth.inc';
require_once __DIR__.'/../app/helpers/pagination_helper.php';
require_once __DIR__.'/../config/config.php';
$_SESSION=['csrf_token'=>'test-form-token','admin_user_name'=>'테스트관리자','admin_info'=>['username'=>'테스트관리자','level'=>1,'live_manage'=>'Y','live_list'=>'Y','history'=>'Y','black_list'=>'Y','video_list'=>'Y','event_video'=>'N','notice'=>'Y']];
function render_csrf_page($name){
    $_SERVER['REQUEST_URI']='/admin/'.$name;$_GET=['url'=>'admin/'.$name];
    $list_kind=$name;$filters=Admin_filters::for_kind($name,[]);
    $page=1;$page_set=10;$total=0;$total_page=1;$block_start=1;$block_end=1;$date_label='날짜';
    $list=[];$admin_list=[];$video_list=[];$preacher_list=[];$pass_row=['passwd'=>'test-password'];
    $yoil=['일','월','화','수','목','금','토'];
    $idx=1;
    $row=['idx'=>1,'church'=>'sky','title'=>'2026-10-30','worship'=>'예배','link'=>'eS6_s4GpYtk','passwd'=>'test-password','use'=>'N','view'=>'N','date_insert'=>'2026-10-30','date_update'=>null,'username'=>'테스트관리자','level'=>1,'insert_date'=>'2026-10-30','update_date'=>null,'preacher'=>'테스트'];
    $admin=$row+$_SESSION['admin_info'];$video=$row;$live_link=$row;
    if($name==='live_list'){$list=[$row];$total=1;}
    ob_start();include __DIR__.'/../app/views/admin/'.$name.'.php';return ob_get_clean();
}
$count=0;
foreach(['login','admin_list','admin_read','black_list','preacher_list','video_list','video_read','live_list','live_read','notice']as $name){
    $html=render_csrf_page($name);
    // Rendered forms are flat; inspect the actual output rather than PHP source templates.
    preg_match_all('~<form\b([^>]*)>(.*?)</form>~s',$html,$forms,PREG_SET_ORDER);
    $expectedActions=['login'=>'admin_login_proc','admin_list'=>'set_admin','admin_read'=>'set_admin','black_list'=>'set_black_list','preacher_list'=>'set_preacher','video_list'=>'set_video_list','video_read'=>'set_video_list','live_list'=>'set_live_list','live_read'=>'set_live_list','notice'=>'set_notice'];
    $hasExpectedForm=false;
    foreach($forms as $form){
        if(strpos($form[1],'method="post"')===false)continue;
        preg_match_all('~<input\b[^>]*name="csrf"[^>]*>~',$form[2],$tokens);
        if(count($tokens[0])!==1||strpos($tokens[0][0],'value="test-form-token"')===false)throw new RuntimeException('Missing/duplicate/malformed CSRF field: '.$name);
        if(strpos($form[1],'action="/admin/'.$expectedActions[$name])!==false)$hasExpectedForm=true;
        $count++;
    }
    if(!$hasExpectedForm)throw new RuntimeException('Missing actual mutation form: '.$name);
    $count++;
    if($name==='live_list'){
        if(!preg_match('~<button[^>]*class="view [^>]* disabled>~',$html))throw new RuntimeException('Inactive transmission button must be disabled');
        $count++;
    }
}
echo 'PASS: ',$count,' rendered admin POST form tokens and inactive transmission UI checks.',PHP_EOL;
