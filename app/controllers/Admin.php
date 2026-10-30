<?php
class Admin extends Controller {
    protected $admin_model;


    private function denyAdminAccess($status, $message) {
        http_response_code($status);
        header('Cache-Control: no-store');
        $xhr = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if ($xhr || strpos($accept, 'application/json') !== false) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        $target = $status === 401 ? '/admin/login' : '/admin/main';
        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        echo '<!doctype html><html lang="ko"><meta charset="utf-8"><title>관리자 로그인 안내</title><body>';
        if ($status === 403 &&
            preg_match('~^admin/(?:main)?/?$~i', trim((string) ($_GET['url'] ?? ''), '/'))) {
            // main itself is forbidden: keep the denial page instead of a redirect loop.
            echo '<script>alert(' . json_encode($message, $flags) . ');</script>';
        } else {
            echo '<script>alert(' . json_encode($message, $flags) . ');location.replace(' . json_encode($target, $flags) . ');</script>';
        }
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<a href="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '">돌아가기</a></body></html>';
        exit;
    }

    private function renderAdminList($kind) {
        require_once __DIR__ . '/../views/layout/auth.inc';
        require_once __DIR__ . '/../helpers/pagination_helper.php';
        try {
            $data = $this->model('Admin_listing')->page($kind, $_GET);
        } catch (InvalidArgumentException $error) {
            http_response_code(422);
            header('Content-Type: text/html; charset=utf-8');
            echo '<script>alert(' . json_encode($error->getMessage(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');history.back();</script>';
            exit;
        }
        if ($kind === 'live_list') {
            $data['pass_row'] = $this->admin_model->get_last_password();
            $data['yoil'] = ['일','월','화','수','목','금','토'];
        } elseif ($kind === 'video_list') {
            $data['video_list'] = $data['list'];
            $data['preacher_list'] = $this->admin_model->get_preacher_select_list();
            $data['yoil'] = ['일','월','화','수','목','금','토'];
        } elseif ($kind === 'admin_list') {
            $data['admin_list'] = $data['list'];
        } elseif ($kind === 'preacher_list') {
            $data['preacher_list'] = $data['list'];
        }
        $this->view('admin/' . $kind, $data);
    }

    private function authorizeAdmin($action) {
        header('Cache-Control: no-store');
        if (in_array($action, ['login', 'admin_login_proc'], true)) return;
        $name = $_SESSION['admin_user_name'] ?? '';
        $admin = is_string($name) && $name !== ''
            ? $this->admin_model->get_admin_by_name($name) : false;
        if (!$admin) {
            unset($_SESSION['admin_user_name'], $_SESSION['admin_info']);
            $this->denyAdminAccess(401, '관리자 로그인이 필요합니다.');
        }
        // 로그인 당시 캐시 대신 현재 DB 권한으로 메뉴와 요청을 함께 검사한다.
        $_SESSION['admin_info'] = $admin;
        require_once __DIR__ . '/../helpers/Admin_access.php';
        if (!Admin_access::allows($action, $admin)) {
            $this->denyAdminAccess(403, '접근 권한이 없습니다.');
        }
    }

    public function __construct() {
        $this->admin_model = $this->model('Admin_model');
    }
    
    public function notice() {
        $this->authorizeAdmin(__FUNCTION__);
        $this->renderAdminList('notice');
    }

    public function get_notice() {
        $this->authorizeAdmin(__FUNCTION__);
        require_once __DIR__ . '/../views/layout/auth.inc';
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $row = $this->model('Notice_model')->find(input_int('idx', $_GET));
        if (!$row) { http_response_code(404); echo json_encode(['message' => '공지사항을 찾을 수 없습니다.']); exit; }
        $row['contents'] = Notice_support::html((string) $row['contents']);
        $row['attachment_url'] = $row['attached'] ? '/live/notice_attachment?idx=' . (int) $row['idx'] : '';
        echo json_encode($row);
        exit;
    }

    public function set_notice() {
        $this->authorizeAdmin(__FUNCTION__);
        require_once __DIR__ . '/../views/layout/auth.inc';
        require_post();
        require_csrf();
        $idx = empty($_POST['idx']) ? null : input_int('idx', $_POST);
        try {
            $this->model('Notice_model')->save($idx, $_POST, $_FILES['attached'] ?? null);
            header('Location: /admin/notice');
        } catch (Throwable $error) {
            $message = $error instanceof InvalidArgumentException ? $error->getMessage() : '저장하지 못했습니다. 서버 로그를 확인해 주세요.';
            error_log('Notice save failed: ' . $error->getMessage());
            http_response_code($error instanceof InvalidArgumentException ? 422 : 500);
            header('Content-Type: text/html; charset=utf-8');
            echo '<script>alert(' . json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ');history.back();</script>';
        }
        exit;
    }

    public function del_notice() {
        $this->authorizeAdmin(__FUNCTION__);
        require_once __DIR__ . '/../views/layout/auth.inc';
        require_post();
        require_csrf();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $deleted = $this->model('Notice_model')->delete(input_int('idx', $_POST));
            if (!$deleted) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => '공지사항을 찾을 수 없습니다.']);
            } else {
                echo json_encode(['success' => true]);
            }
        } catch (Throwable $error) {
            error_log('Notice delete failed: ' . $error->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => '삭제하지 못했습니다. 다시 시도해 주세요.']);
        }
        exit;
    }

    public function update_enable() {
        $this->authorizeAdmin(__FUNCTION__);
        require_once __DIR__ . '/../views/layout/auth.inc';
        require_post();
        require_csrf();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $row = $this->model('Notice_model')->toggle(input_int('idx', $_POST));
        if (!$row) { http_response_code(404); echo json_encode(['message' => '공지사항을 찾을 수 없습니다.']); exit; }
        echo json_encode(['success' => true, 'enable' => $row['enable']]);
        exit;
    }

    public function index() {
        $this->authorizeAdmin(__FUNCTION__);
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 로그인 된 경우 관리자 페이지 출력
        $this->main();
    }

