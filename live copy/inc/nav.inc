<nav class="navbar navbar-expand-md fixed-top navbar-light bg-light" aria-label="Fourth navbar example">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?=SITE_LIVE?>admin/list.html" alt="하늘문교회기도원"><img src="<?=SITE_LIVE?>img/main_logo.png"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarsExample04" style="margin-right: 40px;">
            <div>
                <ul class="navbar-nav me-auto mb-2 gap-2 mgt10">
                    <li><a class="dropdown-item" href="<?=SITE_LIVE?>index.html">생방송 보기</a></li>
                    <li><a class="dropdown-item" href="<?=SITE_LIVE?>admin/<?=$fileName == 'black_list.html' ? 'list' : 'black_list'?>.html"><?=$fileName == 'black_list.html' ? '생방송 링크' : '블랙리스트'?> 관리</a></li>
                    <li><a class="dropdown-item" href="<?=SITE_LIVE?>admin/<?=$fileName == 'history.html' ? 'list' : 'history'?>.html">생방송 <?=$fileName == 'history.html' ? '링크 관리' : '접속 이력'?></a></li>
                    <li><a class="dropdown-item" href="<?=SITE_LIVE?>logout.php?name=<?=$_SESSION['username']?>&branch=<?=$_SESSION['branch']?>">로그아웃</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>