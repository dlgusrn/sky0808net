<?php
require_once __DIR__.'/../app/views/layout/auth.inc';
require_once __DIR__.'/../app/helpers/pagination_helper.php';
require_once __DIR__.'/../config/config.php';
$_SERVER['REQUEST_URI']='/admin/black_list';
$_SESSION=[];
$count=0;
function form_check($ok,$message){global $count;if(!$ok)throw new RuntimeException($message);$count++;}
foreach(glob(__DIR__.'/../app/views/admin/*')as $path){
    if(!preg_match('/\.(php|html)$/',$path))continue;
    $html=file_get_contents($path);
    preg_match_all('~<(button|a)\b([^>]*)>(저장|취소)</\1>~u',$html,$buttons,PREG_SET_ORDER);
    $previous=null;
    foreach($buttons as $button){
        form_check(strpos($button[2],$button[3]==='저장'?'btn-primary':'btn-secondary')!==false,basename($path).' button color');
        if($button[3]==='취소')form_check($previous==='저장',basename($path).' save must precede cancel');
        $previous=$button[3];
    }
}
$html=file_get_contents(__DIR__.'/../app/views/admin/live_read.php');
form_check(preg_match('/<input type="date"[^>]*name="title"/',$html)===1,'Live edit uses native date input');
$html=file_get_contents(__DIR__.'/../app/views/admin/notice.php');
form_check(strpos($html,'id="notice-delete" hidden')!==false&&strpos($html,"$('#notice-delete').prop('hidden', true)")!==false,'Delete hidden for new notices');
form_check(strpos($html,"$('#notice-delete').prop('hidden', false)")!==false&&strpos($html,"url:'/admin/del_notice', type:'POST'")!==false,'Edit shows delete and posts');
// Render the real view to verify descending ordinal numbers on later pages.
$list=[['idx'=>30,'nickname'=>'test-a'],['idx'=>29,'nickname'=>'test-b']];
$total=25;$page=2;$page_set=10;$total_page=3;$block_start=1;$block_end=3;
$filters=['q'=>''];$list_kind='black_list';$_GET['url']='admin/black_list';
ob_start();include __DIR__.'/../app/views/admin/black_list.php';$html=ob_get_clean();
form_check(strpos($html,'<td>15</td>')!==false&&strpos($html,'<td>14</td>')!==false,'Blacklist page 2 numbers descend from filtered total');
echo 'PASS: ',$count,' admin save/cancel, date input, notice deletion UI and blacklist numbering checks.',PHP_EOL;
