<?php
require_once __DIR__ . '/../../helpers/pagination_helper.php';
$toolbar_actions = [
    'live_list' => ['modal'=>'liveaddModal'],
    'video_list' => ['modal'=>'video_add_modal'],
    'black_list' => ['modal'=>'blacklistAddModal','delete'=>'blacklist_delete'],
    'admin_list' => ['modal'=>'admin_add_modal','delete'=>'admin_delete'],
    'preacher_list' => ['modal'=>'preacheraddModal','delete'=>'preacher_delete'],
    'notice' => ['add'=>'notice-add'],
];
$toolbar_action = $toolbar_actions[$list_kind] ?? [];
$toolbar_page = $page ?? $filters['page'];
$toolbar_last = $total_page ?? max(1, (int) ceil($total / $page_set));
$toolbar_start = $block_start ?? ((int) floor(($toolbar_page - 1) / 5) * 5 + 1);
$toolbar_end = $block_end ?? min($toolbar_last, $toolbar_start + 4);
?>
<div class="admin-list-toolbar" role="region" aria-label="목록 도구">
    <div class="admin-list-toolbar-left">
        <span class="admin-list-count">총 <strong><?=number_format($total)?></strong>건</span>
        <?php if (isset($toolbar_action['modal'])): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#<?=e($toolbar_action['modal'])?>">등록하기</button>
        <?php elseif (isset($toolbar_action['add'])): ?>
        <button type="button" class="btn btn-primary" id="<?=e($toolbar_action['add'])?>">등록하기</button>
        <?php endif; ?>
        <?php if (isset($toolbar_action['delete'])): ?><button type="button" class="btn btn-danger" id="<?=e($toolbar_action['delete'])?>">선택 삭제</button><?php endif; ?>
    </div>
    <div class="admin-list-toolbar-right">
        <?=render_pagination($toolbar_page, $toolbar_last, $toolbar_start, $toolbar_end, $total)?>
        <label for="list-per-page" class="visually-hidden">표시 개수</label>
        <select name="per_page" id="list-per-page" form="admin-list-filter-form" class="form-select" aria-label="표시 개수">
            <?php foreach ([10,20,50,100] as $size): ?><option value="<?=$size?>" <?=$page_set===$size?'selected':''?>><?=$size?>개</option><?php endforeach; ?>
        </select>
    </div>
</div>
