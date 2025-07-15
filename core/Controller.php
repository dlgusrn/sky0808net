<?php
class Controller {
    // 모델 로딩
    protected function model($model) {
        require_once "../app/models/$model.php";
        return new $model();
    }

    // 뷰 로딩
    protected function view($view, $data = []) {
        extract($data);
        require_once "../app/views/$view.php";
    }
}
?>
