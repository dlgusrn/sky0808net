<?php
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
            <h4 class="main-title">생방송 접속 이력</h4>
            <table class="table table-hover text-center">
                <thead>
                    <tr>
                        <th>번호</th>
                        <th>구분</th>
                        <th>이름</th>
                        <th>로그인 시간</th>
                        <th>로그아웃 시간</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $row): ?>
                    <tr>
                        <td width="100"><?= $num - ($page_set * ($page - 1)) - $subt ?></td>
                        <td width="100"><?= $row['branch'] == 'ADMIN' ? '관리자' : '일반' ?></td>
                        <td width="200"><?= htmlspecialchars($row['name']) ?></td>
                        <td width="200"><?= $row['login_date'] ?></td>
                        <td width="200"><?= !empty($row['logout_date']) ? $row['logout_date'] : '-' ?></td>
                    </tr>
                    <?php if ($subt < $page_set) $subt++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php
            include_once __DIR__ . '/../../helpers/pagination_helper.php';
            echo render_pagination($page, $total_page, $block_start, $block_end, $total);
            ?>
        </div>
    </div>
</main>
