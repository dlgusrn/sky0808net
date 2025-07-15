<?php include_once __DIR__ . '/../layout/header.php'; ?>
<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">블랙리스트 관리</h4>
            <div class="admin-row">
                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th width="10%"><input type="checkbox" id="all_checked"></th>
                            <th>번호</th>
                            <th>차단 닉네임</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                    <?php if (empty($list)): ?>
                        <tr>
                            <td class="text-center" colspan="3">데이터가 없습니다.</td>
                        </tr>
                    <?php else: ?>
                        <?php $subt = 1; ?>
                        <?php foreach ($list as $row): ?>
                        <tr>
                            <td><input type="checkbox" value="<?= $row['idx'] ?>" name="del_check"></td>
                            <td><?= ($page_set * ($page - 1)) + $subt ?></td>
                            <td><?= htmlspecialchars($row['nickname']) ?></td>
                        </tr>
                        <?php if ($subt < $page_set) $subt++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-grid d-flex justify-content-end mb-3">
                <a type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#blacklistAddModal">등록</a>
                <button type="button" class="btn btn-danger" id="blacklist_delete">삭제</button>
            </div>

            <?php
            include_once __DIR__ . '/../../helpers/pagination_helper.php';
            echo render_pagination($page, $total_page, $block_start, $block_end, $total);
            ?>
        </div>
    </div>
</main>

<!-- 링크 등록 모달 -->
<div class="modal fade" id="blacklistAddModal" tabindex="-1" aria-labelledby="blacklistAddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="blacklistAddModalLabel">블랙리스트 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/set_black_list">
                    <div class="container">
                        <div class="mb-3 row">
                            <label for="nickname" class="col-sm-2 col-form-label">닉네임</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="nickname" id="nickname" placeholder="닉네임 입력">
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
    // 블랙리스트 삭제
    $('#blacklist_delete').click(function(){
        var arr = [];
        $('input[name=del_check]:checked').each(function (index, item){
            arr.push($(item).val());
        });

        if(arr.length > 0){
            location.href = '/admin/del_black_list?idx=' + arr;
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
