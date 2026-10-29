<?php
require_once __DIR__.'/../core/Model.php';
require_once __DIR__.'/../app/models/Admin_model.php';
require_once __DIR__.'/../app/models/Live_model.php';
class BroadcastProbe extends Model {public function connection(){return $this->db;}}
$db=(new BroadcastProbe())->connection();$admin=new Admin_model();$live=new Live_model();
foreach(['sky','beer']as $church){
    $statement=$db->prepare("SELECT idx, link FROM live_list WHERE church=:church AND `use`='Y' AND `view`='Y' ORDER BY idx DESC");
    $statement->execute(['church'=>$church]);$expected=$statement->fetchAll(PDO::FETCH_ASSOC);
    $actual=$admin->get_live_worship($church);$viewer=$live->get_active_live($church);
    if(($actual['idx']??null)!=($expected[0]['idx']??null)||($viewer['link']??null)!==($expected[0]['link']??null)||$live->get_live_status($church)!==count($expected))throw new RuntimeException('Production broadcast selection differs from use=Y/view=Y policy');
}
echo 'PASS: broadcast display and status match active database rows (read-only).',PHP_EOL;
