<?php include_once __DIR__ . '/../layout/header.php'; ?>
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
                            <option value="sky" <?= $live_link['church'] == 'sky' ? 'selected' : '' ?>>하늘문</option>
                            <option value="beer" <?= $live_link['church'] == 'beer' ? 'selected' : '' ?>>브엘성회</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="title" class="col-sm-2 col-form-label">제목</label>
                    <div class="col-sm-3">
                        <select class="form-select" name="worship" id="worship" disabled>
                            <option value="">예배시간</option>
                            <option value="낮예배" <?= $live_link['worship'] == '낮예배' ? 'selected' : '' ?>>낮예배</option>
                            <option value="밤예배" <?= $live_link['worship'] == '밤예배' ? 'selected' : '' ?>>밤예배</option>
                            <option value="11시기도" <?= $live_link['worship'] == '11시기도' ? 'selected' : '' ?>>11시기도</option>
                            <option value="특별예배" <?= $live_link['worship'] == '특별예배' ? 'selected' : '' ?>>특별예배</option>
                        </select>
                    </div>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($live_link['title']) ?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="link" class="col-sm-2 col-form-label">링크</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="https://youtu.be/<?= htmlspecialchars($live_link['link']) ?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row">
                    <label for="password" class="col-sm-2 col-form-label">비밀번호</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($live_link['passwd']) ?>" readonly>
                    </div>
                </div>
                <div class="mb-3 row justify-content-center">
                    <div class="live col-6">
                        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($live_link['link']) ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="d-grid gap-2 d-flex justify-content-end">
                    <a type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#livemodifyModal">수정</a>
                    <a type="button" class="btn btn-danger delete" id="<?= htmlspecialchars($idx) ?>">삭제</a>
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
                <form method="post" action="/admin/set_live_list?idx=<?= htmlspecialchars($idx) ?>">
                    <div class="container">
                        <div class="mb-3 row">
                            <label for="title" class="col-sm-2 col-form-label">교회</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="church" id="church">
                                    <option value="">-</option>
                                    <option value="sky" <?= $live_link['church'] == 'sky' ? 'selected' : '' ?>>하늘문</option>
                                    <option value="beer" <?= $live_link['church'] == 'beer' ? 'selected' : '' ?>>브엘성회</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="title" class="col-sm-2 col-form-label">제목</label>
                            <div class="col-sm-3">
                                <select class="form-select" name="worship" id="worship">
                                    <option value="">예배시간</option>
                                    <option value="낮예배" <?= $live_link['worship'] == '낮예배' ? 'selected' : '' ?>>낮예배</option>
                                    <option value="밤예배" <?= $live_link['worship'] == '밤예배' ? 'selected' : '' ?>>밤예배</option>
                                    <option value="11시기도" <?= $live_link['worship'] == '11시기도' ? 'selected' : '' ?>>11시기도</option>
                                    <option value="특별예배" <?= $live_link['worship'] == '특별예배' ? 'selected' : '' ?>>특별예배</option>
                                    
                                </select>
                            </div>
                            <div class="col-sm-7">
                                <input type="date" class="form-control" name="title" id="title" value="<?= htmlspecialchars($live_link['title']) ?>">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="link" class="col-sm-2 col-form-label">링크</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="link" id="link" value="https://youtu.be/<?= htmlspecialchars($live_link['link']) ?>">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="password" class="col-sm-2 col-form-label">비밀번호</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="password" id="password" value="<?= htmlspecialchars($live_link['passwd']) ?>">
                            </div>
                        </div>
                        <div class="gap-2 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소</button>
                            <button class="btn btn-primary" type="submit">저장</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // 삭제 확인
    $('.delete').click(function(){
        if(confirm('삭제한 데이터는 복구할 수 없습니다.\n정말로 삭제하시겠습니까?')) {
            window.location.replace('/admin/del_live_list?idx=' + $(this).attr('id'));
        }
    });
</script>
