$(document).ready(function(){
    if (document.body.classList.contains('admin-ui')) {
        var route = location.pathname.split('/')[2] || 'main';
        var group = /^(video|event)/.test(route) ? 'video' : /^(admin_|preacher_|saint_)/.test(route) ? 'member' : 'live';
        ['live', 'video', 'member'].forEach(function (name) {
            var top = $('.' + name + '-nav-top');
            var opened = name === group;
            top.toggleClass('active', opened).attr('aria-expanded', String(opened));
            $('.' + name + '-nav-bottom .nav-side').toggle(opened);
            top.on('click', function (event) {
                event.preventDefault();
                var expanded = $(this).attr('aria-expanded') !== 'true';
                top.toggleClass('active', expanded).attr('aria-expanded', String(expanded));
                $('.' + name + '-nav-bottom .nav-side').stop(true, true).toggle(expanded);
            });
        });
        $('.mobile-nav').on('click', function () {
            var expanded = $(this).attr('aria-expanded') !== 'true';
            $(this).attr('aria-expanded', String(expanded));
            $('.div-mobile-nav').stop(true, true).toggle(expanded);
        });
        return;
    }

    // 좌측 메뉴 보이기
    $('.live-nav-top').click(function(){
        $('.live-nav-bottom .nav-side').slideToggle(300);

        if(true == $(this).hasClass('active')){
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });
    $('.video-nav-top').click(function(){
        $('.video-nav-bottom .nav-side').slideToggle(300);

        if(true == $(this).hasClass('active')){
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });
    $('.member-nav-top').click(function(){
        $('.member-nav-bottom .nav-side').slideToggle(300);

        if(true == $(this).hasClass('active')){
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });

    var url = $(location).attr('pathname');
    var path = url.split('/');

    if('' != path[2] || 'undefined' != path[2]){
        switch(path[2]){
            case '' :
            case 'main' :
            case 'history' :
            case 'live_list' :
            case 'live_read' :
            case 'black_list' :
                $('.live-nav-top').addClass('active');
            case 'video_list':
            case 'video_read':
            case 'event_list':
                $('.video-nav-top').addClass('active');
            case 'admin_list':
            case 'admin_read':
            case 'preacher_list':
            case 'preacher_read':
            case 'saint_list':
            case 'saint_read':
                $('.member-nav-top').addClass('active');
        }
    }

    if(true == $('.live-nav-top').hasClass('active')){
        $('.live-nav-bottom .nav-side').css('display', 'block');
        $('.video-nav-bottom .nav-side').css('display', 'none');
        $('.member-nav-bottom .nav-side').css('display', 'none');
    } else if(true == $('.video-nav-top').hasClass('active')){
        $('.live-nav-bottom .nav-side').css('display', 'none');
        $('.video-nav-bottom .nav-side').css('display', 'block');
        $('.member-nav-bottom .nav-side').css('display', 'none');
    } else if(true == $('.member-nav-top').hasClass('active')){
        $('.live-nav-bottom .nav-side').css('display', 'none');
        $('.video-nav-bottom .nav-side').css('display', 'none');
        $('.member-nav-bottom .nav-side').css('display', 'block');
    } else {
        $('.nav-side').css('display', 'none');
    }

   $('.mobile-nav').click(function(){
    $('.div-mobile-nav').slideToggle(300);
   });
});