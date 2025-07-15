<?php include_once __DIR__ . '/../layout/header.php'; ?>
<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">관리자 관리</h4>
            <div class="admin-row">
                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th width="5%"><input type="checkbox" id="all_checked"></th>
                            <th width="5%">번호</th>
                            <th width="10%">이름</th>
                            <th width="5%">레벨</th>
                            <th width="10%">교회</th>
                            <th width="10%">수정일자</th>
                            <th width="10%">생성일자</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                    <?php if (empty($admin_list)): ?>
                        <tr>
                            <td class="text-center" colspan="7">데이터가 없습니다.</td>
                        </tr>
                    <?php else: ?>
                        <?php $subt = 1; ?>
                        <?php foreach ($admin_list as $row): ?>
                            <?php
                                $church = $row['church'] == 'sky' ? '하늘문' : ($row['church'] == 'beer' ? '브엘성회' : $row['church']);
                            ?>
                            <tr>
                                <td><input type="checkbox" value="<?= $row['idx'] ?>" name="del_check"></td>
                                <td><?= ($page_set * ($page - 1)) + $subt ?></td>
                                <td>
                                    <a href="/admin/admin_read?idx=<?= $row['idx'] ?>" style="text-decoration: none; color: black;">
                                        <?= htmlspecialchars($row['username']) ?>
                                    </a>
                                </td>
                                <td><?= $row['level'] ?></td>
                                <td><?= $church ?></td>
                                <td><?= !empty($row['update_date']) ? $row['update_date'] : '-' ?></td>
                                <td><?= $row['insert_date'] ?></td>
                            </tr>
                            <?php if ($subt < $page_set) $subt++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid d-flex justify-content-end">
                <a type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#admin_add_modal">등록</a>
                <button type="button" class="btn btn-danger" id="admin_delete">삭제</button>
            </div>
            
            <?php
            include_once __DIR__ . '/../../helpers/pagination_helper.php';
            echo render_pagination($page, $total_page, $block_start, $block_end, $total);
            ?>
        </div>
    </div>
</main>

<!-- 관리자 추가 모달 -->
<div class="modal fade" id="admin_add_modal" tabindex="-1" aria-labelledby="admin_add_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="admin_add_modal_label">관리자 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/set_admin">
                    <div class="container">
                        <div class="row mb-4">
                            <div class="col-3">
                                <label for="church" class="col col-form-label">교회</label>
                                <select class="form-select" name="church" id="church">
                                    <option value="sky">하늘문</option>
                                    <option value="beer">브엘성회</option>
                                </select>
                            </div>
                            <div class="col-2">
                                <label for="level" class="col col-form-label">등급</label>
                                <select class="form-select" name="level" id="level">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                            <div class="col">
                                <label for="username" class="col col-form-label">이름</label>
                                <input type="text" class="form-control" name="username" id="username" placeholder="관리자 이름">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col">
                                <label for="password" class="col col-form-label">비밀번호</label>
                                <input type="password" class="form-control" name="password" id="password" placeholder="관리자 비밀번호">
                            </div>
                            <div class="col">
                                <label for="password_check" class="col col-form-label">비밀번호 확인</label>
                                <input type="password" class="form-control" name="password_check" id="password_check" placeholder="비밀번호 확인">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="mb-2">권한</div>
                            <div class="row">
                                <div class="col">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="live_manage" id="live_manage" checked>
                                        <label class="form-check-label" for="live_manage">생방송 보기</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="black_list" id="black_list" checked>
                                        <label class="form-check-label" for="black_list">블랙리스트 관리</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="live_list" id="live_list" checked>
                                        <label class="form-check-label" for="live_list">생방송 링크 관리</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="video_list" id="video_list" checked>
                                        <label class="form-check-label" for="video_list">연도별</label>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="history" id="history" checked>
                                        <label class="form-check-label" for="history">생방송 접속 이력 관리</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="event_video" id="event_video" checked>
                                        <label class="form-check-label" for="event_video">행사</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="gap-2 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">저장</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // 레벨별 권한 자동 제어
    $('select[name=level]').change(function () {
        const level = $(this).val();

        const setAccess = (ids, checked) => {
            ids.forEach(id => $('#' + id).prop('checked', checked));
        }

        switch (level) {
            case '1':
                setAccess(['live_manage', 'live_list', 'history', 'black_list', 'video_list', 'event_video'], true);
                break;
            case '2':
                setAccess(['live_manage', 'live_list', 'black_list', 'video_list', 'event_video'], true);
                $('#history').prop('checked', false);
                break;
            case '3':
                setAccess(['live_manage', 'live_list', 'black_list'], true);
                setAccess(['video_list', 'event_video', 'history'], false);
                break;
            case '4':
                setAccess(['live_manage'], true);
                setAccess(['live_list', 'black_list', 'video_list', 'event_video', 'history'], false);
                break;
        }
    });

    // 관리자 삭제
    $('#admin_delete').click(function(){
        var arr = [];
        $('input[name=del_check]:checked').each(function (index, item){
            arr.push($(item).val());
        });

        if(arr.length > 0){
            location.href = '/admin/del_admin?idx=' + arr;
        } else {
            alert('선택된 항목이 없습니다.');
        }
    });

    // 삭제할 목록 체크 + 전체 선택 기능
    $('#all_checked').click(function(){
        if($(this).is(':checked')){
            $('input[name=del_check]').prop('checked', true);
        } else {
            $('input[name=del_check]').prop('checked', false);
        }
    });
    $('input[name=del_check]').click(function(){
        var total = $('input[name=del_check]').length;
		var checked = $('input[name=del_check]:checked').length;

		if(total != checked){
            $('#all_checked').prop('checked', false);
        } else {
            $('#all_checked').prop('checked', true);
        }
    });
</script>