<!-- views/live/index.php -->
<?php include_once __DIR__ . '/../layout/header.php'; ?>
<nav class="top-menu">
    <div class="back_button" style="position: absolute;">
        <button id="back_btn" style="margin-left: 8.333vw; padding: 0; background-color: #FFFFFF; border: none;">
            <img src="/img/back_button.png">
        </button>
    </div>
    <div class="username-span">
        <?php if (isset($_SESSION['username'])): ?>
            <?php if ($_SESSION['branch'] == 'ADMIN'): ?>
                <div class="admin-accessor">
                    <button class="btn dropdown-toggle text-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- 아이콘 생략 -->
                        <span><?= htmlspecialchars($_SESSION['username']) ?>님</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin/live_list">생방송 링크 관리</a></li>
                        <li><a class="dropdown-item" href="/member/logout?name=<?= urlencode($_SESSION['username']) ?>&branch=<?= urlencode($_SESSION['branch']) ?>">로그아웃</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="accessor">
                    <img src="/img/member_icon.png">
                    <span><?= htmlspecialchars($_SESSION['username']) ?>님</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</nav>

<main class="live-index">
    <div class="church-select">
        <div class="worship-title"><?= $church == 'sky' ? '하늘문 교회' : '브엘성회' ?></div>
        <div class="div-live">
            <?php if ($worship && $worship['view'] == 'Y'): ?>
                <div class="text-center live">
                    <div class="text-center">
                        <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($worship['link']) ?>?rel=0" style="border-radius: 15px;" frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
            <?php else: ?>
                <div class="notify">
                    <div class="text-center">
                        지금은<br>예배시간이 아닙니다:)
                    </div>
                </div>
            <?php endif; ?>
            <div class="center-line"></div>
            <div class="worship-notify">
                <?php if ($church == 'sky'): ?>
                    <img class="sky" src="/img/worship_time_sky.png">
                <?php else: ?>
                    <img class="beer" src="/img/worship_time_beer.png">
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    // 생방송 상태 실시간 변경
    var use = "<?= isset($worship['use']) ? $worship['use'] : '' ?>";
    var view = "<?= isset($worship['view']) ? $worship['view'] : '' ?>";
    var link = "<?= isset($worship['link']) ? $worship['link'] : '' ?>";

    setInterval(function() {
        $.ajax({
            type: 'GET',
            data: {link:link},
            url: '/live/check_live_list',
            success: function(data) {
                var live_data = data;
                if (typeof live_data !== 'object') {
                    try { live_data = JSON.parse(live_data); } catch(e){}
                }
                if(live_data.view !== view || live_data.use !== use){
                    location.reload();
                }
                view = live_data.view;
                use = live_data.use;
            }
        });
    }, 1000);

    $('#back_btn').click(function(){
        history.back();
    });
</script>