/** ===================================================================================== */

    /** 로그인 관련 함수 */

    public function login() {
        $this->authorizeAdmin(__FUNCTION__);
        if (isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/main");
            exit;
        }

        // 로그인 폼 출력
        $this->view('admin/login');
    }

    public function admin_login_proc() {
        $this->authorizeAdmin(__FUNCTION__);
        $admin_name = $_POST['admin_name'] ?? '';
        $admin_password = $_POST['admin_password'] ?? '';

        // 관리자 이름 체크
        if (empty($admin_name)) {
            $this->admin_model->system_log('INFO', '[로그인 실패] 관리자 이름 미입력.');
            echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>";
            exit;
        }

        $row = $this->admin_model->get_admin_by_name($admin_name);
        if (!$row || $admin_name !== $row['username']) {
            $this->admin_model->system_log('INFO', "[로그인 실패] 일치하는 관리자의 이름이 없음. 입력된 관리자 이름 : $admin_name");
            echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>";
            exit;
        }

        // 비밀번호 체크
        if (empty($admin_password)) {
            $this->admin_model->system_log('INFO', '[로그인 실패] 관리자 비밀번호 미입력.');
            echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>";
            exit;
        }
        $stored_password = (string) $row['password'];
        $valid_password = password_verify($admin_password, $stored_password)
            || hash_equals($stored_password, hash('sha256', $admin_password));
        if (!$valid_password) {
            $this->admin_model->system_log('INFO', "[로그인 실패] 입력한 비밀번호가 일치하지 않음. 입력된 관리자 이름 : $admin_name");
            echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>";
            exit;
        }

        // 관리자 정보 가져오기
        $admin_info = $this->admin_model->get_admin_by_name($admin_name);

        // 세션 생성
        session_regenerate_id(true);
        $_SESSION['admin_user_name'] = $admin_name;
        $_SESSION['admin_info'] = $admin_info;

        // 생방송 접속 기록 처리
        $this->admin_model->delete_live_entry($admin_name);
        $this->admin_model->add_live_entry(session_id(), $row['church'], $admin_name);

        // 로그인 로그 기록
        $this->admin_model->system_log('INFO', "[로그인] 관리자 : $admin_name.");

        // 로그인 성공 후 이동
        header("Location: /admin/main");
        exit;
    }

    public function admin_logout_proc() {
        $this->authorizeAdmin(__FUNCTION__);
        $admin_user_name = $_SESSION['admin_user_name'] ?? null;

        if ($admin_user_name) {
            // 생방송 접속자 목록에서 제거
            $this->admin_model->delete_live_entry($admin_user_name);

            // 로그아웃 로그 기록
            $this->admin_model->system_log('INFO', "[로그아웃] 관리자 : $admin_user_name.");

            // 세션 삭제
            unset($_SESSION['admin_user_name']);
            unset($_SESSION['admin_info']);
        }

        // 로그아웃 후 이동
        header("Location: /admin/login");
        exit;
    }

    /** 로그인 관련 함수 끝 */

