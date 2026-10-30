<?php
require_once __DIR__ . '/../layout/auth.inc';
require_once __DIR__ . '/../../helpers/Admin_filters.php';
if (!Admin_filters::uses_dates($list_kind)):
?>
<form method="get" action="/admin/<?=e($list_kind)?>" class="admin-list-controls d-flex align-items-end justify-content-between flex-wrap gap-3 mb-3" aria-label="목록 검색">
    <div><label for="list-per-page" class="form-label">표시 개수</label><select name="per_page" id="list-per-page" class="form-select w-auto">
        <?php foreach ([10,20,50,100] as $size): ?><option value="<?=$size?>" <?=$page_set===$size?'selected':''?>><?=$size?>개씩 보기</option><?php endforeach; ?>
    </select></div>
    <div class="flex-grow-1"><label for="list-keyword" class="form-label">검색어</label><input type="search" id="list-keyword" name="q" class="form-control" value="<?=e($filters['q'])?>" placeholder="이름·닉네임 검색"></div>
    <div class="d-flex gap-2"><button class="btn btn-primary" type="submit">조회</button><a class="btn btn-outline-secondary" href="/admin/<?=e($list_kind)?>">초기화</a></div>
    <div class="small text-muted w-100">총 <?=number_format($total)?>건</div>
</form>
<script>
document.getElementById('list-per-page').addEventListener('change',function(){this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit();});
</script>
<?php return; endif; ?>
<form method="get" class="admin-list-filters" aria-label="목록 검색">
    <?php foreach (['church','preacher'] as $key): ?>
        <?php if (($filters[$key]??'')!==''): ?><input type="hidden" name="<?=e($key)?>" value="<?=e($filters[$key])?>"><?php endif; ?>
    <?php endforeach; ?>
    <input type="hidden" name="range" id="list-date-range" value="<?=e($filters['range'])?>">
    <div class="admin-date-presets" role="group" aria-label="조회 기간 선택">
        <span>조회 기간</span>
        <?php foreach (['today'=>'오늘','week'=>'1주일','month'=>'1개월','quarter'=>'3개월','year'=>'1년','custom'=>'직접 선택'] as $range=>$label): ?>
            <button type="button" class="btn btn-sm <?= $filters['range']===$range?'btn-primary':'btn-outline-secondary' ?>" data-date-range="<?=e($range)?>" aria-pressed="<?=$filters['range']===$range?'true':'false'?>"><?=e($label)?></button>
        <?php endforeach; ?>
    </div>
    <div><label for="list-per-page">표시 개수</label><select name="per_page" id="list-per-page" class="form-select">
        <?php foreach ([10,20,50,100] as $size): ?><option value="<?=$size?>" <?=$page_set===$size?'selected':''?>><?=$size?>개씩 보기</option><?php endforeach; ?>
    </select></div>
    <div><label for="list-start-date">시작일자</label><input type="date" id="list-start-date" name="start_date" class="form-control" value="<?=e($filters['start_date'])?>"></div>
    <div><label for="list-end-date">종료일자</label><input type="date" id="list-end-date" name="end_date" class="form-control" value="<?=e($filters['end_date'])?>"></div>
    <div class="admin-search-term"><label for="list-keyword">검색어</label><input type="search" id="list-keyword" name="q" class="form-control" value="<?=e($filters['q'])?>" placeholder="제목·이름 등 검색"></div>
    <div class="admin-search-actions"><button class="btn btn-primary" type="submit">조회</button><a class="btn btn-outline-secondary" href="/admin/<?=e($list_kind)?>">초기화</a></div>
    <div class="admin-list-count">총 <?=number_format($total)?>건</div>
</form>
<script>
(function () {
    var form = document.getElementById('list-per-page').form;
    var range = document.getElementById('list-date-range');
    function submit() { form.requestSubmit ? form.requestSubmit() : form.submit(); }
    function select(value) {
        range.value = value;
        form.querySelectorAll('[data-date-range]').forEach(function(button) {
            var active = button.dataset.dateRange === value;
            button.classList.toggle('btn-primary', active);
            button.classList.toggle('btn-outline-secondary', !active);
            button.setAttribute('aria-pressed', String(active));
        });
    }
    form.querySelectorAll('[data-date-range]').forEach(function(button) {
        button.addEventListener('click', function() {
            select(button.dataset.dateRange);
            if (range.value === 'custom') document.getElementById('list-start-date').focus();
            else submit();
        });
    });
    ['list-start-date','list-end-date'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() { select('custom'); });
        document.getElementById(id).addEventListener('change', function() {
            select('custom');
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
    document.getElementById('list-per-page').addEventListener('change', submit);
})();
</script>
