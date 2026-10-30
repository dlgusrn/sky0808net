<?php
// Invoked separately for each case because controller guards deliberately exit.
require_once __DIR__.'/../core/Model.php';
require_once __DIR__.'/../core/Controller.php';
require_once __DIR__.'/../app/controllers/Admin.php';
$action=$argv[1];$method=$argv[2];$tokenMode=$argv[3];
$writes=0;
class RequestModel {
    public function get_admin_by_name($name){return ['username'=>'테스트관리자','password'=>hash('sha256','test-pass'),'church'=>'sky','level'=>1,'live_manage'=>'Y','live_list'=>'Y','history'=>'Y','black_list'=>'Y','video_list'=>'Y','notice'=>'Y'];}
    public function __call($name,$args){
        global $writes;$writes++;
        if(in_array($name,['set_admin','set_preacher'],true))return ['success'=>true];
        if($name==='toggle')return ['enable'=>'Y'];
        return true;
    }
}
class RequestAdmin extends Admin {
    public function __construct(){$this->admin_model=new RequestModel();}
    protected function model($name){return new RequestModel();}
}
session_start();
$_SESSION=['admin_user_name'=>'테스트관리자','csrf_token'=>'test-token'];
$_SERVER['REQUEST_METHOD']=$method;
$_SERVER['REQUEST_URI']='/admin/'.$action;
$_POST=['idx'=>1,'mod_idx'=>1,'name'=>'설교자','nickname'=>'차단','admin_name'=>'테스트관리자','admin_password'=>'test-pass','use'=>'N','view'=>'N'];
$_GET=['idx'=>1];
$_FILES=[];
if($tokenMode==='valid')$_POST['csrf']='test-token';
if($tokenMode==='bad')$_POST['csrf']='invalid';
if($tokenMode==='array')$_POST['csrf']=['test-token'];
$expected=$method!=='POST'?405:($tokenMode==='valid'?200:403);
$redirectActions=['admin_login_proc','admin_logout_proc','set_live_list','del_live_list',
    'set_black_list','del_black_list','set_video_list','del_video_list','del_admin',
    'set_preacher','del_preacher','set_notice'];
// Native PHP reports Location headers as 302; the local WASM CLI can retain 200.
$allowedStatuses=$expected===200&&in_array($action,$redirectActions,true)?[200,302]:[$expected];
register_shutdown_function(function()use($expected,$allowedStatuses,$action,$method,$tokenMode){
    global $writes;
    $actual=http_response_code()?:200;
    if(!in_array($actual,$allowedStatuses,true)||($expected!==200&&$writes!==0)||($expected===200&&$writes===0)){
        fwrite(STDERR,"FAIL: $action $method $tokenMode HTTP=$actual writes=$writes expected=".implode('/',$allowedStatuses)."\n");exit(1);
    }
});
ob_start();
$controller=new RequestAdmin();
$controller->$action();
