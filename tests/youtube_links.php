<?php
require_once __DIR__.'/../core/Model.php';
require_once __DIR__.'/../app/models/Admin_model.php';
$id='eS6_s4GpYtk';$count=0;
foreach([$id,'https://youtube.com/live/'.$id.'?feature=share','https://www.youtube.com/live/'.$id.'?si=test','https://youtu.be/'.$id.'?si=test&t=3','https://youtube.com/watch?v='.$id,'https://www.youtube.com/watch?feature=share&v='.$id.'&t=10','https://m.youtube.com/watch?v='.$id,'https://youtube.com/shorts/'.$id,'https://www.youtube.com/embed/'.$id,'https://www.youtube-nocookie.com/embed/'.$id,'youtube.com/live/'.$id,'  https://youtube.com/live/'.$id.'?feature=share  ','https://youtu.be/https://youtube.com/live/'.$id.'?feature=share']as $url){
 if(Youtube_link::video_id($url)!==$id)throw new RuntimeException('Valid YouTube share format rejected');$count++;
}
foreach(['','invalid-video-id','https://evil.example/live/'.$id,'https://youtube.com.evil.example/live/'.$id,'https://youtube.com@evil.example/live/'.$id,'https://evil.example/?url=https://youtube.com/live/'.$id,'https://youtu.be/'.$id.'/extra','https://youtube.com/watch?v[]=foo','javascript:alert(1)','https://youtube.com/channel/'.$id,[],null]as $url){
 if(Youtube_link::video_id($url)!==null)throw new RuntimeException('Invalid link accepted');$count++;
}
class YoutubeStatement {private $db;public function __construct($db){$this->db=$db;}public function execute($params){if(isset($params['link']))$this->db->params=$params;return true;}public function fetchColumn(){return 1;}}
class YoutubeDB {public $params=[];public $calls=0;public function beginTransaction(){return true;}public function commit(){return true;}public function inTransaction(){return false;}public function rollBack(){return true;}public function prepare($sql){$this->calls++;return new YoutubeStatement($this);}}
class YoutubeAdmin extends Admin_model {public function __construct($db){$this->db=$db;}}
$db=new YoutubeDB();$model=new YoutubeAdmin($db);
foreach(['set_live_list','set_video_list']as $method){
 foreach([['save',null],['mod',1]]as $operation){
  $_POST=['church'=>'sky','worship'=>'낮예배','title'=>'2026-10-30','preacher'=>'시험','password'=>'test','link'=>'https://youtube.com/live/'.$id.'?feature=share'];
  if(!$model->$method($operation[0],$operation[1])||$db->params['link']!==$id)throw new RuntimeException('Save/update must bind only the extracted ID');$count++;
 }
 $_POST['link']='https://evil.example/live/'.$id;$before=$db->calls;
 if($model->$method('save')!==false||$db->calls!==$before)throw new RuntimeException('Invalid links must not reach SQL');$count++;
}
echo 'PASS: ',$count,' YouTube share formats, validation and live/video save/update checks.',PHP_EOL;
