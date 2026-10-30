<?php
require_once __DIR__.'/../core/Model.php';
require_once __DIR__.'/../app/models/Admin_model.php';
class RegistrationStatement {
    private $db; private $sql;
    public function __construct($db,$sql){$this->db=$db;$this->sql=$sql;}
    public function execute($params){
        if(strpos($this->sql,'GET_LOCK')!==false){$this->db->lock=$this->db->allowLock;return true;}
        if(strpos($this->sql,'RELEASE_LOCK')!==false){$this->db->lock=false;return true;}
        if(strpos($this->sql,'INSERT INTO system_log')!==false)return true;
        if(strpos($this->sql,"UPDATE live_list SET `use` = 'N'")!==false){
            foreach($this->db->rows as &$row)if($row['church']===$params['church'])$row['use']='N';unset($row);
        }elseif(strpos($this->sql,'INSERT INTO live_list')!==false){
            if($this->db->failInsert)throw new RuntimeException('Simulated insert failure');
            if(strpos($this->sql,"'Y', 'N'")===false)throw new RuntimeException('New flags must be explicit');
            $this->db->rows[]=$params+['idx'=>100,'use'=>'Y','view'=>'N'];
        }elseif(strpos($this->sql,'UPDATE live_list')!==false){
            foreach($this->db->rows as &$row)if($row['idx']===$params['idx'])$row=array_merge($row,$params);unset($row);
        }
        return true;
    }
    public function fetchColumn(){return $this->db->allowLock?1:0;}
}
class RegistrationDB {
    public $rows,$snapshot,$lock=false,$allowLock=true,$failInsert=false,$transaction=false,$commits=0,$rollbacks=0;
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
$rows=[['idx'=>1,'church'=>'sky','use'=>'Y','view'=>'Y'],['idx'=>2,'church'=>'sky','use'=>'Y','view'=>'N'],['idx'=>3,'church'=>'beer','use'=>'Y','view'=>'Y']];
foreach(['sky','beer']as $church){
    $_POST=['church'=>$church,'worship'=>'예배','title'=>'2026-10-30','link'=>'eS6_s4GpYtk','password'=>'test'];
    $db=new RegistrationDB($rows);$model=new RegistrationAdmin($db);
    registration_check($model->set_live_list('save')===true,'Registration succeeds');
    foreach($rows as $i=>$original)registration_check($db->rows[$i]['use']===($original['church']===$church?'N':$original['use']),'Only matching church disabled');
    $new=end($db->rows);registration_check($new['use']==='Y'&&$new['view']==='N','Only new broadcast enabled, transmission off');
    registration_check($db->commits===1&&!$db->transaction&&!$db->lock,'Committed and lock released');
    $db=new RegistrationDB($rows);$db->failInsert=true;$model=new RegistrationAdmin($db);
    registration_check($model->set_live_list('save')===false,'Failed insert rejected');
    registration_check($db->rows===$rows&&$db->rollbacks===1&&!$db->lock,'Failed registration restores previous state');
    $db=new RegistrationDB($rows);$db->allowLock=false;$model=new RegistrationAdmin($db);
    registration_check($model->set_live_list('save')===false&&$db->rows===$rows,'Lock timeout leaves broadcasts unchanged');
}
$_POST=['church'=>'sky','worship'=>'수정','title'=>'2026-10-30','link'=>'eS6_s4GpYtk','password'=>'test'];
$db=new RegistrationDB($rows);$model=new RegistrationAdmin($db);
registration_check($model->set_live_list('mod',1)===true&&$db->rows[1]===$rows[1]&&$db->commits===0,'Edit does not reset other broadcasts');
$_POST['church']='invalid';
registration_check($model->set_live_list('save')===false&&count($db->rows)===3,'Unknown church rejected');
echo 'PASS: ',$count,' broadcast registration isolation, flags, rollback and lock checks.',PHP_EOL;
