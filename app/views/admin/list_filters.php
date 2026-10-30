<?php
require_once __DIR__ . '/../layout/auth.inc';
require_once __DIR__ . '/../../helpers/Admin_filters.php';
$has_dates = Admin_filters::uses_dates($list_kind);
$has_church = in_array($list_kind, ['live_list', 'video_list'], true);
?>
<form method="get" action="/admin/<?=e($list_kind)?>" id="admin-list-filter-form" class="admin-list-filters" aria-label="목록 검색">
    <?php if ($has_dates): ?>
    <div class="admin-period-field">
        <label for="list-date-range">날짜 조회</label>
        <select name="range" id="list-date-range" class="form-select">
            <?php foreach (['today'=>'오늘','week'=>'1주일','month'=>'1개월','quarter'=>'3개월','year'=>'1년','custom'=>'직접 선택'] as $range=>$label): ?>
            <option value="<?=e($range)?>" <?=$filters['range']===$range?'selected':''?>><?=e($label)?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="admin-date-field"><label for="list-start-date">시작일자</label><input type="date" id="list-start-date" name="start_date" class="form-control" value="<?=e($filters['start_date'])?>"></div>
    <span class="admin-date-separator" aria-hidden="true">~</span>
    <div class="admin-date-field"><label for="list-end-date">종료일자</label><input type="date" id="list-end-date" name="end_date" class="form-control" value="<?=e($filters['end_date'])?>"></div>
    <?php endif; ?>
    <?php if ($has_church): ?>
    <div class="admin-church-field"><label for="list-church">교회</label><select name="church" id="list-church" class="form-select">
        <?php foreach ([''=>'전체','sky'=>'하늘문','beer'=>'브엘성회'] as $value=>$label): ?><option value="<?=e($value)?>" <?=$filters['church']===$value?'selected':''?>><?=e($label)?></option><?php endforeach; ?>
    </select></div>
    <?php endif; ?>
    <?php if ($list_kind === 'video_list'): ?>
    <div class="admin-preacher-field"><label for="list-preacher">설교자</label><select name="preacher" id="list-preacher" class="form-select">
        <option value="">전체</option>
        <?php $preacher_names = array_unique(array_column($preacher_list ?? [], 'name')); ?>
        <?php if ($filters['preacher'] !== '' && !in_array($filters['preacher'], $preacher_names, true)) $preacher_names[] = $filters['preacher']; ?>
        <?php foreach ($preacher_names as $name): ?><option value="<?=e($name)?>" <?=$filters['preacher']===$name?'selected':''?>><?=e($name)?></option><?php endforeach; ?>
    </select></div>
    <?php endif; ?>
    <div class="admin-search-term"><label for="list-keyword">검색어</label><input type="search" id="list-keyword" name="q" class="form-control" value="<?=e($filters['q'])?>" placeholder="<?=$has_dates?'제목·이름 등 검색':'이름·닉네임 검색'?>"></div>
    <div class="admin-search-actions"><button class="btn btn-outline-primary" type="submit">조회</button><a class="btn btn-outline-secondary" href="/admin/<?=e($list_kind)?>">초기화</a></div>
</form>
<?php include __DIR__ . '/list_toolbar.php'; ?>
<script>
(function () {
    var form = document.getElementById('admin-list-filter-form');
    var range = document.getElementById('list-date-range');
    function submit() { form.requestSubmit ? form.requestSubmit() : form.submit(); }
    if (range) {
        range.addEventListener('change', function () {
            if (range.value === 'custom') document.getElementById('list-start-date').focus();
            else submit();
        });
        ['list-start-date','list-end-date'].forEach(function(id) {
            document.getElementById(id).addEventListener('input', function () { range.value = 'custom'; });
            document.getElementById(id).addEventListener('change', function () {
                range.value = 'custom';
                var start = document.getElementById('list-start-date');
                var end = document.getElementById('list-end-date');
                if (!start.validity.valid || !end.validity.valid) return;
                if (start.value && end.value && start.value > end.value) {
                    if (id === 'list-start-date') end.value = start.value;
                    else start.value = end.value;
                }
                if (form.checkValidity()) submit();
            });
        });
    }
    ['list-per-page','list-church','list-preacher'].forEach(function(id) {
        var input = document.getElementById(id);
        if (input) input.addEventListener('change', submit);
    });
})();
</script>
