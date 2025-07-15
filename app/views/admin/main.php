<?php include_once __DIR__ . '/../layout/header.php'; ?>
<main class="live-main">
    <div class="main-row">
        <div class="nav-left">
            <?php include_once __DIR__ . '/../layout/admin_nav.php'; ?>
        </div>
        <div class="nav-right">
            <h4 class="main-title">생방송 보기</h4>
            <div class="container-fluid admin-row">
                <!-- 하늘문 예배 영상 -->
                <div class="row justify-content-center" id="div_live">
                    <h3>하늘문예배</h3>
                    <?php if (($sky_worship && $sky_worship['view'] == 'Y')): ?>
                        <div class="live col-8">
                            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($sky_worship['link']) ?>" style="border-radius: 15px;" frameborder="0" allowfullscreen></iframe>
                        </div>
                        <div class="div-live-list col-2">
                            <div class="sky-live-list bg-light" style="overflow-y: scroll;"></div>
                        </div>
                    <?php else: ?>
                        <div class="notify">
                            <div class="text-center">
                                <?php if ($_SESSION['admin_user_name'] == '정옥용'): ?>
                                    지금은<br>예배시간이 아닙니다:)
                                <?php else: ?>
                                    현재 송출중인 방송이 없습니다.<br>방송 등록 또는 송출해주세요 :)
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="center-line"></div>

                <!-- 브엘성회 예배 영상 -->
                <div class="row justify-content-center" id="div_live" style="margin-bottom: 100px;">
                    <h3>브엘성회</h3>
                    <?php if (($beer_worship && $beer_worship['view'] == 'Y')): ?>
                        <div class="live col-8">
                            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($beer_worship['link']) ?>" style="border-radius: 15px;" frameborder="0" allowfullscreen></iframe>
                        </div>
                        <div class="div-live-list col-2">
                            <div class="beer-live-list bg-light" style="overflow-y: scroll;"></div>
                        </div>
                    <?php else: ?>
                        <div class="notify">
                            <div class="text-center">
                                <?php if ($_SESSION['admin_user_name'] == '정옥용'): ?>
                                    지금은<br>예배시간이 아닙니다:)
                                <?php else: ?>
                                    현재 송출중인 방송이 없습니다.<br>방송 등록 또는 송출해주세요 :)
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // 생방송 접속자 실시간 불러오기
    setInterval(function() {
        $.ajax({
            type: 'GET',
            url: '/admin/get_sky_live_user',
            success: function(data) {
                $('.sky-live-list').empty();
                $.each(data, function(i){
                    var accessor_branch = data[i].branch == 'ADMIN' ? data[i].name != '정옥용' ? '(관리자)' : '' : '';
                    $('.sky-live-list').append(data[i].name + accessor_branch + '<br>');
                });
            }
        });
        $.ajax({
            type: 'GET',
            url: '/admin/get_beer_live_user',
            success: function(data) {
                $('.beer-live-list').empty();
                $.each(data, function(i){
                    var accessor_branch = data[i].branch == 'ADMIN' ? data[i].name != '정옥용' ? '(관리자)' : '' : '';
                    $('.beer-live-list').append(data[i].name + accessor_branch + '<br>');
                });
            }
        });
    }, 1000);
</script>
