<?php
// Read-only integration checks against the dev database; no records are changed.
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/Admin_listing.php';
$model=new Admin_listing();$count=0;
foreach(array_keys(Admin_filters::specs())as $kind){
 foreach([['per_page'=>20],['per_page'=>50,'page'=>999999,'q'=>'MVC_FILTER_TEST_NO_MATCH'],['start_date'=>'2026-01-01','end_date'=>'2026-12-31','q'=>'시험']]as $query){
  $data=$model->page($kind,$query);
  if(count($data['list'])>$data['page_set']||$data['page']<1||$data['page']>$data['total_page'])throw new RuntimeException('Invalid list page: '.$kind);
  $count++;
 }
}
foreach(['black_list','admin_list','preacher_list'] as $kind){
 $all=$model->page($kind,['per_page'=>10]);
 $stale=$model->page($kind,['per_page'=>20,'range'=>'invalid','q'=>'','start_date'=>'invalid','end_date'=>'1900-01-01','church'=>'invalid']);
 if($all['total']!==$stale['total']||$stale['page_set']!==20)throw new RuntimeException('Full list affected by stale filters: '.$kind);
 $matched=$model->page($kind,['q'=>'시험','range'=>'invalid','start_date'=>'invalid']);
 if($matched['total']>$all['total'])throw new RuntimeException('Keyword search ignored: '.$kind);
}
$model->page('video_list',['church'=>'sky','preacher'=>'시험설교자','q'=>'시험','per_page'=>100]);
echo 'PASS: ',$count+10,' read-only database search/list queries.',PHP_EOL;