/** ===================================================================================== */
  
    /** 생방송 보기 시작 */

    // 생방송 보기
    public function main() {
        $this->authorizeAdmin(__FUNCTION__);
        // 하늘문 예배
        $sky_worship = $this->admin_model->get_live_worship('sky');
        // 브엘성회 예배
        $beer_worship = $this->admin_model->get_live_worship('beer');

        $this->view('admin/main', [
            'sky_worship' => $sky_worship,
            'beer_worship' => $beer_worship,
        ]);
    }

    // Ajax: 하늘문 접속자
    public function get_sky_live_user() {
        $this->authorizeAdmin(__FUNCTION__);
        $users = $this->admin_model->get_live_users('sky');
        header('Content-Type: application/json');
        echo json_encode($users);
        exit;
    }

    // Ajax: 브엘성회 접속자
    public function get_beer_live_user() {
        $this->authorizeAdmin(__FUNCTION__);
        $users = $this->admin_model->get_live_users('beer');
        header('Content-Type: application/json');
        echo json_encode($users);
        exit;
    }

    /** 생방송 보기 끝 */

/** ===================================================================================== */
    
    /** 생방송 링크 관리 시작 */
    
    public function live_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $this->renderAdminList('live_list');
    }

    // use 상태 토글 (Ajax)
    public function update_use() {
        $this->authorizeAdmin(__FUNCTION__);
        $idx = $_GET['idx'] ?? null;
        if ($idx) {
            $result = $this->admin_model->toggle_use($idx);
            echo json_encode(['result' => $result]);
        }
        exit;
    }

    // view 상태 토글 (Ajax)
    public function update_view() {
        $this->authorizeAdmin(__FUNCTION__);
        $idx = $_GET['idx'] ?? null;
        $view = $_GET['view'] ?? null;
        if ($idx && $view !== null) {
            $result = $this->admin_model->toggle_view($idx, $view);
            echo json_encode(['result' => $result]);
        }
        exit;
    }

    public function live_read() {
        $this->authorizeAdmin(__FUNCTION__);
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }
        // idx가 없으면 생방송 리스트로 이동
        $idx = $_GET['idx'] ?? null;
        if (!$idx) {
            header('Location: /admin/live_list');
            exit;
        }
        $live_link = $this->admin_model->get_live_link_by_idx($idx);
        $this->view('admin/live_read', [
            'live_link' => $live_link,
            'idx' => $idx
        ]);
    }

    public function set_live_list() {
        $this->authorizeAdmin(__FUNCTION__);
        require_once __DIR__ . '/../views/layout/auth.inc';
        require_post();
        require_csrf();
        if (empty($_SESSION['admin_user_name'])) {
            http_response_code(401);
            exit;
        }

        $idx = $_GET['idx'] ?? null;
        $type = $idx ? 'mod' : 'save';

        $result = $this->admin_model->set_live_list($type, $idx);

        if ($result === true) {
            // 등록 성공
            if ($type === 'save') {
                header('Location: /admin/live_list');
            }
            // 수정 성공
            else {
                header('Location: /admin/live_read?idx=' . urlencode($idx));
            }
            exit;
        } else {
            // 실패: 알림 후 이전 페이지로
            echo "<script>alert('저장에 실패했습니다. 관리자에게 문의하세요.'); history.back();</script>";
            exit;
        }
    }

    public function del_live_list() {
        $this->authorizeAdmin(__FUNCTION__);
        require_once __DIR__ . '/../views/layout/auth.inc';
        require_post();
        require_csrf();
        if (empty($_SESSION['admin_user_name'])) {
            http_response_code(401);
            exit;
        }

        $live_idx = input_int('idx', $_POST);

        if ($live_idx) {
            // 생방송 접속자 목록에서 제거
            $this->admin_model->delete_live_list($live_idx);
        }

        // 삭제 후 이동
        header("Location: /admin/live_list");
        exit;
    }

    /** 생방송 링크 관리 끝 */

