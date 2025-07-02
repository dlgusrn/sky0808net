<!-- 메인 메뉴 -->
<nav class="top-menu navbar fixed-top navbar-expand-lg navbar-light" aria-label="Ninth navbar example">
  <div class="container-xl mgt20">
    <a href="http://sky08.net/"><img src="/img/main_logo.png" alt="하늘문교회기도원"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample07XL" aria-controls="navbarsExample07XL" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample07XL">
      <ul class="navDropdown navbar-nav col-12 col-md-auto mb-2 mb-md-0">
        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="guideDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            교회소개
          </a>
          <ul class="dropdownMenu dropdown-menu" aria-labelledby="guideDropdown">
            <li><a class="dropdown-item" href="#">인사말</a></li>
            <li><a class="dropdown-item" href="#">시설물</a></li>
            <li><a class="dropdown-item" href="#">연혁</a></li>
            <li><a class="dropdown-item" href="#">오시는길</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="worshipDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            예배안내
          </a>
          <ul class="dropdownMenu dropdown-menu" aria-labelledby="worshipDropdown">
            <li><a class="dropdown-item" href="#">예배시간</a></li>
            <li><a class="dropdown-item" href="#">운행시간</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="introduceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            말씀
          </a>
          <ul class="dropdownMenu dropdown-menu" aria-labelledby="introduceDropdown">
            <li><a class="dropdown-item" href="#">2021년 이후 말씀</a></li>
            <li><a class="dropdown-item" href="#">2020년 이전 말씀</a></li>
          </ul>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link" href="#" id="noticeDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            교회소식
          </a>
          <ul class="dropdownMenu dropdown-menu" aria-labelledby="noticeDropdown">
            <li><a class="dropdown-item" href="#">공지사항</a></li>
            <li><a class="dropdown-item" href="#">특별성회</a></li>
          </ul>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li><a class="dropdown-item" href="/index.html" target="_blank">실시간 방송</a></li>
      </ul>
    </div>
      
    <!-- 회원 관리 메뉴 -->
    <div class="collapse navbar-collapse justify-content-md-end" id="navbarsExample07XL">
      <? if ( empty ( $_SESSION ['memberIdx'] ) ) : ?>
      <button type="button" class="btn btn-outline-primary me-2">로그인</button>
      <button type="button" class="btn btn-primary">회원가입</button>
      <? else : ?>
      <button type="button" class="btn btn-danger me-2">로그아웃</button>
      <button type="button" class="btn btn-outline-primary">회원정보</button>
      <? endif ; ?>
    </div>
  </div>
</nav>