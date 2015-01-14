window.API = (function() {
    function API() {
        var pathArray = window.location.pathname.split( '/' );
        var domain_url = window.location.hostname;
        if(domain_url=='localhost'){
            domain_url = domain_url + ':8888';  
        } 
        
        this.page_name = pathArray[1];
        this.subpage_name = pathArray[2];
        this.subsubpage_name = pathArray[3];

        this.site_root = 'http://'+domain_url;
        this.api_url = 'http://'+domain_url+'/api/';

        this.params = {};
        this.filters = {};
        this.filter_names = ['tags', 'price', 'subkinds', 'colors', 'sort', 'stores'];
        
        this.controller_name = false;
        this.page_number = 1;
        this.notoload = false;
        this.isloading = false;
        this.current_user_id = false;

        if($.cookie('userH')){
          this.islogin = true;  
        }else{
          this.islogin = false;  
        }




    }

    API.prototype.itemsGet = function() {
        var api = this;


        var method = '';
        var template = '';
        var container = '';
        var els_type = ''

        switch(api.controller_name) {
            case 'shop':
                api.params['type'] = 'shop';
                getProducts(); 
                break;
            case 'search':
                api.params['type'] = 'search';
                getProducts();
                break;
            case 'feed':
                api.params['type'] = 'feed';
                getProducts();
                break;
            case 'trending':
                api.params['type'] = 'trending';
                getProducts();
                break;
            case 'store':
               
                if(!api.subsubpage_name){
                     api.params['type'] = 'store';
                    getProducts();
                }
                if(api.subsubpage_name == 'followers'){
                    api.params['type'] = 'store_followers';
                    getUsers();
                }
                break;        
            case 'tag':
                if(!api.subpage_name){
                    api.params['type'] = 'tag';
                    getProducts();
                }
                if(api.subpage_name == 'followers'){
                    api.params['type'] = 'tag_followers';
                    getUsers();
                }
                break;
            case 'user':
                if(!api.subpage_name){
                    api.params['type'] = 'user';
                    getProducts();
                }
                if(api.subpage_name == 'stores'){
                    api.params['type'] = 'user';
                    getStores();
                }
                if(api.subpage_name == 'tags'){
                    api.params['type'] = 'user';
                    getTags();
                }
                if(api.subpage_name == 'followers'){
                    api.params['type'] = 'followers';
                    getUsers();
                }
                if(api.subpage_name == 'following'){
                    api.params['type'] = 'following';
                    getUsers();
                }
                
                break; 
            case 'trendsetters':
                api.params['type'] = 'popular';
                getUsers();
                break; 
            case 'tags':
                api.params['type'] = 'popular';
                getTags();
                break; 
            case 'stores':
                api.params['type'] = 'popular';
                getStores();
                break;  
            case 'wizard':
                if(api.subpage_name == 'step1'){
                    api.params['type'] = 'popular';
                    getStores();
                }
                if(api.subpage_name == 'step2'){
                    api.params['type'] = 'wizard';
                    getUsers();
                }
                break;
            case 'find':
                api.params['type'] = 'find_friends';
                getUsers();
                break; 

             case 'sets':
                api.params['type'] = 'all';
                getSets();
                break;                           

        }


        function getProducts(){
            method = 'products.get';
            els_type = 'products';
            template = '#product_item_tpl';
            container = '#products';
        }

        function getUsers(){
            method = 'users.get';
            els_type = 'users';
            template = '#user_item_tpl';
            container = '#users';
        }

        function getTags(){
            method = 'tags.get';
            els_type = 'tags';
            template = '#tag_item_tpl';
            container = '#tags';
        }

        function getStores(){
            method = 'stores.get';
            els_type = 'stores';
            template = '#store_item_tpl';
            container = '#stores';
        }

        function getSets(){
            method = 'sets.get';
            els_type = 'sets';
            template = '#set_item_tpl';
            container = '#sets';
        }


        api.params['page_number'] = api.page_number + 1;


        api.filter_names.forEach(function(filter_name) {
            if(api.filters[filter_name] && api.filters[filter_name].length != 0){
                api.params[filter_name] = api.filters[filter_name].join(",");
            }       
        });

        $.ajax({
            dataType: "json", 
            type: 'GET',
            data: api.params,
            url: api.api_url + method, 
            beforeSend: function(){
                api.isloading = 1;
                $('.btn-load-more').hide();
                $('#loading').show();
            },
            success: function(answ){

                
                $('#loading').hide();
                $('.btn-load-more').show();

                if(answ){
                    UI.renderItems(answ[els_type], template, container);
                    if(answ[els_type] == '' || answ == 'false'){ 
                        api.notoload = 1; 
                        $('.btn-load-more').hide();

                        if(api.page_number == 0){
                            $('.empty-page').show();
                        } 
                    }

                    API.page_number = api.page_number++;   
                    api.isloading = false;
                    params = false;
                }else{
                    $('.btn-load-more').hide();
                    $('.empty-page').show();
                    API.page_number = api.page_number++;   
                    api.isloading = false;
                    api.notoload = 1;
                    params = false;   
                }

            }
        });


        
    }

    API.prototype.productGet = function(product_id){
        var api = this;
        var data = 'product_id=' + product_id + '&type=view';


        $.ajax({
              dataType: "json", 
              type: 'GET',
              data: data,
              url: api.api_url + 'products.getById', 
              beforeSend: function() { 
                $("#product-preview-container").html('');
                $('#product-preview').show();
                document.body.style.overflow = "hidden"; 
                $('#prod-prev-loading').addClass('active');
              },
              success: function(answ) {
                $('#prod-prev-loading').removeClass('active');

                UI.productPreview(answ);
                ga('send', 'event', 'product', 'click', 'preview');
                    
              },
              error: function(){
                $('#prod-prev-loading').removeClass('active');
                UI.productPreviewClose();
                alert('Some troubles, please try again later');
              }
          });       
    }

    API.prototype.productSave = function(product_id) {

        var api = this;
        var btnid = '.btnsave_' + product_id;
        var btncount = parseInt($(btnid + ' span').text());

        var data =  {};
        data['product_id'] = product_id;

        if($(btnid).data('save-status') == 1){
            var action = 'delete';
        }else{
            var action = 'add';
        }
            
        
        if(action=='add'){
            data['action'] = 'add';
            btncount = btncount + 1;
            var btext =  'Сохранено';
        }else{  
            data['action'] = 'delete';
            var btext =  'Сохранить';
            btncount = btncount - 1;
        }
        
        $.ajax({
          type: 'GET',
          data: data,
          url: api.api_url + 'save.set',
          beforeSend: function(){ $(btnid).attr("disabled", true); }, 
          success: function(answ){
              $(btnid + ' div').html(btext);
              $(btnid + ' span').html(btncount);
              $(btnid).attr("disabled", false);
              
                if(action=='add'){
                    $(btnid).addClass('btn-save-save');
                    $(btnid).data('save-status', 1);

                    mixpanel.track("Product save");
                    ga('send', 'event', 'button', 'click', 'save');


                }else{
                    $(btnid).removeClass('btn-save-save');
                    $(btnid).data('save-status', 0);
                }
        
           }
        });

    }


    API.prototype.follow = function(type, item_id, action) {

        var api = this;
    
    
        var btnid = '.btn-follow-' + type + '-' + item_id
        var data =  {};
        data['type'] = type;
        data['item_id'] = item_id;
                

        if(action=='follow'){
            data['action'] = 'follow';
            var btext =  'В обновлениях';
            var follow_status = 1;

            mixpanel.track("Follow " + type);

        }else{  
            data['action'] = 'unfollow';
            var btext =  'Подписаться';
            var follow_status = 0;
        }
        
        $.ajax({
            type: 'GET',
            data: data,
            url: api.api_url + 'follow.set',
            beforeSend: function(){ $(btnid).attr("disabled", true); }, 
            success: function(answ){

                $(btnid).html(btext);
                $(btnid).data('follow-status', follow_status);
                $(btnid).attr("disabled", false);

                if(action=='follow'){
                    $(btnid).addClass('btn-follow-follow');
                    ga('send', 'event', 'button', 'click', 'follow');
                }else{  
                    $(btnid).removeClass('btn-follow-follow');
                }

            }
        });



    }


    API.prototype.signup = function(data, el_form) {

        var api = this;
        
        $.ajax({
            type: 'POST',
            data: data,
            url: api.api_url + 'user.signup',
            beforeSend: function(){ $(el_form).find('.btn-submit').attr("disabled", true);}, 
            success: function(answ){
    
                var answ = JSON.parse(answ);

                if(answ['status']=='error'){
                    $(el_form).find('.btn-submit').attr("disabled", false);
                    $(el_form).find('.error').text(answ['msg'])
                    $(el_form).find('.error').show();
                    return;
                }

                if(answ['status']=='logged'){
                   ga('send', 'event', 'user', 'signup');

                   $.cookie('userH', answ['userH'], { expires: 100, path: '/' }); 
                   $.cookie('welcome', 1, { expires: 100, path: '/' }); 
                   window.location.replace(api.site_root + '/wizard/step1');
                }

                
            }
        });

    }

    API.prototype.signin = function(data, el_form) {

        var api = this;
        
        $.ajax({
            type: 'POST',
            data: data,
            url: api.api_url + 'user.login',
            beforeSend: function(){ $(el_form).find('.btn-submit').attr("disabled", true);}, 
            success: function(answ){
    
                var answ = JSON.parse(answ);

                if(answ['status']=='error'){
                    $(el_form).find('.btn-submit').attr("disabled", false);
                    $(el_form).find('.error').text(answ['msg'])
                    $(el_form).find('.error').show();
                    return;
                }

                if(answ['status']=='logged'){
                   $.cookie('userH', answ['userH'], { expires: 100, path: '/' }); 
                   location.reload();
                }

                
            }
        });

    }

    API.prototype.userSet = function(data) {
        var api = this;
        $.ajax({
            type: 'POST',
            data: data,
            dataType: 'JSON',
            url: api.api_url + 'user.set',
            beforeSend: function(){ }, 
            success: function(answ){
                location.reload();
            }
        });

    }

    API.prototype.commentAdd = function(data) {

        var api = this;

        var datae= $.unserialize(data);
        var el = '.product-comments-' + datae['product_id'];
    

        $.ajax({
            type: 'POST',
            data: data,
            url: api.api_url + 'comments.add',
            beforeSend: function(){ $('#signup-form .btn-submit').attr("disabled", true); }, 
            success: function(answ){
                var answ = JSON.parse(answ);
                
                $('#comment_item_tpl').tmpl(answ).prependTo(el+' .comments');
                $("time.timeago").timeago();
                $(el+' form .comment-input').val('');

                ga('send', 'event', 'button', 'click', 'comment');
            }
        });

    }


    API.prototype.notificationsView = function() {

        var api = this;

        $.ajax({
            type: 'POST',
            url: api.api_url + 'notifications.view',
            beforeSend: function(){ }, 
            success: function(answ){
                $('.notifications-count').hide();
            }
        });

    }

    API.prototype.getUrlParameter = function (sParam)
        {
            var sPageURL = window.location.search.substring(1);
            var sURLVariables = sPageURL.split('&');
            for (var i = 0; i < sURLVariables.length; i++) 
            {
                var sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] == sParam) 
                {
                    return sParameterName[1];
                }
            }
        }   



    return new API();
}());