/** ===================================================================================== */

    /** 블랙리스트 관리 시작 */

    public function black_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $this->renderAdminList('black_list');
    }

    public function set_black_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $nickname = $_POST['nickname'] ?? '';

        if ($nickname) {
            $result = $this->admin_model->set_black_list($nickname);

            if ($result) {
                header('Location: /admin/black_list');
                exit;
            } else {
                echo "<script>alert('저장에 실패했습니다. 관리자에게 문의하세요.'); history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('저장에 실패했습니다. 관리자에게 문의하세요.'); history.back();</script>";
            exit;
        }
    }

    public function del_black_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $black_list_idx = $_GET['idx'] ?? null;

        if ($black_list_idx) {
            // 체크된 수량만큼 블랙리스트 제거
            $this->admin_model->delete_black_list($black_list_idx);
        }

        // 삭제 후 이동
        header("Location: /admin/black_list");
        exit;
    }

    /** 블랙리스트 관리 끝 */

/** ===================================================================================== */

    /** 생방송 접속 이력 시작 */

    public function history() {
        $this->authorizeAdmin(__FUNCTION__);
        $this->renderAdminList('history');
    }
    
    /** 생방송 접속 이력 끝 */

/** ===================================================================================== */

    /** 설교 다시보기 시작 */

    // 다시보기 영상 관리 메인
    public function video_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $this->renderAdminList('video_list');
    }

    public function video_read() {
        $this->authorizeAdmin(__FUNCTION__);
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // idx가 없으면 영상 다시보기 리스트로 이동
        $idx = $_GET['idx'] ?? null;
        if (!$idx) {
            header('Location: /admin/video_list');
            exit;
        }
        
        // 영상 데이터 및 설교자 목록 조회
        $video = $this->admin_model->get_video_link_by_idx($idx);
        $preacher_list = $this->admin_model->get_preacher_select_list();

        // 뷰에 데이터 전달
        $this->view('admin/video_read', [
            'video' => $video,
            'preacher_list' => $preacher_list,
        ]);
    }

    public function set_video_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $idx = $_GET['idx'] ?? null;
        $type = $idx ? 'mod' : 'save';

        $result = $this->admin_model->set_video_list($type, $idx);

        if ($result === true) {
            // 등록 성공
            if ($type === 'save') {
                header('Location: /admin/video_list');
            }
            // 수정 성공
            else {
                header('Location: /admin/video_read?idx=' . urlencode($idx));
            }
            exit;
        } else {
            // 실패: 알림 후 이전 페이지로
            echo "<script>alert('저장에 실패했습니다. 관리자에게 문의하세요.'); history.back();</script>";
            exit;
        }
    }
    
    public function del_video_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $video_idx = $_GET['idx'] ?? null;

        if ($video_idx) {
            // 생방송 접속자 목록에서 제거
            $this->admin_model->delete_video_list($video_idx);
        }

        // 삭제 후 이동
        header("Location: /admin/video_list");
        exit;
    }

    // 현재 미구현 상태
    // public function event_list() {
    //     // 세션이 없으면 로그인 페이지로 이동
    //     if (!isset($_SESSION['admin_user_name'])) {
    //         header("Location: /admin/login");
    //         exit;
    //     }
    //     // 로그인 된 경우 관리자 페이지 출력
    //     $this->view('admin/live_read');
    // }


    /** 설교 다시보기 끝 */

