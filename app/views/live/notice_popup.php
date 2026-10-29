<?php
require_once __DIR__ . '/../layout/auth.inc';
require_once __DIR__ . '/../../helpers/Notice_support.php';
if (empty($notice)) return;
?>
<div id="popupBackdrop" hidden></div>
<div id="popupWrap" hidden><section id="popup" role="dialog" aria-modal="true" aria-labelledby="notice-popup-title" tabindex="-1">
    <header class="notice-popup-header">
        <div class="notice-popup-heading">
            <span class="notice-popup-kicker">하늘문교회 · 공지사항</span>
            <h2 id="notice-popup-title"><?= e($notice['title']) ?></h2>
        </div>
        <button type="button" id="notice-dismiss" aria-label="공지사항 닫기">×</button>
    </header>
    <div class="contents mb-3">
        <?php if (!empty($notice['attached'])): ?><img src="/live/notice_attachment?idx=<?= (int) $notice['idx'] ?>" alt="공지사항 이미지" class="mb-3"><?php endif; ?>
        <?php $notice_content = Notice_support::html((string) $notice['contents']); echo strip_tags($notice_content) === $notice_content ? nl2br($notice_content) : $notice_content; ?>
    </div>
    <div id="popup_btn"><button type="button" id="notice-hide-day" class="btn btn-secondary me-2">오늘 하루 보지 않기</button><button type="button" id="notice-close" class="btn btn-primary">확인</button></div>
</section></div>
<style>
#popupBackdrop[hidden],#popupWrap[hidden]{display:none!important}
html.notice-page{transform:none}
html.notice-open,html.notice-open body{overflow:hidden}
#popupWrap{inset:0;width:auto;height:auto;padding:16px;box-sizing:border-box}
#popup{display:flex;flex-direction:column;width:min(720px,calc(100vw - 32px));min-width:0;max-width:100%;min-height:0;max-height:90vh;box-sizing:border-box;overflow:hidden}
#popup>h2,#popup>hr,#popup>#popup_btn{flex-shrink:0}
#popup .contents img{display:block;width:auto!important;height:auto!important;max-width:min(100%,640px)!important;max-height:min(420px,55vh)!important;object-fit:contain;margin:0 auto 16px}
#popup .contents{flex:0 1 auto;min-height:0;min-width:0;overflow:auto;overflow-wrap:anywhere}
#popup .contents>:last-child{margin-bottom:0!important}
#popup .contents table{max-width:100%}
@media(max-width:767px){#popup{min-width:0;padding:24px 20px}}

#popupBackdrop{background:rgba(20,32,54,.52);backdrop-filter:blur(4px)}
#popup{background:#fff;border:1px solid #e4e9f1;border-radius:20px;box-shadow:0 24px 80px rgba(16,31,61,.22);padding:28px;color:#25324a}
#popup .notice-popup-header{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;flex:0 0 auto;margin-bottom:22px}
#popup .notice-popup-heading{min-width:0}
#popup .notice-popup-kicker{display:inline-block;margin-bottom:10px;padding:4px 10px;border-radius:6px;background:#eef3ff;color:#355cde;font-size:11px;font-weight:600;letter-spacing:.2px}
#popup #notice-popup-title{margin:0;color:#17243d;font-size:24px;font-weight:700;line-height:1.45;letter-spacing:-.7px;overflow-wrap:anywhere}
#popup #notice-dismiss{flex:0 0 32px;width:32px;height:32px;border:0;border-radius:8px;background:#f3f5f9;color:#758198;font-size:25px;line-height:1;cursor:pointer}
#popup #notice-dismiss:hover{background:#e9eef8;color:#25324a}
#popup .contents{margin-bottom:0!important;padding-right:4px;font-size:14px;line-height:1.85;color:#536078;scrollbar-width:thin}
#popup .contents img{border-radius:10px}
#popup .contents a{color:#355cde;text-underline-offset:3px}
#popup #popup_btn{display:flex;justify-content:flex-end;gap:10px;flex:0 0 auto;margin-top:22px;padding-top:18px;border-top:1px solid #edf0f5}
#popup #popup_btn .btn{min-height:42px;padding:9px 18px;margin:0!important;border-radius:8px;font-size:13px;font-weight:500}
#popup #notice-hide-day{background:#f4f6fa;color:#64718a;border:1px solid #e4e9f1}
#popup #notice-close{min-width:84px;background:#355cde;color:#fff;border:1px solid #355cde}
#popup button:focus-visible,#popup a:focus-visible{outline:3px solid #8ca8ff;outline-offset:3px}
@media(max-width:767px){#popup{padding:22px 20px;border-radius:16px}#popup #notice-popup-title{font-size:20px}#popup #popup_btn{margin-top:18px}#popup #popup_btn .btn{flex:1;padding:9px 10px}}
</style>
<script>
(function () {
    document.documentElement.classList.add('notice-page');
    var backdrop = document.getElementById('popupBackdrop'), wrap = document.getElementById('popupWrap');
    var previous = document.activeElement;
    function close() { backdrop.hidden = wrap.hidden = true; document.documentElement.classList.remove('notice-open'); if (previous && previous.focus) previous.focus(); }
    if (!document.cookie.split(';').some(function (item) { return item.trim() === 'notice_hide=Y'; })) {
        document.documentElement.classList.add('notice-open');
        backdrop.hidden = wrap.hidden = false; document.getElementById('popup').focus();
    }
    document.getElementById('notice-close').onclick = close;
    document.getElementById('notice-dismiss').onclick = close;
    document.getElementById('notice-hide-day').onclick = function () {
        document.cookie = 'notice_hide=Y; Max-Age=86400; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : ''); close();
    };
    wrap.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') { close(); return; }
        if (event.key !== 'Tab') return;
        var focusable = wrap.querySelectorAll('a[href],button:not([disabled]),input,select,textarea,[tabindex="0"]');
        if (!focusable.length) return;
        var first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && (document.activeElement === first || document.activeElement.id === 'popup')) { last.focus(); event.preventDefault(); }
        else if (!event.shiftKey && document.activeElement === last) { first.focus(); event.preventDefault(); }
    });
})();
</script>
