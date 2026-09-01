<?php
include_once '../inc/config.inc';
require_admin('live_list');
require_post();
require_csrf();

$uploadDir = $_SERVER['DOCUMENT_ROOT'].SITE_LIVE.'/uploads';

$attachedFile = ''; // 업로드된 파일명 저장 변수

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 첨부파일 업로드 처리
    if (isset($_FILES['attached']) && $_FILES['attached']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['attached']['tmp_name'];
        $fileExt = strtolower(pathinfo($_FILES['attached']['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fileTmpPath);
        if (!isset($allowedTypes[$mime]) || $_FILES['attached']['size'] > 5 * 1024 * 1024) {
            http_response_code(422);
            exit;
        }
        $fileExt = $allowedTypes[$mime];
        $newFileName = uniqid() . '.' . $fileExt; // 고유한 파일명 생성
        
        $destPath = $uploadDir . '/' . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $attachedFile = $newFileName;
        } else {
            echo "파일 업로드에 실패했습니다.";
            exit;
        }
    }

    $type = $_POST['type'] ?? '';
    $params = [
        'start_date' => $_POST['start_date'] ?? '', 'end_date' => $_POST['end_date'] ?? '',
        'title' => trim((string) ($_POST['title'] ?? '')), 'contents' => $_POST['contents'] ?? '',
        'attached' => $attachedFile,
    ];
    if ($params['title'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['start_date']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $params['end_date'])) { http_response_code(422); exit; }

    if ($type === 'S') {
        $sql = 'INSERT INTO notice (start_date, end_date, title, contents, attached, insert_date) VALUES (:start_date, :end_date, :title, :contents, :attached, NOW())';
        $DB->prepare($sql)->execute($params);
    } elseif ($type === 'M') {
        $params['idx'] = input_int('idx', $_POST);
        // 첨부파일을 교체하지 않은 수정에서는 기존 값을 유지한다.
        if ($attachedFile === '') { unset($params['attached']); $sql = 'UPDATE notice SET start_date = :start_date, end_date = :end_date, title = :title, contents = :contents, update_date = NOW() WHERE idx = :idx'; }
        else { $sql = 'UPDATE notice SET start_date = :start_date, end_date = :end_date, title = :title, contents = :contents, attached = :attached, update_date = NOW() WHERE idx = :idx'; }
        $DB->prepare($sql)->execute($params);
    } else {
        http_response_code(422); exit;
    }
    
    // 처리 후 리다이렉트
    header("Location: " . SITE_LIVE . "/admin/live/notice.html");
    exit;
}
?>
