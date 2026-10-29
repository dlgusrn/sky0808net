<?php
class Live extends Controller {
    protected $live_model;

    public function __construct() {
        $this->live_model = $this->model('Live_model');
    }
    
    public function notice_attachment() {
        if (empty($_SESSION['user_name']) && empty($_SESSION['admin_user_name'])) {
            http_response_code(401);
            exit;
        }
        require_once __DIR__ . '/../views/layout/auth.inc';
        require_once __DIR__ . '/../helpers/Notice_support.php';
        if (isset($_GET['file'])) {
            $filename = $_GET['file'];
        } else {
            $row = $this->model('Notice_model')->find(input_int('idx', $_GET));
            $filename = $row['attached'] ?? '';
        }
        if (!Notice_support::filename($filename)) { http_response_code(404); exit; }
        $path = Notice_support::directory() . '/' . $filename;
        if (!is_file($path)) { http_response_code(404); exit; }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/gif'], true)) { http_response_code(404); exit; }
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: private, no-store');
        session_write_close();
        readfile($path);
        exit;
    }

    public function index() {
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['user_name'])) {
            header("Location: /user/login");
            exit;
        }

        // 로그인된 경우에만 정상 페이지 출력
        header('Location: /live/choice');
        exit;
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
            'notice' => $this->model('Notice_model')->active(),
            'sky_worship' => $sky_worship,
            'beer_worship' => $beer_worship
        ]);
    }

    // 예배 접속 기록 저장 (Ajax용)
    public function access() {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo json_encode(['success' => false]);
            exit;
        }
        $user_name = $_SESSION['user_name'] ?? '';
        if ($user_name === '') {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }
        $church = $_POST['church'] ?? '';
        if (!in_array($church, ['sky', 'beer'], true)) {
            http_response_code(422);
            echo json_encode(['success' => false]);
            exit;
        }
        try {
            $_SESSION['live_history_idx'] = $this->live_model->save_live_entry(
                $church, $user_name, $_SESSION['live_history_idx'] ?? null
            );
            echo json_encode(['success' => true]);
        } catch (Throwable $error) {
            error_log('Live access failed: ' . $error->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false]);
        }
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
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $user_name = $_SESSION['user_name'] ?? '';
        if ($user_name === '') {
            http_response_code(401);
            echo json_encode(['error' => 'Login required']);
            exit;
        }
        $church = $_GET['church'] ?? '';
        if (!in_array($church, ['sky', 'beer'], true)) {
            http_response_code(422);
            echo json_encode(['error' => 'Invalid church']);
            exit;
        }
        // 방송 링크가 없어도 접속 상태를 갱신한다.
        $this->live_model->refresh_live_entry($church, $user_name);
        $data = $this->live_model->get_active_live($church);
        echo json_encode($data ?: ['use' => '', 'view' => '', 'link' => '']);
        exit;
    }

}
?>
