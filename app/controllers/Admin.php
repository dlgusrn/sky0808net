<?php
class Admin extends Controller {
    protected $admin_model;

    public function __construct() {
        $this->admin_model = $this->model('Admin_model');
    }
    
    public function index() {
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 로그인 된 경우 관리자 페이지 출력
        $this->view('admin/main');
    }

/** ===================================================================================== */

    /** 로그인 관련 함수 */

    public function login() {
        if (isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/main");
            exit;
        }

        // 로그인 폼 출력
        $this->view('admin/login');
    }

    public function admin_login_proc() {
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
        $password = hash("sha256", $admin_password);
        if ($password !== $row['password']) {
            $this->admin_model->system_log('INFO', "[로그인 실패] 입력한 비밀번호가 일치하지 않음. 입력된 관리자 이름 : $admin_name");
            echo "<script>alert('일치하는 정보가 없습니다.'); history.back();</script>";
            exit;
        }

        // 관리자 정보 가져오기
        $admin_info = $this->admin_model->get_admin_by_name($admin_name);

        // 세션 생성
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
        $admin_user_name = $_SESSION['admin_user_name'] ?? null;

        if ($admin_user_name) {
            // 생방송 접속자 목록에서 제거
            $this->admin_model->delete_live_entry($admin_user_name);

            // 로그아웃 로그 기록
            $this->admin_model->system_log('INFO', "[로그아웃] 관리자 : $admin_name.");

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
        $users = $this->admin_model->get_live_users('sky');
        header('Content-Type: application/json');
        echo json_encode($users);
        exit;
    }

    // Ajax: 브엘성회 접속자
    public function get_beer_live_user() {
        $users = $this->admin_model->get_live_users('beer');
        header('Content-Type: application/json');
        echo json_encode($users);
        exit;
    }

    /** 생방송 보기 끝 */

/** ===================================================================================== */
    
    /** 생방송 링크 관리 시작 */
    
    public function live_list() {
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 로그인 된 경우 생방송 관리 페이지 출력
        // 페이지에 필요한 데이터 호출
        $church_select = $_GET['church'] ?? '';
        $page_set = 10;
        $block_set = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $total = $this->admin_model->get_live_total($church_select);
        $total_page = ceil($total / $page_set);
        $block = ceil($page / $block_set);
        $block_start = (($block - 1) * $block_set) + 1;
        $block_end = $block_start + $block_set - 1;
        if ($block_end > $total_page) $block_end = $total_page;
        $start_num = ($page - 1) * $page_set;

        $list = $this->admin_model->get_live_list($church_select, $start_num, $page_set);
        $pass_row = $this->admin_model->get_last_password();

        $yoil = array('일', '월', '화', '수', '목', '금', '토');

        $this->view('admin/live_list', [
            'list' => $list,
            'church_select' => $church_select,
            'page_set' => $page_set,
            'block_set' => $block_set,
            'page' => $page,
            'block' => $block,
            'block_start' => $block_start,
            'block_end' => $block_end,
            'total' => $total,
            'total_page' => $total_page,
            'start_num' => $start_num,
            'pass_row' => $pass_row,
            'yoil' => $yoil,
        ]);
    }

    // use 상태 토글 (Ajax)
    public function update_use() {
        $idx = $_GET['idx'] ?? null;
        if ($idx) {
            $result = $this->admin_model->toggle_use($idx);
            echo json_encode(['result' => $result]);
        }
        exit;
    }

    // view 상태 토글 (Ajax)
    public function update_view() {
        $idx = $_GET['idx'] ?? null;
        $view = $_GET['view'] ?? null;
        if ($idx && $view !== null) {
            $result = $this->admin_model->toggle_view($idx, $view);
            echo json_encode(['result' => $result]);
        }
        exit;
    }

    public function live_read() {
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
        $live_idx = $_GET['idx'] ?? null;

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
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 로그인 된 경우 관리자 페이지 출력
        $page_set = 10;
        $block_set = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $total = $this->admin_model->get_black_list_total();
        $total_page = ceil($total / $page_set);
        $block = ceil($page / $block_set);
        $block_start = (($block - 1) * $block_set) + 1;
        $block_end = $block_start + $block_set - 1;
        if ($block_end > $total_page) $block_end = $total_page;
        $start_num = ($page - 1) * $page_set;

        $list = $this->admin_model->get_black_list($start_num, $page_set);

        $this->view('admin/black_list', [
            'list' => $list,
            'total' => $total,
            'page_set' => $page_set,
            'block_set' => $block_set,
            'page' => $page,
            'block' => $block,
            'block_start' => $block_start,
            'block_end' => $block_end,
            'total_page' => $total_page,
        ]);
    }

    public function set_black_list() {
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
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 로그인 된 경우 관리자 페이지 출력
        $page_set = 10;
        $block_set = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $total = $this->admin_model->get_history_total();
        $total_page = ceil($total / $page_set);
        $block = ceil($page / $block_set);
        $block_start = (($block - 1) * $block_set) + 1;
        $block_end = $block_start + $block_set - 1;
        if ($block_end > $total_page) $block_end = $total_page;
        $start_num = ($page - 1) * $page_set;

        $list = $this->admin_model->get_history_list($start_num, $page_set);

        $this->view('admin/history', [
            'list' => $list,
            'total' => $total,
            'page_set' => $page_set,
            'block_set' => $block_set,
            'page' => $page,
            'block' => $block,
            'block_start' => $block_start,
            'block_end' => $block_end,
            'total_page' => $total_page,
        ]);
    }
    
    /** 생방송 접속 이력 끝 */

/** ===================================================================================== */

    /** 설교 다시보기 시작 */

    // 다시보기 영상 관리 메인
    public function video_list() {
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 설교자 or 교회 선택
        $church_select = $_GET['church'] ?? '';
        $preacher_select = $_GET['preacher'] ?? '';

        // 설교자 목록
        $preacher_list = $this->admin_model->get_preacher_select_list($preacher_select);

        // 게시물/페이징
        $page_set = 10;
        $block_set = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $total = $this->admin_model->get_video_count($church_select, $preacher_select);
        $total_page = ceil($total / $page_set);
        $block = ceil($page / $block_set);
        $block_start = (($block - 1) * $block_set) + 1;
        $block_end = min($block_start + $block_set - 1, $total_page);
        $start_num = ($page - 1) * $page_set;

        // 영상 목록
        $video_list = $this->admin_model->get_video_list($church_select, $preacher_select, $start_num, $page_set);

        // 요일 배열
        $yoil = ['일', '월', '화', '수', '목', '금', '토'];

        $this->view('admin/video_list', [
            'preacher_list' => $preacher_list,
            'preacher_select' => $preacher_select,
            'church_select' => $church_select,
            'video_list' => $video_list,
            'page_set' => $page_set,
            'block_set' => $block_set,
            'page' => $page,
            'block' => $block,
            'block_start' => $block_start,
            'block_end' => $block_end,
            'total' => $total,
            'total_page' => $total_page,
            'start_num' => $start_num,
            'yoil' => $yoil,
        ]);
    }

    public function video_read() {
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
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 로그인 된 경우 관리자 관리 페이지 출력
        $page_set = 10;
        $block_set = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $total = $this->admin_model->get_admin_count();
        $total_page = ceil($total / $page_set);
        $block = ceil($page / $block_set);
        $block_start = (($block - 1) * $block_set) + 1;
        $block_end = min($block_start + $block_set - 1, $total_page);
        $start_num = ($page - 1) * $page_set;

        $admin_list = $this->admin_model->get_admin_list($start_num, $page_set);

        $this->view('admin/admin_list', [
            'admin_list' => $admin_list,
            'page_set' => $page_set,
            'block_set' => $block_set,
            'page' => $page,
            'block' => $block,
            'block_start' => $block_start,
            'block_end' => $block_end,
            'total' => $total,
            'total_page' => $total_page,
        ]);
    }

    public function admin_read() {
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
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['admin_user_name'])) {
            header("Location: /admin/login");
            exit;
        }

        // 로그인 된 경우 관리자 페이지 출력
        $page_set = 10;
        $block_set = 5;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        $total = $this->admin_model->get_preacher_count();
        $total_page = ceil($total / $page_set);
        $block = ceil($page / $block_set);
        $block_start = (($block - 1) * $block_set) + 1;
        $block_end = min($block_start + $block_set - 1, $total_page);
        $start_num = ($page - 1) * $page_set;

        $preacher_list = $this->admin_model->get_preacher_list($start_num, $page_set);

        $this->view('admin/preacher_list', [
            'preacher_list' => $preacher_list,
            'page_set' => $page_set,
            'block_set' => $block_set,
            'page' => $page,
            'block' => $block,
            'block_start' => $block_start,
            'block_end' => $block_end,
            'total' => $total,
            'total_page' => $total_page,
        ]);
    }

    public function get_preacher() {
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
