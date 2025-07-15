<?php include_once __DIR__ . '/../layout/header.php'; ?>

<main class="live-index">
    <div class="login-form">
        <div class="text-center login-top">
            <div class="login-top-logo"><img src="/img/login_logo.png"></div>
            <div class="live-text"><img src="/img/live_top_text.png"></div>
        </div>
        <div class="form-signin">
            <form method="post" action="/user/login_proc">
                <div class="">
                    <input type="text" class="form-control" name="user_name" id="user_name" placeholder="이름을 입력해주세요">
                </div>
                <div class="">
                    <input type="password" class="form-control" name="live_password" id="live_password" placeholder="비밀번호를 입력해주세요">
                </div>
                <div class="checkbox">
                    <input type="checkbox" id="name_save" checked>
                    <label for="name_save"> <span>이름 저장</span></label>
                </div>
                <div class="login-btn">
                    <button type="submit" class="btn btn-primary">로그인</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php // include_once __DIR__ . '/../layout/footer.php'; ?>

<script>
    // 저장된 쿠키값을 가져와서 ID 칸에 넣어준다. 없으면 공백으로 들어감.
    var key = getCookie('key');
    $('#user_name').val(key); 
     
    if($('#user_name').val() != ''){ // 그 전에 ID를 저장해서 처음 페이지 로딩 시, 입력 칸에 저장된 ID가 표시된 상태라면,
        $('#name_save').attr('checked', true); // ID 저장하기를 체크 상태로 두기.
        $('#live_password').focus();
    } else {
        $('#user_name').focus();
    }
     
    $('#name_save').change(function(){ // 체크박스에 변화가 있다면,
        if($('#name_save').is(':checked')){ // ID 저장하기 체크했을 때,
            setCookie('key', $('#user_name').val(), 7); // 7일 동안 쿠키 보관
        }else{ // ID 저장하기 체크 해제 시,
            deleteCookie('key');
        }
    });
     
    // ID 저장하기를 체크한 상태에서 ID를 입력하는 경우, 이럴 때도 쿠키 저장.
    $('#user_name').keyup(function(){ // ID 입력 칸에 ID를 입력할 때,
        if($('#name_save').is(':checked')){ // ID 저장하기를 체크한 상태라면,
            setCookie('key', $('#user_name').val(), 7); // 7일 동안 쿠키 보관
        }
    });
 
    function setCookie(cookieName, value, exdays){
        var exdate = new Date();
        exdate.setDate(exdate.getDate() + exdays);
        var cookieValue = escape(value) + ((exdays==null) ? "" : "; expires=" + exdate.toGMTString());
        document.cookie = cookieName + "=" + cookieValue;
    }
    
    function deleteCookie(cookieName){
        var expireDate = new Date();
        expireDate.setDate(expireDate.getDate() - 1);
        document.cookie = cookieName + "= " + "; expires=" + expireDate.toGMTString();
    }
    
    function getCookie(cookieName) {
        cookieName = cookieName + '=';
        var cookieData = document.cookie;
        var start = cookieData.indexOf(cookieName);
        var cookieValue = '';
        if(start != -1){
            start += cookieName.length;
            var end = cookieData.indexOf(';', start);
            if(end == -1)end = cookieData.length;
            cookieValue = cookieData.substring(start, end);
        }
        return unescape(cookieValue);
    }
</script>