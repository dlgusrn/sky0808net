<?php
require_once __DIR__ . '/../helpers/Admin_filters.php';
class Admin_listing extends Model {
    public function page($kind,array $query) {
        $specs=Admin_filters::specs();
        if (!isset($specs[$kind])) throw new InvalidArgumentException('지원하지 않는 목록입니다.');
        $spec=$specs[$kind];$filters=Admin_filters::parse($query);
        list($where,$params)=Admin_filters::where($kind,$filters);
        $statement=$this->db->prepare('SELECT COUNT(*) FROM `'.$spec['table'].'`'.$where);
        $statement->execute($params);$total=(int)$statement->fetchColumn();
        $last=max(1,(int)ceil($total/$filters['per_page']));$page=min($last,$filters['page']);
        $offset=($page-1)*$filters['per_page'];$block_start=(int)floor(($page-1)/5)*5+1;
        $select=$spec['select']??'*';
        $statement=$this->db->prepare('SELECT '.$select.' FROM `'.$spec['table'].'`'.$where.' ORDER BY '.$spec['order'].' LIMIT '.$offset.', '.$filters['per_page']);
        $statement->execute($params);
        return ['list'=>$statement->fetchAll(PDO::FETCH_ASSOC),'filters'=>$filters,'list_kind'=>$kind,
            'date_label'=>$spec['label'],'page'=>$page,'page_set'=>$filters['per_page'],'block_set'=>5,
            'block'=>(int)ceil($page/5),'start_num'=>$offset,'total'=>$total,'total_page'=>$last,
            'block_start'=>$block_start,'block_end'=>min($last,$block_start+4),
            'church_select'=>$filters['church'],'preacher_select'=>$filters['preacher']];
    }
}
