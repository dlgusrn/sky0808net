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
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM live_list WHERE church = :church AND `use` = 'Y' AND `view` = 'Y'");
        $stmt->execute(['church' => $church]);
        return (int)$stmt->fetchColumn();
    }

    // 실시간 접속 기록 저장
    public function save_live_entry($church, $user_name, $previous_history_idx = null) {
        $this->db->beginTransaction();
        try {
            // 재입장 시 현재 세션의 이전 이력만 종료한다.
            if ($previous_history_idx) {
                $stmt = $this->db->prepare("UPDATE live_history SET logout_date = NOW() WHERE idx = :idx AND name = :name AND branch = 'MEMBER' AND logout_date IS NULL");
                $stmt->execute(['idx' => $previous_history_idx, 'name' => $user_name]);
            }
            $stmt = $this->db->prepare("INSERT INTO live_history (branch, name, login_date) VALUES ('MEMBER', :name, NOW())");
            $stmt->execute(['name' => $user_name]);
            $history_idx = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare("DELETE FROM live_entry WHERE session = :session AND name = :name AND branch = 'MEMBER'");
            $stmt->execute(['session' => session_id(), 'name' => $user_name]);
            $stmt = $this->db->prepare("INSERT INTO live_entry (session, church, name, branch, date_insert, date_update) VALUES (:session, :church, :name, 'MEMBER', NOW(), NOW())");
            $stmt->execute(['session' => session_id(), 'church' => $church, 'name' => $user_name]);
            $this->db->commit();
            return $history_idx;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    // 현재 송출중인 방송 정보 조회
    public function get_active_live($church) {
        $stmt = $this->db->prepare(
            "SELECT `use`, `view`, `link` FROM live_list WHERE church = :church AND `use` = 'Y' AND `view` = 'Y' ORDER BY idx DESC LIMIT 1"
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
    public function refresh_live_entry($church, $user_name) {
        $params = ['session' => session_id(), 'name' => $user_name];
        $stmt = $this->db->prepare("SELECT idx FROM live_entry WHERE session = :session AND name = :name AND branch = 'MEMBER' LIMIT 1");
        $stmt->execute($params);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($entry) {
            $stmt = $this->db->prepare("UPDATE live_entry SET date_update = NOW() WHERE idx = :idx");
            $stmt->execute(['idx' => $entry['idx']]);
        } else {
            // 시간 만료로 정리된 접속자만 복구하며 이력은 추가하지 않는다.
            $stmt = $this->db->prepare("INSERT INTO live_entry (session, church, name, branch, date_insert, date_update) VALUES (:session, :church, :name, 'MEMBER', NOW(), NOW())");
            $stmt->execute($params + ['church' => $church]);
        }
    }

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
