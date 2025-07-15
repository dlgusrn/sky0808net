<?php include_once __DIR__ . '/../layout/header.php'; ?>
<nav class="top-menu">
    <div class="username-span">
        <div class="accessor">
            <img src="/img/member_icon.png">
            <span><?= $_SESSION['user_name'] ?? '비회원' ?>님</span>
        </div>
    </div>
</nav>

<main class="live-index">
    <div class="church-select">
        <div class="index-title">실시간 방송</div>
        <div class="div-live-index">
            <!-- 하늘문 -->
            <div class="worship" id="sky" style="background-color: #EEF7FF;">
                <div class="text-center">
                    <div class="text-end live-status">
                        <?php if ($sky_worship > 0): ?>
                            <img src="/img/live_status_on.png">
                        <?php else: ?>
                            <img src="/img/live_status_off.png">
                        <?php endif; ?>
                    </div>
                    <div class="church-name"><span>하늘문 교회</span></div>
                    <div style="display: flex; justify-content: space-between;">
                        <div class="church-sentence" style="color: #849AAC;">
                            <span>하늘문 교회 예배를<br>실시간으로 방송합니다.</span>
                        </div>
                        <div class="text-end church-icon">
                            <img src="/img/church_sky.png">
                        </div>
                    </div>
                </div>
            </div>
            <!-- 브엘성회 -->
            <div class="worship" id="beer" style="background-color: #FFF6EE;">
                <div class="text-center">
                    <div class="text-end live-status">
                        <?php if ($beer_worship > 0): ?>
                            <img src="/img/live_status_on.png">
                        <?php else: ?>
                            <img src="/img/live_status_off.png">
                        <?php endif; ?>
                    </div>
                    <div class="church-name"><span>브엘성회</span></div>
                    <div style="display: flex; justify-content: space-between;">
                        <div class="church-sentence" style="color: #A6A09D;">
                            <span>브엘성회 예배를<br>실시간으로 방송합니다.</span>
                        </div>
                        <div class="text-end church-icon">
                            <img src="/img/church_beer.png">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php if (!empty($_SESSION['user_name'])): ?>
<div class="text-center mt-5">
    <a class="btn btn-sm btn-danger" href="/user/logout_proc">테스트 로그아웃</a>
</div>
<?php endif; ?>

<script>
    $('.worship').click(function(){
        const church = $(this).attr('id');
        $.ajax({
            type: 'POST',
            url: '/live/access',
            data: { church },
            success: function(res) {
                location.href = '/live/worship_view?church=' + church;
            }
        });
    });
</script>