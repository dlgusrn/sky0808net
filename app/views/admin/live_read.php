<?php
require_once __DIR__ . '/../layout/auth.inc';
if (empty($live_link)) {
    http_response_code(404);
    echo '방송 정보를 찾을 수 없습니다.';
    return;
}
$row = $live_link;
include_once __DIR__ . '/../layout/header.php';
?>
<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">생방송 링크 관리</h4>
            <div class="container-md admin-row">
                <div class="mb-3 row">
                    <label for="title" class="col-sm-2 col-form-label">교회</label>
                    <div class="col-sm-10">
                        <select class="form-select" name="church" id="church" disabled>
                            <option value="">-</option>
                            <option value="sky" <?='sky' == $row['church'] ? 'selected' : ''?>>하늘문</option>
                            <option value="beer" <?='beer' == $row['church'] ? 'selected' : ''?>>브엘성회</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="title" class="col-sm-2 col-form-label">제목</label>
                    <div class="col-sm-3">
                        <select class="form-select" name="worship" id="worship" disabled>
                            <option value="">예배시간</option>
                            <option value="낮예배" <?='낮예배' == $row['worship'] ? 'selected' : ''?>>낮예배</option>
                            <option value="밤예배" <?='밤예배' == $row['worship'] ? 'selected' : ''?>>밤예배</option>
                            <option value="11시기도" <?='11시기도' == $row['worship'] ? 'selected' : ''?>>11시기도</option>
                            <option value="특별예배" <?='특별예배' == $row['worship'] ? 'selected' : ''?>>특별예배</option>
                            <option value="etc" <?='etc' == $row['worship'] ? 'selected' : ''?>>직접입력</option>
                        </select>
                    </div>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" value="<?=htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8')?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="link" class="col-sm-2 col-form-label">링크</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="https://youtu.be/<?=htmlspecialchars((string) $row['link'], ENT_QUOTES, 'UTF-8')?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="password" class="col-sm-2 col-form-label">비밀번호</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="<?=htmlspecialchars((string) $row['passwd'], ENT_QUOTES, 'UTF-8')?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row justify-content-center">
                    <div class="live col-6 admin-link-preview">
                        <iframe src="https://www.youtube.com/embed/<?=htmlspecialchars((string) $row['link'], ENT_QUOTES, 'UTF-8')?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="d-grid gap-2 d-flex justify-content-end">
                    <a type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#livemodifyModal">수정</a>
                    <a type="button" class="btn btn-danger delete" id="<?=(int) $idx?>">삭제</a>
                    <a type="button" class="btn btn-light" href="/admin/live_list">목록</a>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 링크 수정 모달 -->
<div class="modal fade" id="livemodifyModal" tabindex="-1" aria-labelledby="livemodifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="livemodifyModalLabel">생방송 링크 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/set_live_list?idx=<?=(int) $idx?>">
                    <?=csrf_field()?>
                    <div class="container">
                        <div class="mb-3 row">
                            <label for="title" class="col-sm-2 col-form-label">교회</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="church" id="church">
                                    <option value="">-</option>
                                    <option value="sky" <?='sky' == $row['church'] ? 'selected' : ''?>>하늘문</option>
                                    <option value="beer" <?='beer' == $row['church'] ? 'selected' : ''?>>브엘성회</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="title" class="col-sm-2 col-form-label">제목</label>
                            <div class="col-sm-3">
                                <select class="form-select" name="worship" id="worship">
                                    <option value="">예배시간</option>
                                    <option value="낮예배" <?='낮예배' == $row['worship'] ? 'selected' : ''?>>낮예배</option>
                                    <option value="밤예배" <?='밤예배' == $row['worship'] ? 'selected' : ''?>>밤예배</option>
                                    <option value="11시기도" <?='11시기도' == $row['worship'] ? 'selected' : ''?>>11시기도</option>
                                    <option value="특별예배" <?='특별예배' == $row['worship'] ? 'selected' : ''?>>특별예배</option>
                                    <option value="etc" <?='etc' == $row['worship'] ? 'selected' : ''?>>직접입력</option>
                                </select>
                            </div>
                            <div class="col-sm-7">
                                <input type="date" class="form-control" name="title" id="title" value="<?=htmlspecialchars((string) $row['title'], ENT_QUOTES, 'UTF-8')?>">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="link" class="col-sm-2 col-form-label">링크</label>
                            <div class="col-sm-10">
                            <input type="text" class="form-control" name="link" id="link" value="https://youtu.be/<?=htmlspecialchars((string) $row['link'], ENT_QUOTES, 'UTF-8')?>">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="password" class="col-sm-2 col-form-label">비밀번호</label>
                            <div class="col-sm-10">
                            <input type="text" class="form-control" name="password" id="password" value="<?=htmlspecialchars((string) $row['passwd'], ENT_QUOTES, 'UTF-8')?>">
                            </div>
                        </div>
                        <div class="gap-2 d-flex justify-content-end">
                            <button class="btn btn-primary" type="submit">저장</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // 모달 포커스
    $('.modal').on('shown.bs.modal', function(){
        $('#title').focus();
    });

    // 삭제 확인
    $('.delete').click(function(){
        if(confirm('삭제한 데이터는 복구할 수 없습니다.\n정말로 삭제하시겠습니까?')) {
            $.post('/admin/del_live_list', {idx: $(this).attr('id'), csrf: '<?=csrf_token()?>'})
                .done(function(){ location.href = '/admin/live_list'; });
        }
    });
</script>
