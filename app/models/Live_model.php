<?php
class Live_model extends Model {

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

    // 현재 송출 중 여부 확인 (church 기준으로 최근 use='Y' && view='Y' 1건)
    public function get_live_status($church) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM live_list WHERE church = :church AND `view` = 'Y'");
        $stmt->execute(['church' => $church]);
        return (int)$stmt->fetchColumn();
    }

    // 실시간 접속 기록 저장
    public function save_live_entry($church, $user_name) {
        $sql = "INSERT INTO live_entry (`session`, `church`, `name`, `branch`, `date_insert`) 
                VALUES (:session, :church, :name, 'MEMBER', NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'session' => session_id(),
            'church' => $church,
            'name' => $user_name
        ]);
    }

    // 현재 송출중인 방송 정보 조회
    public function get_active_live($church) {
        $stmt = $this->db->prepare(
            "SELECT `use`, `view`, `link` FROM live_list WHERE church = :church AND `view` = 'Y' ORDER BY idx DESC LIMIT 1"
        );
        $stmt->execute(['church' => $church]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 특정 링크로 방송 정보 조회
    public function get_live_info_by_link($link) {
        $stmt = $this->db->prepare("SELECT * FROM live_list WHERE link = :link ORDER BY idx DESC LIMIT 1");
        $stmt->execute(['link' => $link]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // name + branch 기준으로 세션 정보 갱신
    public function update_live_session($name, $branch, $session_id) {
        $stmt = $this->db->prepare(
            "UPDATE live_entry 
            SET session = :session, date_update = NOW() 
            WHERE name = :name AND branch = :branch"
        );
        $stmt->execute([
            'session' => $session_id,
            'name'    => $name,
            'branch'  => $branch
        ]);
    }

}
?>
