<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/models/Notice_model.php';
$count = 0;
function notice_check($ok, $label) {
    global $count;
    if (!$ok) throw new RuntimeException($label);
    $count++;
}
$data = ['title' => '시험 공지', 'start_date' => '2026-10-29', 'end_date' => '2026-10-30', 'contents' => '<p>한글 <strong>본문</strong></p>'];
$valid = Notice_support::validate($data);
notice_check($valid['title'] === '시험 공지' && strpos($valid['contents'], '한글') !== false, 'Korean content');
foreach ([['start_date' => '2026-02-30'], ['end_date' => '2026-10-28'], ['title' => ' ']] as $bad) {
    $failed = false;
    try { Notice_support::validate(array_replace($data, $bad)); } catch (InvalidArgumentException $error) { $failed = true; }
    notice_check($failed, 'Date/title validation');
}
$html = Notice_support::html('<p onclick="alert(1)" style="text-align:center;background:url(javascript:evil)">본문</p><script>alert(1)</script><img src="javascript:alert(1)" onerror="evil()"><a href="https://example.com">링크</a><iframe src="https://example.com"></iframe>');
notice_check(strpos($html, '<script') === false && strpos($html, '<iframe') === false && strpos($html, 'onclick') === false && strpos($html, 'onerror') === false && strpos($html, 'javascript:') === false, 'Unsafe HTML removed');
notice_check(strpos($html, 'text-align:center') !== false && strpos($html, 'href="https://example.com"') !== false, 'Formatting preserved');
foreach (['existing_123.jpg', 'file.png', 'file.gif'] as $name) notice_check((bool) Notice_support::filename($name), 'Valid filename');
foreach (['../file.jpg', 'file.php', 'x.svg', 'x.jpg/evil.php', "x.jpg\n"] as $name) notice_check(!Notice_support::filename($name), 'Invalid filename');
notice_check(Notice_support::upload(null) === '' && Notice_support::upload(['error' => UPLOAD_ERR_NO_FILE]) === '', 'No upload keeps attachment');
notice_check(strpos(Notice_support::html('<img src="/live/uploads/existing.jpg">'), '/live/notice_attachment?file=existing.jpg') !== false, 'Legacy inline image URL migrated');
$failed = false;
try { Notice_support::upload(['error' => UPLOAD_ERR_INI_SIZE]); } catch (InvalidArgumentException $error) { $failed = true; }
notice_check($failed, 'Upload errors handled');
class NoticeTestStatement {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function execute($params) { $this->db->params = $params; return true; }
}
class NoticeTestDB {
    public $sql; public $params;
    public function prepare($sql) { $this->sql = $sql; return new NoticeTestStatement($this); }
}
class NoticeTestModel extends Notice_model {
    public function __construct($db) { $this->db = $db; }
    public function find($idx) { return $idx === 1 ? ['attached' => 'existing.jpg'] : false; }
}
$db = new NoticeTestDB();
$model = new NoticeTestModel($db);
$model->save(1, $data, null);
notice_check(strpos($db->sql, 'attached') === false && !isset($db->params['attached']), 'Existing attachment retained on edit');
$model->save(null, $data, null);
notice_check($db->params['attached'] === '', 'New notice without attachment');
$failed = false;
try { $model->save(999, $data, null); } catch (InvalidArgumentException $error) { $failed = true; }
notice_check($failed, 'Missing notice rejected before upload');
echo 'PASS: ', $count, ' notice validation, HTML, upload and attachment preservation checks.', PHP_EOL;
