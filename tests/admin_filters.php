<?php
require_once __DIR__ . '/../app/helpers/Admin_filters.php';
$count=0;
function filter_check($ok,$message){global $count;if(!$ok)throw new RuntimeException($message);$count++;}
foreach([10,20,50,100] as $size)filter_check(Admin_filters::parse(['per_page'=>$size])['per_page']===$size,'Supported size');
filter_check(Admin_filters::parse(['per_page'=>'9999','page'=>'-5'])['per_page']===10,'Safe size fallback');
filter_check(Admin_filters::parse(['page'=>'-5'])['page']===1,'Page bound');
foreach([['start_date'=>'2026-02-30'],['start_date'=>'2026-11-01','end_date'=>'2026-10-29'],['q'=>['bad']],['church'=>'invalid'],['q'=>str_repeat('가',101)]]as $bad){
 $failed=false;try{Admin_filters::parse($bad);}catch(InvalidArgumentException $error){$failed=true;}filter_check($failed,'Invalid search rejected');
}
$f=Admin_filters::parse(['church'=>'sky','preacher'=>'시험설교자','q'=>"%_' OR 1=1",'start_date'=>'2026-10-01','end_date'=>'2026-10-29']);
foreach(array_keys(Admin_filters::specs())as $kind){
 list($where,$params)=Admin_filters::where($kind,$f);

 filter_check(strpos($where,"OR 1=1")===false,'Keyword stays out of SQL');
 filter_check($params['keyword0']==="%=%=_' OR 1==1%",'Literal LIKE escaping');
 if(Admin_filters::uses_dates($kind)){
  filter_check(strpos($where,'DATE_ADD(:date_end, INTERVAL 1 DAY)')!==false,'Inclusive end date');
  filter_check($params['date_start']==='2026-10-01'&&$params['date_end']==='2026-10-29','Bound date parameters');
 }else{filter_check(!isset($params['date_start'])&&!isset($params['date_end']),'No date restrictions');}
}
list($where,$params)=Admin_filters::where('notice',$f);
filter_check(strpos($where,'end_date >= :date_start')!==false,'Notice period overlap');
list($where,$params)=Admin_filters::where('video_list',$f);
filter_check($params['church']==='sky'&&$params['preacher']==='시험설교자','Combined filters');
$failed=false;try{Admin_filters::where('arbitrary_table',$f);}catch(InvalidArgumentException $e){$failed=true;}filter_check($failed,'Unknown table denied');
echo 'PASS: ',$count,' list size, search validation, SQL binding and date checks.',PHP_EOL;
