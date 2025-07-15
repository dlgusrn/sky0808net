<?php include_once __DIR__ . '/../layout/header.php'; ?>

<?php
// 요일 배열
$yoil = ['일', '월', '화', '수', '목', '금', '토'];

// 날짜 분리
$date = explode('-', $video['title']);
?>

<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">다시보기 영상 관리</h4>
            <div class="container-md admin-row">
                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">설교자</label>
                    <div class="col-sm-4">
                        <select class="form-select" disabled>
                            <option value="">-</option>
                            <?php foreach ($preacher_list as $preacher_row): ?>
                                <option value="<?= htmlspecialchars($preacher_row['name']) ?>" <?= $preacher_row['name'] == $video['preacher'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($preacher_row['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <label class="col-sm-2 col-form-label">교회</label>
                    <div class="col-sm-4">
                        <select class="form-select" disabled>
                            <option value="">-</option>
                            <option value="sky" <?= $video['church'] == 'sky' ? 'selected' : '' ?>>하늘문</option>
                            <option value="beer" <?= $video['church'] == 'beer' ? 'selected' : '' ?>>브엘성회</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">제목</label>
                    <div class="col-sm-3">
                        <select class="form-select" disabled>
                            <option value="">예배시간</option>
                            <option value="낮예배" <?= $video['worship'] == '낮예배' ? 'selected' : '' ?>>낮예배</option>
                            <option value="밤예배" <?= $video['worship'] == '밤예배' ? 'selected' : '' ?>>밤예배</option>
                            <option value="11시기도" <?= $video['worship'] == '11시기도' ? 'selected' : '' ?>>11시기도</option>
                            <option value="특별예배" <?= $video['worship'] == '특별예배' ? 'selected' : '' ?>>특별예배</option>
                            <option value="etc" <?= $video['worship'] == 'etc' ? 'selected' : '' ?>>직접입력</option>
                        </select>
                    </div>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($date[0]) ?>년 <?= htmlspecialchars($date[1]) ?>월 <?= htmlspecialchars($date[2]) ?>일(<?= $yoil[date('w', strtotime($video['title']))] ?>)" readonly>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">링크</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="https://youtu.be/<?= htmlspecialchars($video['link']) ?>" readonly>
                    </div>
                </div>

                <div class="mb-3 row justify-content-center">
                    <div class="live col-6">
                        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($video['link']) ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>

                <div class="d-grid gap-2 d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#livemodifyModal">수정</button>
                    <button type="button" class="btn btn-danger delete" data-idx="<?= htmlspecialchars($video['idx']) ?>">삭제</button>
                    <a class="btn btn-light" href="/admin/video_list">목록</a>
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
                <h5 class="modal-title" id="livemodifyModalLabel">다시보기 링크 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="/admin/set_video_list?idx=<?= htmlspecialchars($video['idx']) ?>">
                    <div class="container">
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">설교자</label>
                            <div class="col-sm-4">
                                <select class="form-select" name="preacher" id="preacher">
                                    <option value="">-</option>
                                    <?php foreach ($preacher_list as $preacher_row): ?>
                                        <option value="<?= htmlspecialchars($preacher_row['name']) ?>" <?= $preacher_row['name'] == $video['preacher'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($preacher_row['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <label class="col-sm-2 col-form-label">교회</label>
                            <div class="col-sm-4">
                                <select class="form-select" name="church" id="church">
                                    <option value="">-</option>
                                    <option value="sky" <?= $video['church'] == 'sky' ? 'selected' : '' ?>>하늘문</option>
                                    <option value="beer" <?= $video['church'] == 'beer' ? 'selected' : '' ?>>브엘성회</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">제목</label>
                            <div class="col-sm-3">
                                <select class="form-select" name="worship" id="worship">
                                    <option value="">예배시간</option>
                                    <option value="낮예배" <?= $video['worship'] == '낮예배' ? 'selected' : '' ?>>낮예배</option>
                                    <option value="밤예배" <?= $video['worship'] == '밤예배' ? 'selected' : '' ?>>밤예배</option>
                                    <option value="11시기도" <?= $video['worship'] == '11시기도' ? 'selected' : '' ?>>11시기도</option>
                                    <option value="특별예배" <?= $video['worship'] == '특별예배' ? 'selected' : '' ?>>특별예배</option>
                                    <option value="etc" <?= $video['worship'] == 'etc' ? 'selected' : '' ?>>직접입력</option>
                                </select>
                            </div>
                            <div class="col-sm-7">
                                <input type="date" class="form-control" name="title" id="title" value="<?= htmlspecialchars($video['title']) ?>">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">링크</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" name="link" id="link" value="<?= 'https://youtu.be/' . htmlspecialchars($video['link']) ?>">
                            </div>
                        </div>
                        <div class="gap-2 d-flex justify-content-end">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소</button>
                            <button type="submit" class="btn btn-primary">저장</button>
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
            window.location.replace('/admin/del_video_list?idx=' + $(this).attr('data-idx'));
        }
    });
</script>
