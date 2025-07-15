<!-- views/admin/video_list.php -->
<?php include_once __DIR__ . '/../layout/header.php'; ?>
<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">다시보기 영상 관리</h4>
            <div class="admin-row">
                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th width="8%">번호</th>
                            <th width="10%">
                                <select class="form-select form-select-sm" id="preacher_select">
                                    <option value="">설교자</option>
                                    <?php foreach ($preacher_list as $preacher_row): ?>
                                        <option value="<?=htmlspecialchars($preacher_row['name'])?>" <?=($preacher_select == $preacher_row['name']) ? 'selected' : ''?>><?=htmlspecialchars($preacher_row['name'])?></option>
                                    <?php endforeach; ?>
                                </select>
                            </th>
                            <th width="10%">
                                <select class="form-select form-select-sm" id="church_select">
                                    <option value="">교회</option>
                                    <option value="sky" <?=$church_select == 'sky' ? 'selected' : ''?>>하늘문</option>
                                    <option value="beer" <?=$church_select == 'beer' ? 'selected' : ''?>>브엘성회</option>
                                </select>
                            </th>
                            <th>제목</th>
                            <th width="16%">수정일자</th>
                            <th width="16%">생성일자</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                    <?php if (empty($video_list)): ?>
                        <tr>
                            <td class="text-center" colspan="7">데이터가 없습니다.</td>
                        </tr>
                    <?php else: ?>
                        <?php $subt = 0; ?>
                        <?php foreach ($video_list as $row): ?>
                            <?php
                                $church = $row['church'] == 'sky' ? '하늘문' : ($row['church'] == 'beer' ? '브엘성회' : $row['church']);
                                $date = explode('-', $row['title']);
                            ?>
                            <tr>
                                <td><?= $total - ($page_set * ($page - 1)) - $subt ?></td>
                                <td><?= htmlspecialchars($row['preacher']) ?></td>
                                <td><?= $church ?></td>
                                <td>
                                    <a class="link-dark" href="/admin/video_read?idx=<?= $row['idx'] ?>">
                                        <?= $date[0] ?>년 <?= $date[1] ?>월 <?= $date[2] ?>일(<?= $yoil[date('w', strtotime($row['title']))] ?>) <?= htmlspecialchars($row['worship']) ?>
                                    </a>
                                </td>
                                <td><?= !empty($row['date_update']) ? $row['date_update'] : '-' ?></td>
                                <td><?= $row['date_insert'] ?></td>
                            </tr>
                            <?php if ($subt < $page_set) $subt++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid d-flex justify-content-end">
                <a type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#video_add_modal">등록</a>
            </div>
            
            <?php
            include_once __DIR__ . '/../../helpers/pagination_helper.php';
            echo render_pagination($page, $total_page, $block_start, $block_end, $total);
            ?>
        </div>
    </div>
</main>

<!-- 링크 등록 모달 -->
<div class="modal fade" id="video_add_modal" tabindex="-1" aria-labelledby="video_add_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="video_add_modal_label">다시보기 링크 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/set_video_list">
                    <div class="container">
                        <div class="mb-3 row">
                            <label for="preacher" class="col-sm-2 col-form-label">설교자</label>
                            <div class="col-sm-4">
                                <select class="form-select" name="preacher" id="preacher">
                                    <option value="">-</option>
                                    <?php foreach ($preacher_list as $preacher_row): ?>
                                        <option value="<?= htmlspecialchars($preacher_row['name']) ?>"><?= htmlspecialchars($preacher_row['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <label for="church" class="col-sm-2 col-form-label">교회</label>
                            <div class="col-sm-4">
                                <select class="form-select" name="church" id="church">
                                    <option value="">-</option>
                                    <option value="sky">하늘문</option>
                                    <option value="beer">브엘성회</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="worship" class="col-sm-2 col-form-label">제목</label>
                            <div class="col-sm-4">
                                <select class="form-select" name="worship" id="worship">
                                    <option value="">예배시간</option>
                                    <option value="낮예배">낮예배</option>
                                    <option value="밤예배">밤예배</option>
                                    <option value="11시기도">11시기도</option>
                                    <option value="특별예배">특별예배</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <input type="date" class="form-control" id="title" name="title">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="link" class="col-sm-2 col-form-label">링크</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="link" id="link" placeholder="유튜브 링크">
                            </div>
                        </div>
                    </div>
                    <div class="gap-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소</button>
                        <button class="btn btn-primary" type="submit">저장</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // 오늘 날짜 자동 입력
    $('#title').val(new Date().toISOString().substring(0, 10));

    // URL 파라미터 유지 & 특정 값만 변경
    function updateQueryParam(param, value) {
        const url = new URL(window.location.href);
        const searchParams = new URLSearchParams(url.search);

        if (value) {
            searchParams.set(param, value); // update or add param
        } else {
            searchParams.delete(param); // remove if empty
        }

        // 새 URL 적용
        window.location.href = url.pathname + '?' + searchParams.toString();
    }

    // 교회 변경
    $('#church_select').change(function () {
        updateQueryParam('church', $(this).val());
    });

    // 설교자 변경
    $('#preacher_select').change(function () {
        updateQueryParam('preacher', $(this).val());
    });

</script>
