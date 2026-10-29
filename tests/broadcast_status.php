<?php
require_once __DIR__.'/../core/Model.php';
require_once __DIR__.'/../app/models/Admin_model.php';
require_once __DIR__.'/../app/models/Live_model.php';
class BroadcastStatement {
    private $sql; private $rows; private $selected=[];
    public function __construct($sql,$rows){$this->sql=$sql;$this->rows=$rows;}
    public function execute($params){
        $this->selected=[];
        foreach($this->rows as $row){
            if($row['church']!==$params['church'])continue;
            if(strpos($this->sql,"`use` = 'Y'")!==false&&$row['use']!=='Y')continue;
            if(strpos($this->sql,"`view` = 'Y'")!==false&&$row['view']!=='Y')continue;
            $this->selected[]=$row;
        }
        usort($this->selected,function($a,$b){return $b['idx']<=>$a['idx'];});
    }
    public function fetch($mode=null){return $this->selected[0]??false;}
    public function fetchColumn(){return count($this->selected);}
}
class BroadcastDB {
    private $rows;
    public function __construct($rows){$this->rows=$rows;}
    public function prepare($sql){return new BroadcastStatement($sql,$this->rows);}
}
class BroadcastAdmin extends Admin_model {public function __construct($db){$this->db=$db;}}
class BroadcastLive extends Live_model {public function __construct($db){$this->db=$db;}}
$count=0;
foreach(['sky','beer']as $church){
    foreach([false,true]as $active){
        $rows=[];
        foreach([['N','Y'],['Y','N'],['N','N']]as $i=>$flags)$rows[]=['idx'=>100+$i,'church'=>$church,'use'=>$flags[0],'view'=>$flags[1],'link'=>'old-'.$i];
        $rows[]=['idx'=>200,'church'=>$church==='sky'?'beer':'sky','use'=>'Y','view'=>'Y','link'=>'other-church'];
        if($active){$rows[]=['idx'=>1,'church'=>$church,'use'=>'Y','view'=>'Y','link'=>'active'];$rows[]=['idx'=>2,'church'=>$church,'use'=>'Y','view'=>'Y','link'=>'newest-active'];}
        $db=new BroadcastDB($rows);$admin=new BroadcastAdmin($db);$live=new BroadcastLive($db);
        $expected=$active?'newest-active':null;
        foreach([$admin->get_live_worship($church),$live->get_active_live($church)]as $row){
            if(($row['link']??null)!==$expected)throw new RuntimeException('Inactive/historical broadcast leaked');$count++;
        }
        if($live->get_live_status($church)!==($active?2:0))throw new RuntimeException('Incorrect on-air indicator');$count++;
    }
}
echo 'PASS: ',$count,' broadcast selection checks across use/view flags, churches and newest active rows.',PHP_EOL;
