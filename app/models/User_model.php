<?php
class User_model extends Model {

    // 생방송 비밀번호 가져오기
    public function getLivePassword() {
        $stmt = $this->db->query("SELECT passwd FROM live_list WHERE `use` = 'Y' ORDER BY idx DESC");
        $row = $stmt->fetch();

        return !empty($row['passwd']) ? $row['passwd'] : '019108';
    }
    
    // 블랙리스트 체크
    public function isBlacklisted($user_name) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM black_list WHERE `nickname` = :user_name");
        $stmt->execute(['user_name' => $user_name]);
        return $stmt->fetchColumn() > 0;
    }

    // 로그아웃 기록 업데이트
    public function updateLogoutHistory($user_name) {
        $stmt = $this->db->prepare("SELECT idx FROM live_history WHERE `name` = :name AND logout_date IS NULL ORDER BY idx DESC LIMIT 1");
        $stmt->execute(['name' => $user_name]);
        $row = $stmt->fetch();
        if ($row && isset($row['idx'])) {
            $update = $this->db->prepare("UPDATE live_history SET logout_date = NOW() WHERE `idx` = :idx");
            $update->execute(['idx' => $row['idx']]);
        }
    }

    // 실시간 방송 나가기
    public function delete_live_entry($user_name) {
        $stmt = $this->db->prepare("DELETE FROM live_entry WHERE `name` = :name");
        $stmt->execute(['name' => $user_name]);
    }
}
?>
