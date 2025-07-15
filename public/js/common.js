$(document).ready(function(){
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