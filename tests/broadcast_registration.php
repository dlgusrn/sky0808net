<?php
require_once __DIR__.'/../core/Model.php';
require_once __DIR__.'/../app/models/Admin_model.php';
class RegistrationStatement {
    private $db; private $sql; private $selected=false;
    public function __construct($db,$sql){$this->db=$db;$this->sql=$sql;}
    public function execute($params){
        if(strpos($this->sql,'GET_LOCK')!==false){$this->db->lock=$this->db->allowLock;$this->db->lockNames[]=$params['name'];return true;}
        if(strpos($this->sql,'RELEASE_LOCK')!==false){$this->db->lock=false;return true;}
        if(strpos($this->sql,'SELECT * FROM live_list')!==false){
            foreach($this->db->rows as $row)if($row['idx']==$params['idx'])$this->selected=$row;
        }elseif(strpos($this->sql,'idx <> :idx')!==false){
            foreach($this->db->rows as &$row)if($row['church']===$params['church']&&$row['idx']!=$params['idx']){$row['use']='N';$row['view']='N';}unset($row);
        }elseif(strpos($this->sql,'INSERT INTO live_list')!==false){
            if($this->db->failInsert)throw new RuntimeException('Simulated insert failure');
            if(strpos($this->sql,"'Y', 'N'")===false)throw new RuntimeException('Explicit registration flags missing');
            $this->db->rows[]=$params+['idx'=>100,'use'=>'Y','view'=>'N'];
        }elseif(strpos($this->sql,'DELETE FROM live_list')!==false){
            $this->db->rows=array_values(array_filter($this->db->rows,function($r)use($params){return $r['idx']!=$params['idx'];}));
        }elseif(strpos($this->sql,'UPDATE live_list')!==false){
            if($this->db->failUpdate)throw new RuntimeException('Simulated update failure');
            foreach($this->db->rows as &$row)if($row['idx']==$params['idx']){
                foreach($params as $key=>$value)$row[$key]=$value;
                if(strpos($this->sql,"`view` = 'N'")!==false)$row['view']='N';
            }unset($row);
        }
        return true;
    }
    public function fetchColumn(){return $this->db->allowLock?1:0;}
    public function fetch($mode=null){return $this->selected;}
}
class RegistrationDB {
    public $rows,$snapshot,$lock=false,$allowLock=true,$failInsert=false,$failUpdate=false,$transaction=false,$commits=0,$rollbacks=0,$lockNames=[];
    public function __construct($rows){$this->rows=$rows;}
    public function prepare($sql){return new RegistrationStatement($this,$sql);}
    public function beginTransaction(){$this->snapshot=$this->rows;$this->transaction=true;return true;}
    public function inTransaction(){return $this->transaction;}
    public function commit(){$this->transaction=false;$this->commits++;return true;}
    public function rollBack(){$this->rows=$this->snapshot;$this->transaction=false;$this->rollbacks++;return true;}
}
class RegistrationAdmin extends Admin_model {public function __construct($db){$this->db=$db;}}
$count=0;
function registration_check($ok,$message){global $count;if(!$ok)throw new RuntimeException($message);$count++;}
$rows=[['idx'=>1,'church'=>'sky','use'=>'Y','view'=>'Y'],['idx'=>2,'church'=>'sky','use'=>'N','view'=>'Y'],['idx'=>3,'church'=>'beer','use'=>'Y','view'=>'Y']];
foreach(['sky','beer']as $church){
    $_POST=['church'=>$church,'worship'=>'예배','title'=>'2026-10-30','link'=>'eS6_s4GpYtk','password'=>'test'];
    $db=new RegistrationDB($rows);$model=new RegistrationAdmin($db);
    registration_check($model->set_live_list('save')===true,'Registration succeeds');
    foreach($rows as $i=>$original)registration_check($db->rows[$i]===($original['church']===$church?array_merge($original,['use'=>'N','view'=>'N']):$original),'Only matching church disabled');
    $new=end($db->rows);registration_check($new['use']==='Y'&&$new['view']==='N','New selected broadcast requires transmission approval');
    registration_check($db->commits===1&&!$db->transaction&&!$db->lock,'Committed and lock released');
    $db=new RegistrationDB($rows);$db->failInsert=true;$model=new RegistrationAdmin($db);
    registration_check($model->set_live_list('save')===false,'Failed insert rejected');
    registration_check($db->rows===$rows&&$db->rollbacks===1&&!$db->lock,'Insert failure rolls back old flags');
    $db=new RegistrationDB($rows);$db->allowLock=false;$model=new RegistrationAdmin($db);
    registration_check($model->set_live_list('save')===false&&$db->rows===$rows,'Lock timeout leaves state unchanged');
}
function state_model($rows){$db=new RegistrationDB($rows);return [$db,new RegistrationAdmin($db)];}
list($db,$model)=state_model($rows);
registration_check($model->toggle_use(2,'N')===true,'Old broadcast can be selected');
registration_check($db->rows[0]['use']==='N'&&$db->rows[0]['view']==='N'&&$db->rows[1]['use']==='Y'&&$db->rows[1]['view']==='N','Single use, old transmission cleared, new transmission off');
registration_check($db->rows[2]===$rows[2],'Use switch preserves other church');
registration_check($model->toggle_view(2,'N')===true&&$db->rows[1]['view']==='Y','Selected broadcast can transmit');
$before=$db->rows;
registration_check($model->toggle_view(2,'N')===false&&$db->rows===$before,'Stale view request cannot change state');
registration_check($model->toggle_use(2,'N')===false&&$db->rows===$before,'Stale use request cannot change state');
registration_check($model->toggle_view(1,'N')===false,'Inactive broadcast cannot transmit');
registration_check($model->toggle_view(2,'Y')===true&&$db->rows[1]['use']==='Y','Transmission off keeps selection');
registration_check($model->toggle_use(2,'Y')===true&&$db->rows[1]['view']==='N'&&$db->rows[1]['use']==='N','Use off clears transmission');
registration_check($model->toggle_use(999,'N')===false,'Missing broadcast rejected');
registration_check(count(array_unique($db->lockNames))===1&&$db->lockNames[0]==='sky0808net:live-write'&&!$db->lock,'All changes serialize with the same lock');
list($db,$model)=state_model($rows);$db->failUpdate=true;
registration_check($model->toggle_use(2,'N')===false&&$db->rows===$rows&&!$db->lock,'Use switch failure restores every row');
$_POST=['church'=>'beer','worship'=>'수정','title'=>'2026-10-30','link'=>'eS6_s4GpYtk','password'=>'test'];
list($db,$model)=state_model($rows);
registration_check($model->set_live_list('mod',1)===true,'Active church move succeeds');
registration_check($db->rows[0]['church']==='beer'&&$db->rows[0]['use']==='Y'&&$db->rows[0]['view']==='N'&&$db->rows[2]['use']==='N'&&$db->rows[2]['view']==='N','Move keeps only selected destination broadcast and requires approval');
registration_check($db->rows[1]===$rows[1],'Move does not change other source rows');
list($db,$model)=state_model($rows);
registration_check($model->set_live_list('mod',2)===true&&$db->rows[2]===$rows[2]&&$db->rows[1]['view']==='N','Inactive move preserves destination broadcast');
list($db,$model)=state_model($rows);$db->failUpdate=true;
registration_check($model->set_live_list('mod',1)===false&&$db->rows===$rows&&!$db->lock,'Move failure restores source and destination');
$_POST['church']='sky';list($db,$model)=state_model($rows);
registration_check($model->set_live_list('mod',1)===true&&$db->rows[0]['view']==='Y','Same-church metadata edit preserves transmission');
$_POST['church']='invalid';
registration_check($model->set_live_list('save')===false,'Unknown church rejected');
registration_check($model->delete_live_list(1)===true&&count($db->rows)===2&&!$db->lock,'Delete uses common transaction');
echo 'PASS: ',$count,' broadcast registration/use/transmission/church move/rollback checks.',PHP_EOL;
