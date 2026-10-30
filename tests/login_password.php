<?php
require_once __DIR__.'/../core/Model.php';
require_once __DIR__.'/../app/models/User_model.php';
require_once __DIR__.'/../app/models/Live_model.php';
class LoginPasswordStatement {
    private $row;
    public function __construct($row){$this->row=$row;}
    public function fetch(){return $this->row;}
}
class LoginPasswordDB {
    private $row;
    public function __construct($row){$this->row=$row;}
    public function query($sql){return new LoginPasswordStatement($this->row);}
}
class PasswordUser extends User_model {public function __construct($db){$this->db=$db;}}
class PasswordLive extends Live_model {public function __construct($db){$this->db=$db;}}
$count=0;
foreach(['PasswordUser','PasswordLive'] as $modelClass){
    foreach([false,['passwd'=>''],['passwd'=>null]] as $row){
        $model=new $modelClass(new LoginPasswordDB($row));
        if(hash('sha256',$model->getLivePassword())!=='fc2e517920c7c0bed3c9f8b2324d60745a5c4bcdaa014349566c40e77a74553e')throw new RuntimeException('Existing default password behavior changed');
        $count++;
    }
    $model=new $modelClass(new LoginPasswordDB(['passwd'=>'test-active-password']));
    if($model->getLivePassword()!=='test-active-password')throw new RuntimeException('Active password no longer takes priority');
    $count++;
}
echo 'PASS: ',$count,' existing default/active login password behavior checks.',PHP_EOL;
