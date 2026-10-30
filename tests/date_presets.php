<?php
require_once __DIR__ . '/../app/helpers/Admin_filters.php';
$count=0;
function date_check($ok,$message){global $count;if(!$ok)throw new RuntimeException($message);$count++;}
$today=new DateTimeImmutable('2026-10-29',new DateTimeZone('Asia/Seoul'));
foreach(['week'=>'2026-10-23','month'=>'2026-09-29','quarter'=>'2026-07-29','year'=>'2025-10-29']as $range=>$start){
 $f=Admin_filters::parse(['range'=>$range],$today);
 date_check($f['start_date']===$start&&$f['end_date']==='2026-10-29'&&$f['range']===$range,'Preset '.$range);
}
$f=Admin_filters::parse([],$today);date_check($f['range']==='week'&&$f['start_date']==='2026-10-23','Default inclusive seven days');
$f=Admin_filters::parse(['range'=>'month'],new DateTimeImmutable('2026-03-31'));
date_check($f['start_date']==='2026-02-28','Month-end clamped');
$f=Admin_filters::parse(['range'=>'year'],new DateTimeImmutable('2024-02-29'));
date_check($f['start_date']==='2023-02-28','Leap-year clamped');
$f=Admin_filters::parse(['start_date'=>'2026-01-01','end_date'=>'2026-02-01'],$today);
date_check($f['range']==='custom'&&$f['start_date']==='2026-01-01','Existing date URLs remain custom');
$f=Admin_filters::parse(['range'=>'custom','start_date'=>'','end_date'=>''],$today);
date_check($f['start_date']===''&&$f['end_date']==='','Explicit empty custom dates remain unbounded');
$f=Admin_filters::parse(['range'=>'quarter','church'=>'sky','preacher'=>'시험','q'=>'검색','per_page'=>50],$today);
date_check($f['q']==='검색'&&$f['church']==='sky'&&$f['per_page']===50&&$f['preacher']==='시험','Other filters preserved');
$failed=false;try{Admin_filters::parse(['range'=>'invalid'],$today);}catch(InvalidArgumentException $e){$failed=true;}
date_check($failed,'Invalid preset rejected');
$f=Admin_filters::parse(['range'=>'today'],$today);
date_check($f['start_date']==='2026-10-29'&&$f['end_date']==='2026-10-29','Today covers one day');
$f=Admin_filters::for_kind('history',[]);
date_check($f['range']==='today'&&$f['start_date']===$f['end_date'],'History defaults to today');
$f=Admin_filters::for_kind('history',['range'=>'week']);
date_check($f['range']==='week','History explicit preset preserved');
$f=Admin_filters::for_kind('history',['start_date'=>'2026-01-01','end_date'=>'2026-01-31']);
date_check($f['range']==='custom'&&$f['start_date']==='2026-01-01','History custom dates preserved');
$f=Admin_filters::for_kind('live_list',[]);
date_check($f['range']==='week','Other date lists retain week default');
echo 'PASS: ',$count,' date preset/default/custom/month-end checks.',PHP_EOL;
