<?php require_once __DIR__ . '/../layout/auth.inc'; include_once __DIR__ . '/../layout/header.php'; ?>
<main class="live-main"><div class="main-row">
    <div class="nav-left"><?php include_once __DIR__ . '/../layout/admin_nav.php'; ?></div>
    <div class="nav-right">
        <h4 class="main-title">공지사항 관리</h4>
            <?php include __DIR__ . '/list_filters.php'; ?>
        <div class="admin-row"><table class="table table-hover text-center">
            <thead><tr><th>번호</th><th>공지노출</th><th>시작일</th><th>종료일</th><th>제목</th><th>수정일자</th><th>생성일자</th></tr></thead>
            <tbody>
            <?php if (!$list): ?><tr><td colspan="7">데이터가 없습니다.</td></tr><?php endif; ?>
            <?php foreach ($list as $i => $row): ?>
                <tr><td><?= $total - ($page - 1) * $page_set - $i ?></td>
                    <td><button type="button" class="notice-enable btn btn-sm btn-<?= $row['enable'] === 'Y' ? 'success' : 'danger' ?>" data-id="<?= (int) $row['idx'] ?>"><?= e($row['enable']) ?></button></td>
                    <td><?= e($row['start_date']) ?></td><td><?= e($row['end_date']) ?></td>
                    <td><a href="#" class="notice-edit link-dark" data-id="<?= (int) $row['idx'] ?>"><?= e($row['title']) ?></a></td>
                    <td><?= e($row['update_date'] ?: '-') ?></td><td><?= e($row['insert_date']) ?></td></tr>
            <?php endforeach; ?>
            </tbody></table></div>
    </div>
</div></main>
<div class="modal fade" id="notice-modal" tabindex="-1" aria-labelledby="notice-modal-title" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 id="notice-modal-title" class="modal-title">공지사항 등록</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="닫기"></button></div>
    <div class="modal-body"><form method="post" action="/admin/set_notice" enctype="multipart/form-data" id="notice-form">
        <?= csrf_field() ?><input type="hidden" name="idx" id="notice-idx" value="">
        <div class="row mb-3"><div class="col"><label for="notice-start">시작일</label><input type="date" class="form-control" name="start_date" id="notice-start" required></div>
        <div class="col"><label for="notice-end">종료일</label><input type="date" class="form-control" name="end_date" id="notice-end" required></div></div>
        <div class="mb-3"><label for="notice-title">제목</label><input class="form-control" name="title" id="notice-title" maxlength="100" required></div>
        <div class="mb-3"><label for="notice-contents">공지 내용</label><textarea class="form-control" name="contents" id="notice-contents" rows="9"></textarea></div>
        <div class="mb-3">
            <label for="notice-file-name">첨부 이미지 (JPG·PNG·GIF, 최대 5MB)</label>
            <div class="input-group">
                <input type="text" class="form-control" id="notice-file-name" value="첨부파일 없음" readonly aria-label="현재 첨부파일">
                <button type="button" class="btn btn-outline-secondary" id="notice-file-select">파일 선택</button>
            </div>
            <input type="file" name="attached" id="notice-attached" accept="image/jpeg,image/png,image/gif" hidden>
            <small id="notice-file-help" class="text-muted">파일을 선택하지 않으면 기존 첨부가 유지됩니다.</small>
            <div id="notice-preview-wrap" class="mt-3" hidden>
                <img id="notice-preview" alt="첨부 이미지 미리보기">
                <p id="notice-preview-status" class="small text-muted mt-2" aria-live="polite" hidden></p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2"><button type="button" class="btn btn-danger me-auto" id="notice-delete" hidden>삭제</button><div class="d-flex gap-2 ms-auto"><button type="submit" class="btn btn-primary">저장</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button></div></div>
    </form></div>
</div></div></div>

