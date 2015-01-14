$(function() {
    window.UI = (function() {

        function UI() {
            this.init();

            var ytplayer;
            
        }

        UI.prototype.init = function() {

            this.setupListners();

            jQuery(".timeago").timeago();

            if(!$.cookie('popover-free')){
                $('#popover-free').show();
            }

            if(!$.cookie('popover-burger')){
                $('#popover-burger').show();
            }

             
            if(API.page_name == 'welcome'){
                mixpanel.track("SignupTry", {"Source": 'Welcome Page'});
            }

            if(API.page_name == 'wizard' && API.subpage_name == 'step1'){
                mixpanel.track("Signup");
            }
            
            if( API.controller_name == 'feed' 
                || API.controller_name == 'user' 
                || API.controller_name == 'search'
                || API.controller_name == 'wizard'
                || (API.controller_name == 'tag' && API.subpage_name != undefined)
                || (API.controller_name == 'store' && API.subsubpage_name != undefined)
                //|| API.controller_name == 'find' 
                ){

                API.page_number  = 0;
                API.itemsGet();
            }

           if (device.desktop()) $('body').addClass('desktop-device');

           /* if(API.controller_name == 'shop' && (API.page_name == '' || API.page_name == 'shop')){
                $('#shop-main-promos').bjqs({
                    'height' : 600,
                    'width' : '100%',
                    'animtype' : 'fade',
                    'automatic' : true,
                    'showcontrols' : false,
                    'animspeed' : 4000,
                    'showmarkers' : true, // Show individual slide markers
                    'centermarkers' : true,
                    'responsive' : true
                });
            }*/


            API.filter_names.forEach(function(filter_name) {
                if(API.getUrlParameter(filter_name) ){
                    API.filters[filter_name] = API.getUrlParameter(filter_name).split(',');  
                }else{
                    API.filters[filter_name] = [];
                }       
            });

            if(typeof chrome != 'undefined'){
                if (chrome.app.isInstalled) {
                    $('#start').hide();
                }
            }
        }



        $(window).scroll(function(){
            if( $(window).scrollTop() >= 50 && !$(".unauth-banner").hasClass('fixed') ){
                $('#header').addClass('fixed');
            }

            if ($(window).scrollTop() < 49 ){
                $('#header').removeClass('fixed');
            }

            if (device.desktop()) {
                if ($(window).scrollTop() >= 3000 && !$(".unauth-banner").is(':visible') && API.islogin == 0){
                    $('.unauth-banner').slideDown();

                    //mixpanel.track("SignupTry", {"Source": 'Footer Invite'});
                }

                if ($(window).scrollTop() < 1 ){
                    $('.unauth-banner').hide();
                }
            }

            // console.log($(window).scrollTop());

            if ($(window).scrollTop() + $(window).height() >= 
                $(document).height() - 800) { 
                
                if(!API.notoload){
                    if(!API.isloading){
                        //API.controller_name == 'shop' ||  || API.controller_name == 'tag'
                        if( API.controller_name == 'feed' 
                            || API.controller_name == 'user'
                            || API.controller_name == 'trending' 
                            || API.controller_name == 'wizard'
                            || API.controller_name == 'search'
                            || API.controller_name == 'sets' 
                            || API.controller_name == 'stores'
                            || API.controller_name == 'tags'
                            || API.controller_name == 'trendsetters'
                            || (API.controller_name == 'tag' && API.subpage_name != undefined)
                            || (API.controller_name == 'store' && API.subsubpage_name != undefined)
                            //|| API.controller_name == 'find'
                            ){
                            API.itemsGet();
                        }
                    }
                }
            } 
        });


        UI.prototype.setupListners = function() {
            var self = this;


            $('a[href*=#]:not([href=#])').click(function() {
                if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
                  var target = $(this.hash);
                  target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
                  if (target.length) {
                    $('html,body').animate({
                      scrollTop: target.offset().top - 90
                    }, 500);
                    return false;
                  }
                }
              });

            $(this).keydown(function (eventObject) {
                if (eventObject.which == 27) {
                    if(product_preview_status == 1){
                        //$('#btn-preview-close').trigger('click');
                    }   
                    //popup_close();
                }
            });

            $(document).ready(function() {
                console.log(!~location.pathname.indexOf('/p'));
                if (!~location.pathname.indexOf('/p')) {
                    $('#product-preview').hide();
                    $(document.body).css('overflow', "auto");
                    product_preview_status = 0;
                }
            });

            $('.btn-load-more')
                .on('click', function() {
                    API.itemsGet();

                });





            $('#settings')
                .on('click', '.btn-save', function(event) {
                    event.preventDefault();
                    var data = $('.edit-form').serialize();
                    return API.userSet(data);
                });

            $('#products, #product-preview')
                .on('click', '.item-product .image', function(event) {

                    var product_id = $(this).closest('.item-product').data('id');

                    if(API.islogin){
                        if (device.desktop()) {
                            event.preventDefault();
                            return API.productGet(product_id);
                        }
                    }else{  
                        event.preventDefault();  
                        UI.purchase( $(this) );

                        window.open(API.site_root + '/purchase/' + product_id); return false;
                    }

                })
                .on('click', '.item-product .controls .btn-save', function() {
                    var product_id = $(this).closest('.item-product').data('id');

                    if(API.islogin){
                        API.productSave(product_id);    
                    }else{
                        return UI.popupShow('signup');
                    }
                    
                })
                .on('click', '.item-product .controls .btn-free', function() {
                        var price = $(this).data('price');
                        var num = Math.ceil(price / 300);

                        mixpanel.track("GetFree Popup");

                        return UI.popupShow('free', num);
                    
                })
                .on('click', '.item-product .controls .btn-send', function() {
                        mixpanel.track("Send Popup");

                        return UI.popupShow('send');                  
                });  


            $('#product-preview, #product')
                .on('click', '.product-image .purchase-link, .product-sidebar .btn-buy, .info-block ul li .purchase-link', function() {
                    UI.purchase( $(this) );

                })
                .on('click', '.product-sidebar .btn-save', function() {
                    var product_id = $(this).data('id');
                    if($(this).parent('.product-controls').data('save-status') == 1){
                        var action = 'delete';
                    }else{
                        var action = 'add';
                    }

                    if(API.islogin){
                        API.productSave(product_id, action);    
                    }else{
                        return UI.popupShow('signup');
                    }
                    
                })
                .on('click', '.product-sidebar .btn-free', function() {
                        var price = $(this).data('price');
                        var num = Math.ceil(price / 300);

                        return UI.popupShow('free', num);
                    
                })
                .on('click', '.product-sidebar .btn-send', function() {
                        mixpanel.track("Send Popup");

                        return UI.popupShow('send');
                    
                })
                .on('submit', '.product-comments form', function(event){
                    event.preventDefault();
                    if(API.islogin){
                        var data = $(this).serialize();
                        API.commentAdd(data);    
                    }else{
                        return UI.popupShow('signup');
                    } 
                    

                    
                });

                

            $('#trending-section-switcher').on('click', 'button', function(){
        
                 return UI.switchTrendingSection($(this).data('value'));

            });


            $('#popup-signup').on('click', '.to-login', function(event) {
                $('#popup-signup').hide();
                // $('#popup-signin').show();
                UI.popupShow('signin');
            });

            $('#popup-signin').on('click', '.to-reg', function(event) {
                // $('#popup-signup').show();
                $('#popup-signin').hide();
                UI.popupShow('signup');
            });

            $('#popup-free').on('click', '.btn', function(event) {
                mixpanel.track("GetFree Start");
            });

            $('#signup-form, #popup-signup-form')
                .on('keydown change keyup', '.input-username', function(){
                    var username_pattern =  /^[a-zA-Z0-9]/; 
                    var username_test = username_pattern.test($(this).val());

                    if (username_test==false) {
                        var str = $(this).val();
                        str = str.substring(0, str.length - 1);
                        $(this).val(str);
                    }

                    $('.username-tip').text($(this).val());

                })
                .on('submit', function(event){
                    event.preventDefault();
                    var data = $( this ).serialize();
                    var error = $( this ).find(' .error' );
                    
                    error.hide();
                    mixpanel.track("SignupStart", {"Provider": "mail"});

                    if($( this ).find('.input-name').val()==''){
                        error.text('Укажите имя');
                        error.show();
                        return;
                    }

                    if($( this ).find('.input-username').val()==''){
                        error.text('Укажите имя пользователя');
                        error.show();
                        return;
                    }

                    if($( this ).find('.input-email').val()==''){
                        error.text('Укажите электронную почту');
                        error.show();
                        return;
                    }

                    if($( this ).find('.input-password').val()==''){
                        error.text('Укажите пароль');
                        error.show();
                        return;
                    }

                    var email_pattern =  /^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/; 
                    var email_test = email_pattern.test($( this ).find('.input-email').val());

                    if (email_test==false) {
                        error.text('Неверный формат электронной почты');
                        error.show();
                        return;
                    }

                    mixpanel.track("SignupSubmit");
                    API.signup(data, this);
                });

            $('#signin-form, #popup-signin-form').on('submit', function(event){
                    event.preventDefault();
                    var data = $( this ).serialize();
                    var error = $( this ).find(' .error' );
                    
                    error.hide();                

                    if($( this ).find('.input-email').val()==''){
                        error.text('Укажите электронную почту');
                        error.show();
                        return;
                    }

                    if($( this ).find('.input-password').val()==''){
                        error.text('Укажите пароль');
                        error.show();
                        return;
                    }

                    var email_pattern =  /^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/; 
                    var email_test = email_pattern.test($( this ).find('.input-email').val());

                    if (email_test==false) {
                        error.text('Неверный формат электронной почты');
                        error.show();
                        return;
                    }

                    API.signin(data, this);
                });
                
            $('#header-search-form').on('submit', function(){
                if($('#header-search-input').val().length < 2){
                    event.preventDefault();
                }
                
            });


            $('#product-preview')
                 .on('click', '.btn-close, .cover-close', function() {
                    return self.productPreviewClose();
                });


            $('body')
                .on('click', '.btn-follow', function() {

                    var item_id = $(this).data('id');
                    var type = $(this).data('type');
                  

                    if($(this).data('follow-status') == 1){
                        var action = 'unfollow';
                    }else{
                        var action = 'follow';
                    }

                    if(API.islogin){
                        API.follow(type, item_id, action);    
                    }else{
                        return UI.popupShow('signup');
                    } 

                })
                .on('click', '.btn-share', function() {

            
                   var url = encodeURIComponent( $(this).parent('div').data('url') );

                   UI.share($(this).data(), url);

                })
                .on('click', '.btn-signup', function() {

                  var provider = $(this).data('provider');
                  mixpanel.track("SignupStart", {"Provider": provider});


                });
     

            var wizard_count_c = 0;

            $('#wizard').on('mouseup', '.btn-follow', function() {
                wizard_count_c++;
 
                if(wizard_count_c==3){
                    $('.wizard-count').hide();
                    if (!device.desktop()) {
                        $('.btn-next').text('Готово')
                    }
                    $('.btn-next').show();     
                }else{
                    var wcc = 'wizard-count' + wizard_count_c;
                    $('.wizard-count').addClass(wcc);   
                }
            }); 



            $('.popup .cover').click(function(){
                var isFreePopupCover = !!$(this).parent().filter('#popup-free').length;
                return UI.popupClose(isFreePopupCover);
            });

            $(".a-signin").on('click', function() {
                return UI.popupShow('signin');
            });

            $(body).on('click', ".a-signup, .a-feed", function() {
                return UI.popupShow('signup');
            });

            $(".a-video").on('click', function() {
                return UI.popupShow('video');
            });

        
            $('.btn-step1complete').on('click', function(){
               mixpanel.track("SignupWizardStep1"); 
            });

            $('.btn-step2complete').on('click', function(){
               mixpanel.track("SignupWizardStep2"); 
            });


            $('.page-bar-filters')
                .on('click', 'input[type=checkbox]', function(){
                    var type = $(this).data('type');
                    var item_id = $(this).val();
                    

                    if($(this).is(':checked')){
                        API.filters[type].push(item_id);
                    }else{
                        API.filters[type] = jQuery.grep(API.filters[type], function( a ) {
                            return a !== item_id;
                         });
                    }

                    UI.filter();
                })
                .on('click', '.ng', function(){
                    var type = $(this).data('type');
                    var value = $(this).data('value');
                    
                    API.filters[type] = [];
                    API.filters[type].push(value);

                    //console.log(API.filters[type]);
                    UI.filter();
                })
                .on('click', '.na', function(event){ 
                    //event.preventDefault();
                    //$(this).closest('input[type=checkbox]').prop('checked', true);       
                })
                .on('click', '.btn-filter', function(e){ 
                    $('.filter .list').hide();
                    $(this).parent('.filter').children('.list').toggle();
                    e.stopPropagation(); 
                    return false;  
                })
                .on('click', '.list', function(e){ 
                    e.stopPropagation(); 
                    //return false;  
                })
                .on('click', '.result-item', function(e){ 
                    var type = $(this).data('type');
                    var item_id = $(this).data('id').toString();; 

                    API.filters[type] = jQuery.grep(API.filters[type], function( a ) {
                        return a !== item_id;
                    });

                    UI.filter();
                      
                });   

            $(document).click(function() {
                $('.filter .list').hide();
            });


            $('.btn-add').on('click', function(){
                return UI.popupShow('button');
            });

            $('.btn-chrome-extension').on('click', function(){
                chrome.webstore.install('',function(){ UI.popupClose(); $('#start').hide(); });
            });



           
            //     .on('click', 'nav .side-nav-shop li .sn-section', function(event){
            //         event.preventDefault();
            //         $(this).parent('li').children('.sn-categories').toggle();
            //     })
            //    .on('click', 'nav .side-nav-shop li .sn-categories li .sn-category', function(event){
            //         event.preventDefault();
            //         $(this).parent('li').children('.sn-types').toggle();
            //         $(this).toggleClass('active');
            //     })
             $('.sidebar-left')
               .on('click', '.cover', function(event){
                    $(document.body).css('overflow', "auto");
                    $('.sidebar-left').hide();

                });

//             $('body').on('click', '*', function(event) {
//                 if ($(this).hasClass('burger')) {
//                     if ($('body').hasClass('opened-ddlist')) {
//                         $('.l-side-nav').hide();
//                         if (!device.desktop()) {
//                             $('body').removeClass('opened-ddlist');
//                             $('.contain').css('height','auto');
//                         }
//                     }
//                     else {
//                         $('.l-side-nav').show();
//                         if (!device.desktop())  {
//                             $('body').addClass('opened-ddlist');
//                             $('.contain').height($('.l-side-nav').outerHeight(true));
//                         }
//                     }
//                     event.stopPropagation();
//                 }
//                 else if (!$(this).closest('.l-side-nav').length || ($(this).hasClass('cover') && $(this).closest('.l-side-nav').length) ) {
//                     if ($('.l-side-nav').css('display')!=='none'){
//                         $('.l-side-nav').hide();
//                         if (!device.desktop()) $('body').removeClass('opened-ddlist');
//                         event.stopPropagation();   
//                     }
//                 }
//             }); 

            $('#header .container .header')
                .on('click', '.burger', function(event) {

                    if ($(window).width() > 1024) {
                        $(document.body).css('overflow', "hidden");
                        $('.sidebar-left').show();
                        $('.sidebar-left .cover').show();
                    }else{
                        $(document.body).toggleClass('show-left');

                        $('.contain').removeAttr('style');
                        if ($(document.body).hasClass('show-left')) {
                            $('.contain').height($(window).height());
                            // if (device.desktop()) $('.sidebar-left .cover').show();
                        }
                        else {
                            $('.contain').css('min-height', '100%');
                            // if (device.desktop()) $('.sidebar-left .cover').hide();
                        }
                    } 

                    if(!$.cookie('popover-burger')){
                        $.cookie('popover-burger', true, { expires: 1000, path: '/' }); 
                        $('#popover-burger').hide();
                    }  

                    // $('.contain').height($(window).height());
                })
                .on('click', '.btn-invite, #popover-free', function(event) {
                    if(!$.cookie('popover-free')){
                        $.cookie('popover-free', true, { expires: 1000, path: '/' }); 
                    }    
                });    


            $(document).on('click', function(){
                $("#popover-notifications").hide();
            }).on('click', '#popover-notifications', function(event){
                event.stopPropagation();
            }).on('click', '.btn-notifications', function(event){
                $('.popover').hide();
                $('#popover-notifications').toggle();
                
                API.notificationsView();
                event.stopPropagation();
            });   

        }






        UI.filter = function(){
            var url_query = '?';
            var i = 0; 

            API.filter_names.forEach(function(filter_name) {
                if(API.filters[filter_name] && API.filters[filter_name].length != 0){
                    if(i > 0 ){ url_query = url_query + '&'; }
                    url_query = url_query + filter_name + '=' + API.filters[filter_name].join(",");
                    i++;
                }       
            });

            if(API.page_number > 1){
                var wlh = window.location.href.split('?')[0];
                var strtr = "/"+API.page_number;
                wlh = wlh.replace(strtr, "");

            }else{
                var wlh = window.location.href.split('?')[0];
            }

            window.location.replace( wlh + url_query);

        }

        UI.share = function(data, share_url){
            var w = 600;
            var h = 400;
           
            var title = 'Поделиться';
            var type = data['type'];

            switch(type){
                case 'vk':
                    var url = 'https://vk.com/share.php?url=' + API.site_root + '/' + share_url;
                    break;

                case 'fb':
                    var url = 'https://www.facebook.com/sharer/sharer.php?u=' + API.site_root + '/' + share_url;
                    break;

                case 'tw':
                    var text = data['text'];
                    var url = 'https://twitter.com/intent/tweet?url=' + API.site_root + '/' + share_url + '&text=' + text;
                    break;

                case 'gp':
                    var url = 'https://plus.google.com/share?url=' + API.site_root + '/' + share_url;
                    break;


                case 'pin':
                    var media = data['media'];
                    var description = data['description'];
                    var url = 'http://pinterest.com/pin/create/link/?url=' + API.site_root + '/' + share_url +
                              '&media=' + media +
                              '&description=' + description;
                    break;

                default: return false;        

            }

            var left = (screen.width/2)-(w/2);
            var top = (screen.height/2)-(h/2);

            var sw = window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);  
            
            mixpanel.track("Share", {"Type": type});
            setTimeout(function() { UI.popupClose() }, 5000);

        }


        UI.popupClose = function(hideVisible){
            $('.popup').hide();
            $('.popup .popup-content').css('top', $(window).height() + 'px' ); 

            $(document.body).css('overflow', "visible");
     
            notoload = 0;
            isloading = 0;
            product_preview_status = 0;

            //TODO: needtofix on local
            if (ytplayer) { ytplayer.stopVideo(); }

        }

        UI.popupShow = function(name, data){

            if (!device.desktop() && (name === 'signin' || name === 'signup')) {
                location.href = '/' + name;
                return;
            }

            var popup_id = '#popup-' + name;
            $(popup_id).show();

            var popup = $(popup_id + ' .popup-content');
            var top = 8;//$(window).height()/2 - popup.outerHeight(true)/2;
            if (device.desktop() || name === 'free') {
                top = $('.popup').height()/2 - popup.outerHeight(true)/2;
            }
            popup.css('top', $(window).height() + 'px' ); 
            popup.animate({top:top}, 800, "easeOutQuint");

            //$(document.body).css('overflow', "hidden");

            if (name == 'signup') {
                mixpanel.track("SignupTry", {"Source": 'Popup'});
            }

            if(name == 'button'){

                var videoId = $('#ytplayer').data('videoid');

                ytplayer = new YT.Player('ytplayer', {
                playerVars: { 'autoplay': 1, 'controls': 1, 'rel':0, 'showinfo': 0, 'loop': 1, 'autohide':1, 'wmode':'opaque' },
                    videoId: videoId,
                    height: '300',
                    width: '500',
                    //events: {'onReady': onPlayerReady}
                });

                //function onPlayerReady(event) {
                //    event.target.mute();
                //}

                mixpanel.track("Install Button Popup");
            }

            if(name == 'video'){

                var videoId = $('#ytplayer-video').data('videoid');

                ytplayer = new YT.Player('ytplayer-video', {
                playerVars: { 'autoplay': 1, 'controls': 1, 'rel':0, 'showinfo': 0, 'loop': 1, 'autohide':1, 'wmode':'opaque' },
                    videoId: videoId,
                    height: '440',
                    width: '780',
                   
                });

                mixpanel.track("Video Intro Popup");
            }

            if(name == 'free'){
                $(popup_id).find('.num').html(data);
                mixpanel.track("GetFree Popup");
            }    
        }

        UI.prototype.renderItems = function(products, template, container){
            $(template).tmpl(products).appendTo(container);
            $("time.timeago").timeago();
            //$('.btn').tipsy({gravity: 's'});


        }

        UI.prototype.productPreview = function(data){

            history.pushState(null, null, '/p/' + data['id'] + '/' + data['name_url']);

            // $('#product-preview').show();
            // document.body.style.overflow = "hidden";

            $(document.body).css('overflow', "hidden");
          
            $('#product_preview_tpl').tmpl(data).appendTo('#product-preview-container'); 

            jQuery(".timeago").timeago();
            $('#product-preview').scrollTop(0);
        }

        UI.prototype.productPreviewClose = function(){

            $('#product-preview').hide();
            $(document.body).css('overflow', "auto");
          

            history.back(1);
            product_preview_status = 0;
        }

        UI.purchase = function(product) {

            var trans_id = new Date().getTime();
            var product_id = product.data('id');
            var product_name = product.data('name');
            var product_store = product.data('store');
            var source = product.data('source');
            
            ga('ecommerce:addTransaction', {
              'id': trans_id,                     // Transaction ID. Required.
              'affiliation': product_store,   // Affiliation or store name.
              'revenue': '0.10',               // Grand Total.
              'shipping': '',                  // Shipping.
              'tax': ''                     // Tax.
            });
           
           ga('ecommerce:addItem', {
              'id': trans_id,                     // Transaction ID. Required.
              'name': product_name,    // Product name. Required.
              'sku': product_id,                 // SKU/code.
              'category': '',         // Category or variation.
              'price': '0.10',                 // Unit price.
              'quantity': '1'                   // Quantity.
            });

           ga('ecommerce:send');
           ga('ecommerce:clear');

           mixpanel.track("Purchase", {"Source": source });

           mixpanel.people.track_charge(0.10);

           //return  "/purchase/" + product_id;
        }   


        

        UI.switchTrendingSection = function (section_id) 
        {

            $.cookie('trending_section_id', section_id, { expires: 100, path: '/' });
            location.reload();
              
        }


        return new UI();
    }());
});