/** ===================================================================================== */

    /** 관리자 관리 시작 */
    
    public function admin_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $this->renderAdminList('admin_list');
    }

    public function admin_read() {
        $this->authorizeAdmin(__FUNCTION__);
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 로그인 된 경우 관리자 관리 페이지 출력
        $idx = $_GET['idx'] ?? null;

        if (!$idx || !is_numeric($idx)) {
            header("Location: /admin/admin_list");
            exit;
        }

        // 모델에서 관리자 정보 가져오기
        $admin = $this->admin_model->get_admin_by_idx($idx);
        if (!$admin) {
            echo "<script>alert('해당 관리자 정보가 없습니다.'); history.back();</script>";
            exit;
        }

        $this->view('admin/admin_read', ['admin' => $admin]);
    }

    public function set_admin() {
        $this->authorizeAdmin(__FUNCTION__);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idx = $_GET['idx'] ?? null; // 있을 경우 수정

            $result = $this->admin_model->set_admin($_POST, $idx);

            if ($result['success']) {
                $message = $idx ? '관리자 정보가 수정되었습니다.' : '관리자가 추가되었습니다.';
                echo "<script>alert('{$message}'); location.href = '/admin/admin_list';</script>";
            } else {
                echo "<script>alert('{$result['message']}'); history.back();</script>";
            }
            exit;
        }

        // GET으로 접근한 경우
        header("Location: /admin/admin_list");
        exit;
    }

    // 관리자 삭제
    public function del_admin() {
        $this->authorizeAdmin(__FUNCTION__);
        $admin_idx = $_GET['idx'] ?? null;

        if ($admin_idx) {
            // 체크된 수량만큼 관리자 삭제
            $this->admin_model->delete_admin($admin_idx);
        }

        // 삭제 후 이동
        header("Location: /admin/admin_list");
        exit;
    }

    /** 관리자 관리 끝 */

/** ===================================================================================== */

    /** 설교자 관리 시작 */

    public function preacher_list() {
        $this->authorizeAdmin(__FUNCTION__);
        $this->renderAdminList('preacher_list');
    }

    public function get_preacher() {
        $this->authorizeAdmin(__FUNCTION__);
        $idx = $_POST['idx'] ?? null;
        if (!$idx) {
            echo json_encode(['error' => 'No index provided']);
            exit;
        }
        $preacher = $this->admin_model->get_preacher_by_idx($idx);
        echo json_encode($preacher ?: []);
        exit;
    }

    public function set_preacher() {
        $this->authorizeAdmin(__FUNCTION__);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idx = $_POST['mod_idx'] ?? null;
            $name = trim($_POST['name'] ?? $_POST['mod_name'] ?? '');

            if ($name === '') {
                echo "<script>alert('설교자 이름을 입력해주세요.'); history.back();</script>";
                exit;
            }

            $result = $this->admin_model->set_preacher($name, $idx);

            if (!$result['success']) {
                $this->admin_model->system_log('INFO', '[설교자 추가 실패] 설교자 : '.$name.' / message : '.$result['message']);
            }
        }

        header('Location: /admin/preacher_list');
        exit;
    }

    // 설교자 삭제
    public function del_preacher() {
        $this->authorizeAdmin(__FUNCTION__);
        $preacher_idx = $_GET['idx'] ?? null;

        if ($preacher_idx) {
            // 체크된 수량만큼 설교자 삭제
            $this->admin_model->delete_preacher($preacher_idx);
        }

        // 삭제 후 이동
        header("Location: /admin/preacher_list");
        exit;
    }


    /** 설교자 관리 끝 */

/** ===================================================================================== */

    // public function saint_list() {
    //     // 세션이 없으면 로그인 페이지로 이동
    //     if (!isset($_SESSION['admin_user_name'])) {
    //         header("Location: /admin/login");
    //         exit;
    //     }

    //     // 로그인 된 경우 관리자 페이지 출력
        
    // }
}
?>
