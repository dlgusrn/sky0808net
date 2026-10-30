<?php
require_once __DIR__.'/../app/helpers/Admin_filters.php';
require_once __DIR__.'/../app/helpers/pagination_helper.php';
$count=0;
function control_check($ok,$message){global $count;if(!$ok)throw new RuntimeException($message);$count++;}
function controls($list_kind){
 $filters=Admin_filters::for_kind($list_kind,['per_page'=>50]);$page_set=50;$total=123;$date_label='등록일';
 ob_start();include __DIR__.'/../app/views/admin/list_filters.php';return ob_get_clean();
}
foreach(['black_list','admin_list','preacher_list']as $kind){
 $f=Admin_filters::for_kind($kind,['per_page'=>50,'page'=>2,'range'=>'invalid','q'=>'시험','start_date'=>'invalid','end_date'=>'1900-01-01','church'=>'bad']);
 control_check($f['q']==='시험'&&$f['start_date']===''&&$f['end_date']===''&&$f['range']==='custom','Stale conditions ignored');
 control_check($f['per_page']===50&&$f['page']===2,'Count and page preserved');
 list($where,$params)=Admin_filters::where($kind,$f);control_check(strpos($where,'LIKE')!==false&&isset($params['keyword0'])&&!isset($params['date_start'])&&!isset($params['date_end']),'Keyword query without dates');
 $html=controls($kind);
 control_check(strpos($html,'name="per_page"')!==false&&strpos($html,'50개')!==false,'Count selector retained');
 control_check(strpos($html,'name="q"')!==false,'Keyword control retained');
 foreach(['range','start_date','end_date']as $name)control_check(strpos($html,'name="'.$name.'"')===false,'No search controls rendered');
 $_GET=['url'=>'admin/'.$kind,'q'=>'old','range'=>'week','start_date'=>'1900-01-01','per_page'=>50];
 $html=render_pagination(1,8,1,5,400);
 preg_match_all('/href="([^"]*)"/',$html,$links);
 foreach($links[1]as $link){parse_str(parse_url(html_entity_decode($link,ENT_QUOTES,'UTF-8'),PHP_URL_QUERY),$query);control_check(isset($query['per_page'],$query['page'])&&count($query)===3&&$query['q']==='old','Pagination drops dates but keeps keyword');}
}
foreach(['live_list','video_list','history','notice']as $kind){
 $f=Admin_filters::for_kind($kind,[]);control_check($f['range']===($kind==='history'?'today':'week')&&$f['start_date']!=='','Searchable menus retain default dates');
 $html=controls($kind);control_check(strpos($html,'name="q"')!==false&&strpos($html,'name="start_date"')!==false,'Search controls remain');
 control_check(strpos($html,'<select name="range"')!==false&&preg_match('/<option value="today"[^>]*>오늘<\/option>/',$html)===1,'Today available on all date lists');
}
echo 'PASS: ',$count,' keyword-only controls and stale date query checks.',PHP_EOL;
