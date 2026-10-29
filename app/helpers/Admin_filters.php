<?php
class Admin_filters {
    public static function specs() {
        return [
            'live_list' => ['table'=>'live_list','date'=>'title','label'=>'예배일','fields'=>['title','worship','link'],'order'=>'idx DESC'],
            'video_list' => ['table'=>'video_list','date'=>'title','label'=>'예배일','fields'=>['title','worship','preacher','link'],'order'=>'idx DESC'],
            'history' => ['table'=>'live_history','date'=>'login_date','label'=>'입장일','fields'=>['name','branch'],'order'=>'idx DESC'],
            'black_list' => ['table'=>'black_list','date'=>'insert_date','label'=>'등록일','fields'=>['nickname'],'order'=>'idx ASC'],
            'admin_list' => ['table'=>'member_admin','date'=>'insert_date','label'=>'등록일','fields'=>['username','church'],'order'=>'level, idx','select'=>'idx, username, church, level, insert_date, update_date'],
            'preacher_list' => ['table'=>'preacher_list','date'=>'insert_date','label'=>'등록일','fields'=>['name'],'order'=>'idx ASC'],
            'notice' => ['table'=>'notice','date'=>'start_date','label'=>'공지 기간','fields'=>['title','contents'],'order'=>'idx DESC','select'=>'idx, enable, DATE(start_date) AS start_date, DATE(end_date) AS end_date, title, insert_date, update_date'],
        ];
    }
    public static function uses_dates($kind) {
        return !in_array($kind, ['black_list','admin_list','preacher_list'], true);
    }
    public static function for_kind($kind, array $query) {
        if (!self::uses_dates($kind)) {
            $query = array_intersect_key($query, array_flip(['page','per_page','q']));
            $query['range'] = 'custom';
        }
        return self::parse($query);
    }
    private static function scalar(array $query, $key) {
        $value = $query[$key] ?? '';
        if (!is_scalar($value)) throw new InvalidArgumentException('검색 조건을 확인해 주세요.');
        return trim((string) $value);
    }
    public static function parse(array $query, ?DateTimeImmutable $today = null) {
        $size = (int) self::scalar($query, 'per_page');
        $result = ['per_page'=>in_array($size,[10,20,50,100],true)?$size:10,
            'page'=>max(1,(int) self::scalar($query,'page'))];
        foreach (['q','start_date','end_date','church','preacher'] as $key) $result[$key]=self::scalar($query,$key);
        $range = self::scalar($query, 'range');
        if ($range === '') $range = array_key_exists('start_date', $query) || array_key_exists('end_date', $query) ? 'custom' : 'week';
        if (!in_array($range, ['week','month','quarter','year','custom'], true)) throw new InvalidArgumentException('조회 기간을 확인해 주세요.');
        $result['range'] = $range;
        if ($range !== 'custom') {
            $today = ($today ?? new DateTimeImmutable('today', new DateTimeZone('Asia/Seoul')))->setTime(0, 0);
            $result['end_date'] = $today->format('Y-m-d');
            if ($range === 'week') {
                $result['start_date'] = $today->modify('-6 days')->format('Y-m-d');
            } else {
                $months = ['month'=>1,'quarter'=>3,'year'=>12][$range];
                $month = $today->modify('first day of this month')->modify('-' . $months . ' months');
                $day = min((int) $today->format('d'), (int) $month->format('t'));
                $result['start_date'] = $month->setDate((int) $month->format('Y'), (int) $month->format('m'), $day)->format('Y-m-d');
            }
        }
        if (preg_match('//u',$result['q'])!==1 || preg_match('//u',$result['preacher'])!==1 || preg_match_all('/./us',$result['q'])>100 || preg_match_all('/./us',$result['preacher'])>100) throw new InvalidArgumentException('검색어는 100자 이내로 입력해 주세요.');
        foreach (['start_date','end_date'] as $key) {
            if ($result[$key]==='') continue;
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$result[$key],$m) || !checkdate((int)$m[2],(int)$m[3],(int)$m[1]) || (int)$m[1]>9998) throw new InvalidArgumentException('검색 날짜를 확인해 주세요.');
        }
        if ($result['start_date']!=='' && $result['end_date']!=='' && $result['start_date']>$result['end_date']) throw new InvalidArgumentException('종료일은 시작일 이후여야 합니다.');
        if (!in_array($result['church'],['','sky','beer'],true)) throw new InvalidArgumentException('교회 조건을 확인해 주세요.');
        return $result;
    }
    public static function where($kind,array $filters) {
        $specs=self::specs();
        if (!isset($specs[$kind])) throw new InvalidArgumentException('지원하지 않는 목록입니다.');
        $spec=$specs[$kind];$where=[];$params=[];
        if (in_array($kind,['live_list','video_list'],true)) {
            if ($filters['church']!=='') {$where[]='church = :church';$params['church']=$filters['church'];}
            else $where[]="church IN ('sky','beer')";
        }
        if ($kind==='video_list' && $filters['preacher']!=='') {$where[]='preacher = :preacher';$params['preacher']=$filters['preacher'];}
        if (self::uses_dates($kind) && $filters['start_date']!=='') {
            $where[]=$kind==='notice'?'end_date >= :date_start':'`'.$spec['date'].'` >= :date_start';
            $params['date_start']=$filters['start_date'];
        }
        if (self::uses_dates($kind) && $filters['end_date']!=='') {
            $where[]='`'.$spec['date'].'` < DATE_ADD(:date_end, INTERVAL 1 DAY)';
            $params['date_end']=$filters['end_date'];
        }
        if ($filters['q']!=='') {
            $keyword='%'.strtr($filters['q'],['='=>'==','%'=>'=%','_'=>'=_']).'%';$parts=[];
            foreach ($spec['fields'] as $i=>$field) {$key='keyword'.$i;$parts[]='`'.$field.'` LIKE :'.$key." ESCAPE '='";$params[$key]=$keyword;}
            $where[]='('.implode(' OR ',$parts).')';
        }
        return [$where?' WHERE '.implode(' AND ',$where):'', $params];
    }
}
