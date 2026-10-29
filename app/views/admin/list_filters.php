<?php require_once __DIR__ . '/../layout/auth.inc'; ?>
<form method="get" class="admin-list-filters" aria-label="목록 검색">
    <?php foreach (['church','preacher'] as $key): ?>
        <?php if (($filters[$key]??'')!==''): ?><input type="hidden" name="<?=e($key)?>" value="<?=e($filters[$key])?>"><?php endif; ?>
    <?php endforeach; ?>
    <input type="hidden" name="range" id="list-date-range" value="<?=e($filters['range'])?>">
    <div class="admin-date-presets" role="group" aria-label="조회 기간 선택">
        <span>조회 기간</span>
        <?php foreach (['week'=>'1주일','month'=>'1개월','quarter'=>'3개월','year'=>'1년','custom'=>'직접 선택'] as $range=>$label): ?>
            <button type="button" class="btn btn-sm <?= $filters['range']===$range?'btn-primary':'btn-outline-secondary' ?>" data-date-range="<?=e($range)?>" aria-pressed="<?=$filters['range']===$range?'true':'false'?>"><?=e($label)?></button>
        <?php endforeach; ?>
    </div>
    <div><label for="list-per-page">표시 개수</label><select name="per_page" id="list-per-page" class="form-select">
        <?php foreach ([10,20,50,100] as $size): ?><option value="<?=$size?>" <?=$page_set===$size?'selected':''?>><?=$size?>개씩 보기</option><?php endforeach; ?>
    </select></div>
    <div><label for="list-start-date"><?=e($date_label)?> 시작</label><input type="date" id="list-start-date" name="start_date" class="form-control" value="<?=e($filters['start_date'])?>"></div>
    <div><label for="list-end-date"><?=e($date_label)?> 종료</label><input type="date" id="list-end-date" name="end_date" class="form-control" value="<?=e($filters['end_date'])?>"></div>
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
        document.getElementById(id).addEventListener('change', function() { select('custom'); });
    });
    document.getElementById('list-per-page').addEventListener('change', submit);
})();
</script>