<style>
#notice-preview-wrap{padding:0;border:0;background:transparent}
#notice-preview{display:block;width:auto;height:auto;max-width:min(100%,480px);max-height:240px;object-fit:contain;margin:0 auto}
#notice-preview[hidden]{display:none}
#notice-file-name{min-width:0;text-overflow:ellipsis}
</style>
<script src="https://cdn.tiny.cloud/1/hd244k9tq5qthpits11gthb58cl2lbl22c6w3vw7uga1f8ny/tinymce/6/tinymce.min.js"></script>
<script>
(function () {
    var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('notice-modal'));
    var form = document.getElementById('notice-form');
    var csrf = <?=json_encode(csrf_token())?>;
    var existingAttachment = {url: '', name: ''};
    var previewObjectUrl = '';
    function releasePreview() {
        if (previewObjectUrl) { URL.revokeObjectURL(previewObjectUrl); previewObjectUrl = ''; }
    }
    function showAttachment(url, name, selected) {
        $('#notice-file-name').val(name || '첨부파일 없음');
        $('#notice-file-select').text(name ? '파일 교체' : '파일 선택');
        $('#notice-preview-wrap').prop('hidden', !url);
        $('#notice-preview-status').prop('hidden', true).text('');
        if (url) $('#notice-preview').prop('hidden', false).attr('src', url);
        else $('#notice-preview').removeAttr('src');
    }
    function resetAttachment(url, name) {
        releasePreview();
        existingAttachment = {url: url || '', name: name || ''};
        $('#notice-attached').val('');
        showAttachment(existingAttachment.url, existingAttachment.name, false);
    }
    $('#notice-file-select').on('click', function () { document.getElementById('notice-attached').click(); });
    $('#notice-attached').on('change', function () {
        releasePreview();
        var file = this.files && this.files[0];
        if (!file) { showAttachment(existingAttachment.url, existingAttachment.name, false); return; }
        if (file.size > 5 * 1024 * 1024 || (file.type && ['image/jpeg','image/png','image/gif'].indexOf(file.type) === -1)) {
            alert('JPG, PNG, GIF 이미지(최대 5MB)를 선택해 주세요.');
            this.value = '';
            showAttachment(existingAttachment.url, existingAttachment.name, false);
            return;
        }
        previewObjectUrl = URL.createObjectURL(file);
        showAttachment(previewObjectUrl, file.name, true);
    });
    $('#notice-preview').on('error', function () {
        $(this).prop('hidden', true);
        $('#notice-preview-status').prop('hidden', false).text('미리보기를 불러오지 못했습니다. 첨부파일은 그대로 유지됩니다.');
    });
    $('#notice-modal').on('hidden.bs.modal', function () { releasePreview(); });

    function editor() { return window.tinymce && tinymce.get('notice-contents'); }
    function content(value) { $('#notice-contents').val(value); if (editor()) editor().setContent(value); }
    function fail(xhr) { alert(xhr.responseJSON && xhr.responseJSON.message || '요청에 실패했습니다. 다시 로그인하거나 서버 로그를 확인해 주세요.'); }
    if (window.tinymce) tinymce.init({selector: '#notice-contents', height: 300, menubar: false,
        plugins: 'lists link image table', toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table',
        setup: function (ed) { ed.on('init', function () { ed.setContent($('#notice-contents').val()); }); }});
    $('#notice-add').on('click', function () {
        form.reset(); $('#notice-delete').prop('hidden', true); $('#notice-idx').val(''); content(''); resetAttachment('', '');
        var d = new Date(); var today = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
        $('#notice-start, #notice-end').val(today); $('#notice-modal-title').text('공지사항 등록'); modal.show();
    });
    $('#notice-form').on('submit', function () { if (window.tinymce) tinymce.triggerSave(); });
    $('.notice-edit').on('click', function (event) {
        event.preventDefault();
        $.ajax({url:'/admin/get_notice', data:{idx:$(this).data('id')}, dataType:'json', cache:false})
        .done(function (data) {
            form.reset(); $('#notice-delete').prop('hidden', false).prop('disabled', false); $('#notice-idx').val(data.idx); $('#notice-start').val(data.start_date); $('#notice-end').val(data.end_date);
            $('#notice-title').val(data.title); content(data.contents); $('#notice-modal-title').text('공지사항 수정');
            resetAttachment(data.attachment_url, data.attached); modal.show();
        }).fail(fail);
    });
    $('#notice-delete').on('click', function () {
        var idx = $('#notice-idx').val();
        if (!idx || !confirm('이 공지사항을 삭제하시겠습니까? 삭제 후 복구할 수 없습니다.')) return;
        var button = $(this); button.prop('disabled', true);
        $.ajax({url:'/admin/del_notice', type:'POST', data:{idx:idx,csrf:csrf}, dataType:'json'})
        .done(function (data) {
            if (data.success) window.location.reload();
            else alert(data.message || '삭제하지 못했습니다.');
        }).fail(fail).always(function () { button.prop('disabled', false); });
    });
    $('.notice-enable').on('click', function () {
        var button = $(this); button.prop('disabled', true);
        $.ajax({url:'/admin/update_enable', type:'POST', data:{idx:button.data('id'),csrf:csrf}, dataType:'json'})
        .done(function(data) { button.text(data.enable).toggleClass('btn-success', data.enable === 'Y').toggleClass('btn-danger', data.enable !== 'Y'); })
        .fail(fail).always(function() { button.prop('disabled', false); });
    });
})();
</script>
