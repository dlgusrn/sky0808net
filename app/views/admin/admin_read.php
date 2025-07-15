<?php include_once __DIR__ . '/../layout/header.php'; ?>
<main class="live-main">
<div class="main-row">
    <div class="nav-left">
        <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
    </div>
    <div class="nav-right">
        <h4 class="main-title">관리자 관리</h4>
        <form method="post" action="/admin/set_admin?idx=<?= $admin['idx'] ?>">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-3">
                        <label class="col-form-label">교회</label>
                        <select class="form-select" name="church">
                            <option value="sky" <?= $admin['church'] == 'sky' ? 'selected' : '' ?>>하늘문</option>
                            <option value="beer" <?= $admin['church'] == 'beer' ? 'selected' : '' ?>>브엘성회</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <label class="col-form-label">등급</label>
                        <select class="form-select" name="level">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <option value="<?= $i ?>" <?= $admin['level'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label class="col-form-label">이름</label>
                        <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($admin['username']) ?>" required>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col">
                        <label class="col-form-label">새 비밀번호</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <div class="col">
                        <label class="col-form-label">비밀번호 확인</label>
                        <input type="password" class="form-control" name="password_check">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="mb-2">권한</div>
                    <div class="row">
                        <?php
                        $permissions = [
                            'live_manage'  => '생방송 보기',
                            'black_list'   => '블랙리스트 관리',
                            'live_list'    => '생방송 링크 관리',
                            'video_list'   => '연도별',
                            'history'      => '생방송 접속 이력 관리',
                            'event_video'  => '행사',
                        ];
                        ?>
                        <?php foreach(array_chunk($permissions, 2, true) as $chunk): ?>
                            <div class="col">
                                <?php foreach($chunk as $key => $label): ?>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="<?= $key ?>" <?= $admin[$key] == 'Y' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="<?= $key ?>"><?= $label ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">저장</button>
                    <a href="/admin/member_admin_list" class="btn btn-danger">취소</a>
                </div>
            </div>
        </form>
    </div>
</div>
</main>

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
</script>
