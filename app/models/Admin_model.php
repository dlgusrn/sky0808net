<?php
class Admin_model extends Model {

    // 관리자 정보 조회
    public function get_admin_by_name($admin_name) {
        $stmt = $this->db->prepare("SELECT * FROM member_admin WHERE username = :name");
        $stmt->execute(['name' => $admin_name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 시스템 로그 기록
    public function system_log($level, $message) {
        $stmt = $this->db->prepare("INSERT INTO system_log (log_lev, message, insert_date) VALUES (:lev, :msg, NOW())");
        $stmt->execute(['lev' => $level, 'msg' => $message]);
    }

    // 생방송 접속 기록 삭제
    public function delete_live_entry($admin_name) {
        $stmt = $this->db->prepare("DELETE FROM live_entry WHERE name LIKE :name AND branch = 'ADMIN'");
        $stmt->execute(['name' => "%{$admin_name}%"]);
    }

    // 생방송 접속 기록 추가
    public function add_live_entry($session, $church, $admin_name) {
        $stmt = $this->db->prepare("INSERT INTO live_entry (`session`, branch, church, name, date_insert) VALUES (:session, 'ADMIN', :church, :name, NOW())");
        $stmt->execute([
            'session' => $session,
            'church' => $church,
            'name'    => $admin_name
        ]);
    }

/** ===================================================================================== */
  
    /** 생방송 보기 시작 */

    // 교회별 현재 송출중인 예배 정보 가져오기
    public function get_live_worship($church) {
        $stmt = $this->db->prepare("SELECT * FROM live_list WHERE church = :church AND `view` = 'Y' ORDER BY idx DESC LIMIT 1");
        $stmt->execute(['church' => $church]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 실시간 접속자 목록 조회
    public function get_live_users($church) {
        $stmt = $this->db->prepare("SELECT name, branch FROM live_entry WHERE church = :church");
        $stmt->execute(['church' => $church]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /** 생방송 보기 끝 */

/** ===================================================================================== */
    
    /** 생방송 링크 관리 시작 */

    // 생방송 링크 목록 조회 (페이징, 교회조건)
    public function get_live_list($church_select, $start_num, $page_set) {
        $where = '';
        $params = [];
        if (!empty($church_select)) {
            $where = "WHERE church = :church";
            $params['church'] = $church_select;
        } else {
            $where = "WHERE church IN ('sky', 'beer')";
        }
        $sql = "SELECT * FROM live_list $where ORDER BY idx DESC LIMIT $start_num, $page_set";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 등록된 링크 수량
    public function get_live_total($church_select) {
        $where = '';
        $params = [];
        if (!empty($church_select)) {
            $where = "WHERE church = :church";
            $params['church'] = $church_select;
        } else {
            $where = "WHERE church IN ('sky', 'beer')";
        }
        $sql = "SELECT COUNT(*) FROM live_list $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // 최근 사용한 패스워드
    public function get_last_password() {
        $stmt = $this->db->query("SELECT passwd FROM live_list ORDER BY idx DESC");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // use 상태 변경
    public function toggle_use($idx) {
        // 현재 상태 확인
        $stmt = $this->db->prepare("SELECT `use` FROM live_list WHERE idx = :idx");
        $stmt->execute(['idx' => $idx]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        $new_use = $row['use'] == 'Y' ? 'N' : 'Y';
        $update = $this->db->prepare("UPDATE live_list SET `use` = :use WHERE idx = :idx");
        return $update->execute(['use' => $new_use, 'idx' => $idx]);
    }

    // view 상태 변경
    public function toggle_view($idx, $current_view) {
        $new_view = $current_view == 'Y' ? 'N' : 'Y';
        $update = $this->db->prepare("UPDATE live_list SET `view` = :view WHERE idx = :idx");
        return $update->execute(['view' => $new_view, 'idx' => $idx]);
    }

    // 생방송 링크 정보 가져오기
    public function get_live_link_by_idx($idx) {
        $stmt = $this->db->prepare("SELECT * FROM live_list WHERE idx = :idx");
        $stmt->execute(['idx' => $idx]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 생방송 링크 등록 or 수정
    public function set_live_list($type, $idx = null) {
        $church   = $_POST['church'] ?? '';
        $worship  = $_POST['worship'] ?? '';
        $title    = $_POST['title'] ?? '';
        $link     = $_POST['link'] ?? '';
        $passwd   = $_POST['password'] ?? '';

        // 유튜브 링크 파싱 (생략 가능)
        if (strpos($link, 'youtu.be/') !== false) {
            $link = str_replace('https://youtu.be/', '', $link);
        } elseif (strpos($link, 'youtube.com/watch?v=') !== false) {
            parse_str(parse_url($link, PHP_URL_QUERY), $query);
            $link = $query['v'] ?? $link;
        }

        try {
            if ($type === 'save') {
                $sql = "INSERT INTO live_list (church, worship, title, link, passwd, date_insert)
                        VALUES (:church, :worship, :title, :link, :passwd, NOW())";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    'church'  => $church,
                    'worship' => $worship,
                    'title'   => $title,
                    'link'    => $link,
                    'passwd'  => $passwd
                ]);
            } elseif ($type === 'mod' && $idx) {
                $sql = "UPDATE live_list
                        SET church = :church, worship = :worship, title = :title, link = :link, passwd = :passwd, date_update = NOW()
                        WHERE idx = :idx";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    'church'  => $church,
                    'worship' => $worship,
                    'title'   => $title,
                    'link'    => $link,
                    'passwd'  => $passwd,
                    'idx'     => $idx
                ]);
            } else {
                $success = false;
            }

            if ($success) {
                return true;
            } else {
                // 실패 로그 기록
                $this->system_log('ERROR', '[링크 '.($type === 'save' ? '등록' : '수정')." 실패] church: $church, title: $title, idx: $idx");
                return false;
            }
        } catch (Exception $e) {
            // 예외 발생 시 로그 기록
            $this->system_log('ERROR', '[링크 '.($type === 'save' ? '등록' : '수정')." 예외] " . $e->getMessage());
            return false;
        }
    }


    // 생방송 링크 삭제
    public function delete_live_list($live_idx) {
        $stmt = $this->db->prepare("DELETE FROM live_list WHERE idx = :idx");
        $stmt->execute(['idx' => $live_idx]);
    }

    /** 생방송 링크 관리 끝 */

/** ===================================================================================== */

    /** 생방송 접속 이력 시작 */

    // 총 이력 개수 반환
    public function get_history_total() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM live_history");
        return $stmt->fetchColumn();
    }

    // 이력 목록 반환 (페이징)
    public function get_history_list($start_num, $page_set) {
        $start_num = (int)$start_num;
        $page_set = (int)$page_set;
        $sql = "SELECT * FROM live_history ORDER BY idx DESC LIMIT $start_num, $page_set";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** 생방송 접속 이력 끝 */

/** ===================================================================================== */

    /** 블랙리스트 관리 시작 */

    // 총 개수 반환
    public function get_black_list_total() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM black_list");
        return $stmt->fetchColumn();
    }

    // 블랙리스트 목록 반환 (페이징)
    public function get_black_list($start_num, $page_set) {
        $start_num = (int)$start_num;
        $page_set = (int)$page_set;
        $sql = "SELECT * FROM black_list ORDER BY idx ASC LIMIT $start_num, $page_set";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function set_black_list($nickname) {
        $stmt = $this->db->prepare("INSERT INTO black_list (`nickname`, insert_date) VALUES (:nickname, NOW())");
        $result = $stmt->execute(['nickname' => $nickname]);

        return $result;
    }

    public function delete_black_list($black_list_idx) {
        // 문자열로 들어온 경우 (e.g. '4,5,7') => explode로 배열화
        if (!is_array($black_list_idx)) {
            $black_list_idx = explode(',', $black_list_idx);
        }

        // 정수형으로 필터링 (보안)
        $black_list_idx = array_filter(array_map('intval', $black_list_idx));

        // 빈 배열 예외 처리
        if (empty($black_list_idx)) {
            return false;
        }

        // ?,?,? 동적 생성
        $placeholders = rtrim(str_repeat('?,', count($black_list_idx)), ',');

        // SQL 실행
        $sql = "DELETE FROM black_list WHERE idx IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($black_list_idx);
    }

    /** 블랙리스트 관리 끝 */

/** ===================================================================================== */

    /** 설교 다시보기 시작 */

    // 설교자 목록 조회
    public function get_preacher_select_list() {
        $sql = "SELECT * FROM preacher_list";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 영상 총 개수
    public function get_video_count($church_select = '', $preacher_select = '') {
        $where = [];
        $params = [];

        if (!empty($church_select)) {
            $where[] = "church = :church";
            $params['church'] = $church_select;
        }
        if (!empty($preacher_select)) {
            $where[] = "preacher = :preacher";
            $params['preacher'] = $preacher_select;
        }

        $where_sql = "";
        if (count($where) > 0) {
            $where_sql = "WHERE " . implode(" AND ", $where);
        } else {
            $where_sql = "WHERE church IN ('sky', 'beer')";
        }

        $sql = "SELECT COUNT(*) FROM video_list $where_sql";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // 영상 목록
    public function get_video_list($church_select = '', $preacher_select = '', $start_num = 0, $page_set = 10) {
        $where = [];
        $params = [];
        
        if (!empty($church_select)) {
            $where[] = "church = :church";
            $params['church'] = $church_select;
        }
        if (!empty($preacher_select)) {
            $where[] = "preacher = :preacher";
            $params['preacher'] = $preacher_select;
        }

        $where_sql = "";
        if (count($where) > 0) {
            $where_sql = "WHERE " . implode(" AND ", $where);
        } else {
            $where_sql = "WHERE church IN ('sky', 'beer')";
        }

        $start_num = (int)$start_num;
        $page_set = (int)$page_set;

        $sql = "SELECT * FROM video_list $where_sql ORDER BY idx DESC LIMIT $start_num, $page_set";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 영상 다시보기 링크 정보 가져오기
    public function get_video_link_by_idx($idx) {
        $stmt = $this->db->prepare("SELECT * FROM video_list WHERE idx = :idx");
        $stmt->execute(['idx' => $idx]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 영상 다시보기 등록 or 수정
    public function set_video_list($type, $idx = null) {
        $church   = $_POST['church'] ?? '';
        $worship  = $_POST['worship'] ?? '';
        $preacher = $_POST['preacher'] ?? '';
        $title    = $_POST['title'] ?? '';
        $link     = $_POST['link'] ?? '';

        // 유튜브 링크 파싱 (생략 가능)
        if (strpos($link, 'youtu.be/') !== false) {
            $link = str_replace('https://youtu.be/', '', $link);
        } elseif (strpos($link, 'youtube.com/watch?v=') !== false) {
            parse_str(parse_url($link, PHP_URL_QUERY), $query);
            $link = $query['v'] ?? $link;
        }

        try {
            if ($type === 'save') {
                $sql = "INSERT INTO video_list (church, worship, preacher, title, link, date_insert)
                        VALUES (:church, :worship, :preacher, :title, :link, NOW())";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    'church'   => $church,
                    'worship'  => $worship,
                    'preacher' => $preacher,
                    'title'    => $title,
                    'link'     => $link
                ]);
            } elseif ($type === 'mod' && $idx) {
                $sql = "UPDATE video_list
                        SET church = :church, worship = :worship, preacher = :preacher, title = :title, link = :link, date_update = NOW()
                        WHERE idx = :idx";
                $stmt = $this->db->prepare($sql);
                $success = $stmt->execute([
                    'church'   => $church,
                    'worship'  => $worship,
                    'preacher' => $preacher,
                    'title'    => $title,
                    'link'     => $link,
                    'idx'      => $idx
                ]);
            } else {
                $success = false;
            }

            if ($success) {
                return true;
            } else {
                // 실패 로그 기록
                $this->system_log('ERROR', '[다시보기 '.($type === 'save' ? '등록' : '수정')." 실패] church: $church, title: $title, idx: $idx");
                return false;
            }
        } catch (Exception $e) {
            // 예외 발생 시 로그 기록
            $this->system_log('ERROR', '[다시보기 '.($type === 'save' ? '등록' : '수정')." 예외] " . $e->getMessage());
            return false;
        }
    }

    // 다시보기 링크 삭제
    public function delete_video_list($video_idx) {
        $stmt = $this->db->prepare("DELETE FROM video_list WHERE idx = :idx");
        $stmt->execute(['idx' => $video_idx]);
    }


    /** 설교 다시보기 끝 */

/** ===================================================================================== */

    /** 관리자 관리 시작 */

    // 관리자 리스트
    public function get_admin_list($start_num = 0, $page_set = 6) {
        $start_num = (int)$start_num;
        $page_set = (int)$page_set;
        $sql = "SELECT * FROM member_admin ORDER BY level, idx LIMIT $start_num, $page_set";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function get_admin_count() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM member_admin");
        return $stmt->fetchColumn();
    }

    public function get_admin_by_idx($idx) {
        $stmt = $this->db->prepare("SELECT * FROM member_admin WHERE idx = :idx");
        $stmt->execute(['idx' => $idx]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // 관리자 추가
    public function set_admin($data, $idx = null) {
        $is_insert = is_null($idx);

        // 비밀번호 검사 (수정 시 입력했다면 비교)
        if ($is_insert || !empty($data['password'])) {
            if ($data['password'] !== $data['password_check']) {
                return ['success' => false, 'message' => '비밀번호가 일치하지 않습니다.'];
            }
            $password = hash("sha256", $data['password']);
        }

        // 권한 설정
        $live_manage  = isset($data['live_manage'])  ? 'Y' : 'N';
        $live_list    = isset($data['live_list'])    ? 'Y' : 'N';
        $history      = isset($data['history'])      ? 'Y' : 'N';
        $black_list   = isset($data['black_list'])   ? 'Y' : 'N';
        $video_list   = isset($data['video_list'])   ? 'Y' : 'N';
        $event_video  = isset($data['event_video'])  ? 'Y' : 'N';

        try {
            if ($is_insert) {
                // INSERT
                $sql = "INSERT INTO member_admin (
                            username, password, level, church,
                            live_manage, live_list, history,
                            black_list, video_list, event_video,
                            insert_date
                        ) VALUES (
                            :username, :password, :level, :church,
                            :live_manage, :live_list, :history,
                            :black_list, :video_list, :event_video,
                            NOW()
                        )";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'username'     => $data['username'],
                    'password'     => $password,
                    'level'        => $data['level'],
                    'church'       => $data['church'],
                    'live_manage'  => $live_manage,
                    'live_list'    => $live_list,
                    'history'      => $history,
                    'black_list'   => $black_list,
                    'video_list'   => $video_list,
                    'event_video'  => $event_video
                ]);
            } else {
                // UPDATE
                $sql = "UPDATE member_admin SET
                            username     = :username,
                            level        = :level,
                            church       = :church,
                            live_manage  = :live_manage,
                            live_list    = :live_list,
                            history      = :history,
                            black_list   = :black_list,
                            video_list   = :video_list,
                            event_video  = :event_video,
                            update_date  = NOW()";

                $params = [
                    'username'     => $data['username'],
                    'level'        => $data['level'],
                    'church'       => $data['church'],
                    'live_manage'  => $live_manage,
                    'live_list'    => $live_list,
                    'history'      => $history,
                    'black_list'   => $black_list,
                    'video_list'   => $video_list,
                    'event_video'  => $event_video,
                    'idx'          => $idx
                ];

                // 비밀번호도 바꾸고 싶을 때만 추가
                if (!empty($data['password'])) {
                    $sql .= ", password = :password";
                    $params['password'] = $password;
                }

                $sql .= " WHERE idx = :idx";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            return ['success' => true];

        } catch (PDOException $e) {
            $mode = $is_insert ? '추가' : '수정';
            $this->system_log('ERROR', "[관리자 {$mode} 실패] " . $e->getMessage());
            return ['success' => false, 'message' => "DB 처리 중 오류가 발생했습니다."];
        }
    }


    // 관리자 삭제
    public function delete_admin($admin_idx) {
        // 문자열로 들어온 경우 (e.g. '4,5,7') => explode로 배열화
        if (!is_array($admin_idx)) {
            $admin_idxs = explode(',', $admin_idx);
        }

        // 정수형으로 필터링 (보안)
        $admin_idxs = array_filter(array_map('intval', $admin_idxs));

        // 빈 배열 예외 처리
        if (empty($admin_idxs)) {
            return false;
        }

        // ?,?,? 동적 생성
        $placeholders = rtrim(str_repeat('?,', count($admin_idxs)), ',');

        // SQL 실행
        $sql = "DELETE FROM member_admin WHERE idx IN ($placeholders)";
        $stmt = $this->db->prepare($sql);

        $this->system_log('INFO', '[관리자 삭제] 관리자 id : '.$admin_idx.' / 삭제한 관리자 : '.$_SESSION['admin_user_name']);
        
        return $stmt->execute($admin_idxs);
    }

    /** 관리자 관리 끝 */

/** ===================================================================================== */

    /** 설교자 관리 시작 */

    // 설교자 리스트
    public function get_preacher_list($start_num = 0, $page_set = 10) {
        $start_num = (int)$start_num;
        $page_set = (int)$page_set;
        $sql = "SELECT * FROM preacher_list ORDER BY idx LIMIT $start_num, $page_set";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // 설교자 총 개수
    public function get_preacher_count() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM preacher_list");
        return $stmt->fetchColumn();
    }

    public function get_preacher_by_idx($idx) {
        $stmt = $this->db->prepare("SELECT * FROM preacher_list WHERE idx = :idx");
        $stmt->execute(['idx' => $idx]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function set_preacher(string $name, $idx = null) {
        try {
            if ($idx) {
                // 수정
                $sql = "UPDATE preacher_list SET name = :name, update_date = NOW() WHERE idx = :idx";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'name' => $name,
                    'idx' => $idx
                ]);
            } else {
                // 추가
                $sql = "INSERT INTO preacher_list (name, insert_date) VALUES (:name, NOW())";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['name' => $name]);
            }

            return ['success' => true];

        } catch (PDOException $e) {
            $this->system_log('ERROR', '[설교자 저장 실패] ' . $e->getMessage());
            return ['success' => false, 'message' => '저장 중 오류 발생: ' . $e->getMessage()];
        }
    }

    // 관리자 삭제
    public function delete_preacher($preacher_idx) {
        // 문자열로 들어온 경우 (e.g. '4,5,7') => explode로 배열화
        if (!is_array($preacher_idx)) {
            $preacher_idx = explode(',', $preacher_idx);
        }

        // 정수형으로 필터링 (보안)
        $preacher_idx = array_filter(array_map('intval', $preacher_idx));

        // 빈 배열 예외 처리
        if (empty($preacher_idx)) {
            return false;
        }

        // ?,?,? 동적 생성
        $placeholders = rtrim(str_repeat('?,', count($preacher_idx)), ',');

        // SQL 실행
        $sql = "DELETE FROM preacher_list WHERE idx IN ($placeholders)";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($preacher_idx);
    }

    
    /** 설교자 관리 끝 */

/** ===================================================================================== */
}
?>
