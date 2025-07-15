<?php
class Live extends Controller {
    protected $live_model;

    public function __construct() {
        $this->live_model = $this->model('Live_model');
    }
    
    public function index() {
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['user_name'])) {
            header("Location: /user/login");
            exit;
        }

        // 로그인된 경우에만 정상 페이지 출력
        $this->view('live/choice');
    }

    // 실시간 방송 선택 페이지
    public function choice() {
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['user_name'])) {
            header("Location: /user/login");
            exit;
        }

        $sky_worship = $this->live_model->get_live_status('sky');
        $beer_worship = $this->live_model->get_live_status('beer');

        $this->view('live/choice', [
            'sky_worship' => $sky_worship,
            'beer_worship' => $beer_worship
        ]);
    }

    // 예배 접속 기록 저장 (Ajax용)
    public function access() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['church'])) {
            $church = $_POST['church'];
            $user_name = $_SESSION['user_name'] ?? '비회원';

            $this->live_model->save_live_entry($church, $user_name);

            echo json_encode(['success' => true]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => '잘못된 요청']);
        exit;
    }

    // 생방송 보기 메인
    public function worship_view() {
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['user_name'])) {
            header("Location: /user/login");
            exit;
        }

        $church = $_GET['church'] ?? 'sky';
        $worship = $this->live_model->get_active_live($church);

        $this->view('live/worship_view', [
            'church' => $church,
            'worship' => $worship
        ]);
    }

    // AJAX: 실시간 상태 확인
    public function check_live_list() {
        $link = $_GET['link'] ?? null;

        if (!$link) {
            echo json_encode(['error' => 'Missing link']);
            exit;
        }

        // 방송 정보 가져오기
        $data = $this->live_model->get_live_info_by_link($link);

        // 접속자 세션 정보 갱신
        $user_name = $_SESSION['user_name'] ?? null;
        $branch = $_SESSION['branch'] ?? 'MEMBER';

        if ($user_name) {
            $this->live_model->update_live_session($user_name, $branch, session_id());
        }

        echo json_encode($data ?: []);
        exit;
    }

}
?>
