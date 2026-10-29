<?php
class Notice_support {
    public static function validate(array $data) {
        foreach (['title', 'start_date', 'end_date', 'contents'] as $key) {
            if (isset($data[$key]) && !is_string($data[$key])) throw new InvalidArgumentException('공지 입력값을 확인해 주세요.');
        }
        $title = trim((string) ($data['title'] ?? ''));
        $start = (string) ($data['start_date'] ?? '');
        $end = (string) ($data['end_date'] ?? '');
        foreach ([$start, $end] as $date) {
            if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $parts)
                || !checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
                throw new InvalidArgumentException('공지 기간을 올바르게 입력해 주세요.');
            }
        }
        if ($start > $end) throw new InvalidArgumentException('종료일은 시작일 이후여야 합니다.');
        if ($title === '' || preg_match('//u', $title) !== 1 || preg_match_all('/./us', $title) > 100) throw new InvalidArgumentException('공지 제목을 확인해 주세요.');
        return ['title' => $title, 'start_date' => $start, 'end_date' => $end,
            'contents' => self::html((string) ($data['contents'] ?? ''))];
    }

    public static function html($html) {
        if ($html === '') return '';
        if (!class_exists('DOMDocument')) throw new RuntimeException('PHP DOM 확장이 필요합니다.');
        $previous = libxml_use_internal_errors(true);
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadHTML('<?xml encoding="UTF-8"><html><body>' . $html . '</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $body = $doc->getElementsByTagName('body')->item(0);
        if (!$body) return '';
        $allowed = ['p','br','b','strong','i','em','u','s','ul','ol','li','blockquote','h1','h2','h3','h4','h5','h6','div','span','table','thead','tbody','tfoot','tr','td','th','a','img','hr'];
        $clean = function ($parent) use (&$clean, $allowed) {
            foreach (iterator_to_array($parent->childNodes) as $node) {
                if ($node instanceof DOMComment || $node instanceof DOMProcessingInstruction) {
                    $parent->removeChild($node);
                    continue;
                }
                if (!($node instanceof DOMElement)) continue;
                $tag = strtolower($node->tagName);
                if (in_array($tag, ['script','style','iframe','object','embed','svg','math','form','input','button','link','meta','base','template'], true)) {
                    $parent->removeChild($node);
                    continue;
                }
                $clean($node);
                if (!in_array($tag, $allowed, true)) {
                    while ($node->firstChild) $parent->insertBefore($node->firstChild, $node);
                    $parent->removeChild($node);
                    continue;
                }
                foreach (iterator_to_array($node->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    $value = trim($attr->value);
                    $keep = false;
                    if (($tag === 'a' && $name === 'href') || ($tag === 'img' && $name === 'src')) {
                        if ($tag === 'img' && preg_match('~^/live/uploads/([a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|gif))$~iD', $value, $legacy)) {
                            $value = '/live/notice_attachment?file=' . rawurlencode($legacy[1]);
                            $node->setAttribute($name, $value);
                        }
                        $keep = (bool) preg_match('~^(?:https?://[^\s<>]+|/(?!/)[^\s<>]*)$~i', $value);
                    } elseif (in_array($name, ['alt','title'], true)) {
                        $keep = true;
                    } elseif (in_array($tag, ['td','th'], true) && in_array($name, ['colspan','rowspan'], true)) {
                        $keep = ctype_digit($value) && (int) $value > 0 && (int) $value <= 100;
                    } elseif ($name === 'style') {
                        $styles = [];
                        foreach (explode(';', $value) as $style) {
                            if (preg_match('/^\s*(text-align)\s*:\s*(left|right|center|justify)\s*$/i', $style, $m)) $styles[] = strtolower($m[1] . ':' . $m[2]);
                        }
                        if ($styles) { $node->setAttribute('style', implode(';', $styles)); $keep = true; }
                    }
                    if (!$keep) $node->removeAttribute($attr->name);
                }
            }
        };
        $clean($body);
        $result = '';
        foreach ($body->childNodes as $node) $result .= $doc->saveHTML($node);
        return $result;
    }

    public static function directory() {
        return dirname(__DIR__, 2) . '/storage/notice';
    }
    public static function filename($name) {
        return is_string($name) && preg_match('/^[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|gif)$/iD', $name);
    }
    public static function upload($file) {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
        if (($file['error'] ?? -1) !== UPLOAD_ERR_OK) throw new InvalidArgumentException('첨부파일 업로드에 실패했습니다.');
        $tmp = $file['tmp_name'] ?? '';
        if (!is_uploaded_file($tmp) || filesize($tmp) > 5 * 1024 * 1024) throw new InvalidArgumentException('첨부 이미지는 5MB 이하여야 합니다.');
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!isset($types[$mime]) || @getimagesize($tmp) === false) throw new InvalidArgumentException('JPG, PNG, GIF 이미지만 첨부할 수 있습니다.');
        $name = bin2hex(random_bytes(16)) . '.' . $types[$mime];
        if (!move_uploaded_file($tmp, self::directory() . '/' . $name)) throw new RuntimeException('첨부파일 저장 권한을 확인해 주세요.');
        return $name;
    }
}
