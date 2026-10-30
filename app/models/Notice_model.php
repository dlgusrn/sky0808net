<?php
require_once __DIR__ . '/../helpers/Notice_support.php';
class Notice_model extends Model {
    public function total() {
        return (int) $this->db->query('SELECT COUNT(*) FROM notice')->fetchColumn();
    }
    public function listing($offset, $limit) {
        return $this->db->query('SELECT idx, enable, DATE(start_date) AS start_date, DATE(end_date) AS end_date, title, insert_date, update_date FROM notice ORDER BY idx DESC LIMIT ' . (int) $offset . ', ' . (int) $limit)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function find($idx) {
        $stmt = $this->db->prepare('SELECT idx, enable, DATE(start_date) AS start_date, DATE(end_date) AS end_date, title, contents, attached FROM notice WHERE idx = :idx');
        $stmt->execute(['idx' => $idx]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function active() {
        return $this->db->query("SELECT idx, title, contents, attached FROM notice WHERE enable = 'Y' AND DATE(start_date) <= CURRENT_DATE() AND DATE(end_date) >= CURRENT_DATE() ORDER BY idx DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    }
    public function save($idx, array $data, $file) {
        $params = Notice_support::validate($data);
        if ($idx && !$this->find($idx)) throw new InvalidArgumentException('공지사항을 찾을 수 없습니다.');
        $attached = Notice_support::upload($file);
        try {
            if ($idx) {
                $sql = 'UPDATE notice SET title = :title, contents = :contents, start_date = :start_date, end_date = :end_date, update_date = NOW()';
                if ($attached !== '') { $sql .= ', attached = :attached'; $params['attached'] = $attached; }
                $sql .= ' WHERE idx = :idx';
                $params['idx'] = $idx;
            } else {
                $sql = 'INSERT INTO notice (title, contents, start_date, end_date, attached, insert_date) VALUES (:title, :contents, :start_date, :end_date, :attached, NOW())';
                $params['attached'] = $attached;
            }
            $this->db->prepare($sql)->execute($params);
        } catch (Throwable $error) {
            if ($attached !== '') @unlink(Notice_support::directory() . '/' . $attached);
            throw $error;
        }
    }
    public function delete($idx) {
        $stmt = $this->db->prepare('DELETE FROM notice WHERE idx = :idx');
        $stmt->execute(['idx' => $idx]);
        return $stmt->rowCount() > 0;
    }
    public function toggle($idx) {
        $stmt = $this->db->prepare("UPDATE notice SET enable = CASE WHEN enable = 'Y' THEN 'N' ELSE 'Y' END, update_date = NOW() WHERE idx = :idx");
        $stmt->execute(['idx' => $idx]);
        return $this->find($idx);
    }
}
