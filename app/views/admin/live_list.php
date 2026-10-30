<?php
require_once __DIR__ . '/../layout/auth.inc';
$num = $total;
$subt = 0;
?>
<?php include_once __DIR__ . '/../layout/header.php'; ?>
<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">생방송 링크 관리</h4>
            <?php include __DIR__ . '/list_filters.php'; ?>
            <div class="admin-row">
                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th width="8%">번호</th>
                            <th width="8%">사용</th>
                            <th width="8%">송출</th>
                            <th width="10%">교회</th>
                            <th>제목</th>
                            <th width="16%">수정일자</th>
                            <th width="16%">생성일자</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle">
                    <?php if (empty($list)) : ?>
                        <tr>
                        <td class="text-center" colspan="7">데이터가 없습니다.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($list as $row) : ?>
                        <?php
                            $church = $row['church'] == 'sky' ? '하늘문' : ($row['church'] == 'beer' ? '브엘성회' : $row['church']);
                            $date = explode('-', $row['title']);
                        ?>
                        <tr>
                            <td><?=$num - ($page_set * ($page - 1)) - $subt?></td>
                            <td><button type="button" class="use btn btn-sm btn-<?=$row['use'] == 'Y' ? 'success' : 'danger'?>" data-id="<?=(int) $row['idx']?>"><?=$row['use']?></button></td>
                            <td><button type="button" class="view btn btn-sm btn-<?=$row['view'] == 'Y' ? 'success' : 'danger'?>" data-id="<?=(int) $row['idx']?>" <?=$row['use'] !== 'Y' ? 'disabled' : ''?>><?=$row['view']?></button></td>
                            <td><?=$church?></td>
                            <td>
                                <a class="link-dark" href="/admin/live_read?idx=<?=$row['idx']?>">
                                    <?=$date[0]?>년 <?=$date[1]?>월 <?=$date[2]?>일(<?=$yoil[date('w', strtotime($row['title']))]?>) <?=$row['worship']?>
                                </a>
                            </td>
                            <td><?=!empty($row['date_update']) ? $row['date_update'] : '-'?></td>
                            <td><?=$row['date_insert']?></td>
                        </tr>
                        <?php if ($subt < $page_set) $subt++; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- 링크 등록 모달 -->
<div class="modal fade" id="liveaddModal" tabindex="-1" aria-labelledby="liveaddModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="liveaddModalLabel">생방송 링크 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/set_live_list">
                    <?=csrf_field()?>
                    <div class="container">
                        <div class="mb-3 row">
                            <label for="title" class="col-sm-2 col-form-label">교회</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="church" id="church">
                                    <option value="">-</option>
                                    <option value="sky">하늘문</option>
                                    <option value="beer">브엘성회</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="title" class="col-sm-2 col-form-label">제목</label>
                            <div class="col-sm-3">
                                <select class="form-select" name="worship" id="worship">
                                    <option value="">예배시간</option>
                                    <option value="낮예배">낮예배</option>
                                    <option value="밤예배">밤예배</option>
                                    <option value="11시기도">11시기도</option>
                                    <option value="특별예배">특별예배</option>
                                </select>
                            </div>
                            <div class="col-sm-7">
                                <input type="date" class="form-control" id="title" name="title">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="link" class="col-sm-2 col-form-label">링크</label>
                            <div class="col-sm-10">
                            <input type="text" class="form-control" name="link" id="link" placeholder="유튜브 링크">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="password" class="col-sm-2 col-form-label">비밀번호</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="password" id="password" value="<?=! empty ( $passRow ) ? $passRow['passwd'] : '019108'?>">
                            </div>
                        </div>
                    </div>
                    <div class="gap-2 d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit">저장</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // 오늘 날짜 자동 입력
    $('#title').val(new Date().toISOString().substring(0, 10));

    // Reload all flags after a write; switching use can change other rows too.
    $('.use, .view').on('click', function () {
        var button = $(this);
        if (button.prop('disabled')) return;
        var kind = button.hasClass('use') ? 'use' : 'view';
        var data = {idx: button.data('id'), csrf: $('meta[name="csrf-token"]').attr('content')};
        data[kind] = button.text().trim();
        $('.use, .view').prop('disabled', true);
        $.ajax({type:'POST', url:'/admin/update_' + kind, data:data, dataType:'json'})
        .done(function (result) {
            if (!result.result) alert('변경하지 못했습니다. 현재 상태를 다시 확인해 주세요.');
            window.location.reload();
        }).fail(function (xhr) {
            alert(xhr.status === 409 ? '방송 상태가 바뀌었거나 사용 중인 방송이 아닙니다. 다시 확인해 주세요.' : '변경하지 못했습니다. 다시 로그인하거나 상태를 확인해 주세요.');
            window.location.reload();
        });
    });
</script>
