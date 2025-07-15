<!-- views/admin/preacher_list.php -->
<?php include_once __DIR__ . '/../layout/header.php'; ?>
<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">설교자 관리</h4>
            <div class="admin-row">
                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th width="5%"><input type="checkbox" id="all_checked"></th>
                            <th width="5%">번호</th>
                            <th width="10%">이름</th>
                            <th width="10%">수정일자</th>
                            <th width="10%">생성일자</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                    <?php if (empty($preacher_list)): ?>
                        <tr>
                            <td class="text-center" colspan="5">데이터가 없습니다.</td>
                        </tr>
                    <?php else: ?>
                        <?php $subt = 1; ?>
                        <?php foreach ($preacher_list as $row): ?>
                        <tr>
                            <td><input type="checkbox" value="<?= $row['idx'] ?>" name="del_check"></td>
                            <td><?= ($page_set * ($page - 1)) + $subt ?></td>
                            <td>
                                <a href="#" id="<?= $row['idx'] ?>" class="preacher_mod_modal_btn" style="text-decoration: none; color: black;">
                                    <?= htmlspecialchars($row['name']) ?>
                                </a>
                            </td>
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
                <a type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#preacheraddModal">등록</a>
                <button type="button" class="btn btn-danger" id="preacher_delete">삭제</button>
            </div>
            
            <?php
            include_once __DIR__ . '/../../helpers/pagination_helper.php';
            echo render_pagination($page, $total_page, $block_start, $block_end, $total);
            ?>
        </div>
    </div>
</main>

<!-- 설교자 추가 모달 -->
<div class="modal fade" id="preacheraddModal" tabindex="-1" aria-labelledby="preacheraddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="preacheraddModalLabel">설교자 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/set_preacher">
                    <div class="container">
                        <div class="mb-4 row">
                            <label for="name" class="col-sm-2 col-form-label">설교자 이름</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="name" id="name" placeholder="이름 입력">
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

<!-- 설교자 수정 모달 -->
<div class="modal fade" id="preacher_mod_modal" tabindex="-1" aria-labelledby="preacher_mod_modal_label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="preacher_mod_modal_label">설교자 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/set_preacher">
                    <div class="container">
                        <div class="mb-4 row">
                            <label for="mod_name" class="col-sm-2 col-form-label">설교자 이름</label>
                            <div class="col-sm-10">
                                <input type="hidden" name="mod_idx" id="mod_idx" value="">
                                <input type="text" class="form-control" name="mod_name" id="mod_name" value="" placeholder="이름 입력">
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
    // 설교자 삭제
    $('#preacher_delete').click(function(){
        var arr = [];
        $('input[name=del_check]:checked').each(function (index, item){
            arr.push($(item).val());
        });

        if(arr.length > 0){
            location.href = '/admin/del_preacher?idx=' + arr;
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

    // 설교자 수정 모달 열기
    $('.preacher_mod_modal_btn').click(function(){
        $.ajax({
            type: 'POST',
            url: '/admin/get_preacher',
            data: {idx: $(this).attr('id')},
            dataType: 'json',
            success: function(data) {
                $('#mod_idx').val(data.idx);
                $('#mod_name').val(data.name);
                $('#preacher_mod_modal').modal('show');
            }
        });
    });
</script>
