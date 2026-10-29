<?php
require_once __DIR__ . '/../layout/auth.inc';
require_once __DIR__ . '/../../helpers/Notice_support.php';
if (empty($notice)) return;
?>
<div id="popupBackdrop" hidden></div>
<div id="popupWrap" hidden><section id="popup" role="dialog" aria-modal="true" aria-labelledby="notice-popup-title" tabindex="-1">
    <h2 id="notice-popup-title"><?= e($notice['title']) ?></h2><hr>
    <div class="contents mb-3">
        <?php if (!empty($notice['attached'])): ?><img src="/live/notice_attachment?idx=<?= (int) $notice['idx'] ?>" alt="공지사항 이미지" class="mb-3"><?php endif; ?>
        <?php $notice_content = Notice_support::html((string) $notice['contents']); echo strip_tags($notice_content) === $notice_content ? nl2br($notice_content) : $notice_content; ?>
    </div>
    <div id="popup_btn"><button type="button" id="notice-hide-day" class="btn btn-secondary me-2">오늘 하루 보지 않기</button><button type="button" id="notice-close" class="btn btn-light">닫기</button></div>
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
