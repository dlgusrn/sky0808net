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

    if('' != path[3] || 'undefined' != path[3]){
        switch(path[3]){
            case '' :
            case 'index.html' :
            case 'live' :
                $('.live-nav-top').addClass('active');
            
            case 'video':
                $('.video-nav-top').addClass('active');
            case 'member':
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