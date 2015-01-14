$(document).ready(function(){

    $("#preloader" ).fadeTo( 2000 , 0, function() {
        $("#preloader" ).hide();
        $( "#header" ).animate({ opacity: 1}, 300,function() {
            $( ".header-nav" ).animate({ opacity:1}, 1000);
            $( "#banner-fade" ).animate({ opacity:1}, 1000);
            $( ".previews-wrapper" ).animate({ opacity:1}, 1000);
            $( "#news" ).animate({ opacity:1}, 1000);
            $( ".editions-wrap" ).animate({ opacity:1}, 1000);
            $( ".str_wrap" ).animate({opacity:1}, 1000);
            $( ".meta-box" ).animate({opacity:1}, 1400);
            $( ".facebook-social" ).animate({opacity:1}, 1400);
            $( ".collection-item" ).animate({ opacity:1}, 1000);
        });
        $( "#content" ).animate({ opacity: 1}, 1500);
        $( "#footer" ).animate({ opacity: 1}, 1500);
    });

   
});
