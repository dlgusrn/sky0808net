<?php include_once __DIR__ . '/../layout/header.php'; ?>
<style type="text/css">
    input[type=text] {
        -webkit-ime-mode:active;
        -moz-ime-mode:active;
        -ms-ime-mode:active;
        ime-mode:active;
    }
</style>

<main class="live-main">
    <div class="admin-login-form">
        <div class="text-center login-top">
            <img src="/img/login_logo.png">
            <div><span>관리자 로그인</span></div>
        </div>
        <div class="form-signin">
            <form method="post" action="/admin/admin_login_proc">
                <div class="">
                    <input type="text" class="form-control" name="admin_name" id="admin_name" style="ime-mode: active;" placeholder="관리자 이름">
                </div>
                <div class="">
                    <input type="password" class="form-control" name="admin_password" id="admin_password" placeholder="관리자 비밀번호">
                </div>
                <div class="checkbox">
                    <input type="checkbox" id="namesave" checked>
                    <label for="namesave"> 이름 저장</label>
                </div>
                <div class="login-btn">
                    <button type="submit" class="btn btn-primary">로그인</button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
    // 저장된 쿠키값을 가져와서 ID 칸에 넣어준다. 없으면 공백으로 들어감.
    var key = getCookie('key');
    $('#admin_name').val(key); 
     
    if($('#admin_name').val() != ''){ // 그 전에 ID를 저장해서 처음 페이지 로딩 시, 입력 칸에 저장된 ID가 표시된 상태라면,
        $('#namesave').attr('checked', true); // ID 저장하기를 체크 상태로 두기.
        $('#admin_password').focus();
    } else {
        $('#admin_name').focus();
    }
     
    $('#namesave').change(function(){ // 체크박스에 변화가 있다면,
        if($('#namesave').is(':checked')){ // ID 저장하기 체크했을 때,
            setCookie('key', $('#admin_name').val(), 7); // 7일 동안 쿠키 보관
        }else{ // ID 저장하기 체크 해제 시,
            deleteCookie('key');
        }
    });
     
    // ID 저장하기를 체크한 상태에서 ID를 입력하는 경우, 이럴 때도 쿠키 저장.
    $('#admin_name').keyup(function(){ // ID 입력 칸에 ID를 입력할 때,
        if($('#namesave').is(':checked')){ // ID 저장하기를 체크한 상태라면,
            setCookie('key', $('#admin_name').val(), 7); // 7일 동안 쿠키 보관
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