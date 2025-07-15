<?php
class User extends Controller {

    public function index() {
        // 세션이 없으면 로그인 페이지로 이동
        if (!isset($_SESSION['user_name'])) {
            header("Location: /user/login");
            exit;
        }
        // 로그인 된 경우 예배 선택 페이지 출력
        $this->view('live/choice');
    }

    public function login() {
        if (isset($_SESSION['user_name'])) {
            header("Location: /live/choice");
            exit;
        }
        // 로그인 폼 출력
        $this->view('user/login');
    }

    public function login_proc() {
        $user_name = $_POST['user_name'] ?? '';
        $password = $_POST['live_password'] ?? '';

        // 이름 글자수 체크 (2~4자, 한글만)
        if (!preg_match('/^[가-힣]{2,4}$/u', $user_name)) {
            echo "<script>alert('이름 규칙에 맞지 않습니다.'); history.back();</script>";
            exit;
        }

        $user_model = $this->model('User_model');

        // 블랙리스트 체크
        if ($user_model->isBlacklisted($user_name)) {
            echo "<script>alert('실명만 입력 가능합니다.'); history.back();</script>";
            exit;
        }

        // 비밀번호 체크
        $livePasswd = $user_model->getLivePassword();
        if ($password !== $livePasswd) {
            echo "<script>alert('비밀번호가 맞지 않습니다.'); history.back();</script>";
            exit;
        }

        // 세션 생성
        $_SESSION['user_name'] = $user_name;

        // 로그인 후 이동
        header("Location: /live/choice");
        exit;
    }

    public function logout_proc() {
        // 세션 정보 확보
        $user_name = $_SESSION['user_name'] ?? '';

        // 모델 로드
        $user_model = $this->model('User_model');

        // 로그아웃 기록 및 방송 나가기 처리
        if ($user_name) {
            $user_model->updateLogoutHistory($user_name);
            $user_model->delete_live_entry($user_name);
        }

        // 세션 파괴
        session_destroy();

        // 로그아웃 후 이동
        header("Location: /user/login");
        exit;
    }
}
?>
