if (window.location.hash == '#_=_')window.location.hash = '';

var domain_url = window.location.hostname 
if(domain_url=='localhost'){
	domain_url = domain_url + ':8888';	
}
var limit = 0;
var sub_limit = 0;
var notoload = 0;
var isloading = 0;
var filter = {};
var preview_params = {};
var product_preview_status = 0;


$(document).ready(function(){


	$(this).keydown(function (eventObject) {
        if (eventObject.which == 27) {
        	if(product_preview_status == 1){
            	$('#btn-preview-close').trigger('click');
            }	
            popup_close();
        }
        if (eventObject.which == 39) {
            //if (gallery) {
                //$('#image-gallery .Next').trigger('click');
            //}
        }
        if (eventObject.which == 37) {
            //if (gallery) {
                //$('#image-gallery .Prev').trigger('click');
            //}
        }
    });

	
	$('#product-preview-cover').click(function(){
		  $('#btn-preview-close').trigger('click');
	});

	$('#popup .cover').click(function(){
		  popup_close();
	});

	$('#btn-preview-close').click(function(){
		$('#product-preview').hide();
		document.body.style.overflow = "visible";
		window.history.back(-1);
		product_preview_status = 0;
	});

	/*$('.filter li').click(function(){
		var type = $(this).attr('type');

		$(this).parent('.filter').children('li').removeClass('active');
		$(this).addClass('active');

		if(type == 'category'){
			filter['category_id'] = $(this).attr('categoryid');
			//var method = method;
			var params = {};
			change_filter(method, params);
		}

		
	});*/
	/*
	$('.h-nav-main').hover(function(){
		$('.h-nav-main ul').toggle();
	});

	$("body").click(function() {
	    $(".h-nav-main ul").hide();
	});

	$(".h-nav-main").click(function(e) {
	    e.stopPropagation();
	});
	*/
	

	$('#product-preview').scroll(function(){
		///console.log($('#product-preview-content').height()-$(window).height());
		//console.log($('#product-preview').scrollTop());

	    if ($('#product-preview').scrollTop() >= 
	      $('#product-preview-content').height() - $(window).height() - 800) { 
	        get_content_items('products.similar', preview_params, '#product_item_tpl', '#product-preview-similar-products', true);
	    } 
	});

});


$(window).resize(function() {
	wr();
	
});


function purchase_url(id) {	
	return 	"/purchase/" + id;
}	

function save_changes(store_id, domain, name, store_views, add_time){
    data='store_id=' + store_id + '&domain='+domain+'&name='+name+'&store_views='+store_views+'&add_time='+add_time;
	$.ajax({
	  dataType: "json",
	  type: 'GET',
	  data: data,
	  url: 'http://'+domain_url+'/admin/store.set',
	  beforeSend: function(){  }, 
	  success: function(answ){
	  	console.log(answ);	
	   }
	});	
  	
}


function purchase(url) {
	window.open(url);

	/*var yaGoalParams = {
	  order_id: "{{ purchase['purchase_id']  }}",
	  order_price: "{{ purchase['product']['price']  }}", 
	  currency: "RUR",
	  exchange_rate: 1,
	  goods: 
	     [
	        {
	          id: "{{ purchase['product']['product_id']  }}", 
	          name: "{{ purchase['product']['product_title']  }}", 
	          price: "{{ purchase['product']['price']  }}",
	          quantity: 1
	        } 
	      ]
	};*/

	yaCounter20395936.reachGoal('PURCHASE');
	return false;	
}


function send(product_id){
		show_popup('send');

		var params = {};
		params['product_id'] = product_id;

		get_content_items('friends.getList', params, '#tag_friend_tpl', '#friendstotag', true);


		$('#friendstag_search').domsearch('#friendstotag');
		$('#friendstag_search').focus();

}

function tag_friend_post(owner_id, product_id){
	var product_url = 'http://' + window.location.hostname + '/p/' + product_id + '?utm_campaign=tag_friend';

	VK.Api.call('wall.post', {
		message: "Тебе должно понравиться",
		attachments: product_url,
		owner_id: owner_id,
	}, 
	function(r) {
		popup_close();	
	});

}


function popup_close(){
	$('#popup').hide();
	$('.popup').hide();	

	$('#friendstotag').html('');
	$('#friendstag_search').val('');

	document.body.style.overflow = "visible";
	notoload = 0;
 	isloading = 0;
 	product_preview_status = 0;
}

function show_popup(name){
	//if(name == 'login'){
	//	self.location='http://'+domain_url+'/welcome';
	//	return;
	//}

	var popup_id = '#popup-' + name;
	$('#popup').show();
	$('.popup').hide();
	$(popup_id).show();

	document.body.style.overflow = "hidden";
}


$.fn.serializeObject = function()
{
   var o = {};
   var a = this.serializeArray();
   $.each(a, function() {
       if (o[this.name]) {
           if (!o[this.name].push) {
               o[this.name] = [o[this.name]];
           }
           o[this.name].push(this.value || '');
       } else {
           o[this.name] = this.value || '';
       }
   });
   return o;
};

function filters(){

	
	params = $("#filters").serializeObject();
	//var filters_param=$('#filters').serialize();
	//params.push(filters_param);
	$('#products').html('');
	limit = 0;
	notoload = 0;
	isloading = 0;
	//params[type]=value;
	
	get_content_items(method, params, '#product_item_tpl', '#products');
	/*var filter_id='#filter_'+type;
	$(filter_id).children('a').removeClass('active');
	$(elem).addClass('active');*/
	//console.log(params);

}

function follow(type, item_id, action){
	
 	var btnid = '.btnfollow_' + type + '_' + item_id
	var data =  {};
    data['type'] = type;
    data['item_id'] = item_id;
  			

	if(action=='follow'){
		data['action'] = 'follow';
		var btext =  'В обновлениях';
		var onc = 'follow("' + type + '", ' + item_id + ', "unfollow")';
	}else{	
		data['action'] = 'unfollow';
		var btext =  'Подписаться';
		var onc = 'follow("' + type + '", ' + item_id + ', "follow")';
	}
	
	$.ajax({
	  type: 'GET',
	  data: data,
	  url: 'http://'+domain_url+'/api/follow.set',
	  beforeSend: function(){ $(btnid).attr('onclick', ''); }, 
	  success: function(answ){
		  $(btnid).html(btext);
		  $(btnid).attr('onclick', onc);
		  
			if(action=='follow'){
				$(btnid).removeClass('btn-follow-follow');
			}else{	
				$(btnid).addClass('btn-follow-follow');
			}
	
	   }
	});

}

function save(product_id, action){
	
 	var btnid = '.btnsave_' + product_id;
 	var btncount = parseInt($(btnid + ' span').text());

 	var data =  {};
    data['product_id'] = product_id;
    	
	
	if(action=='add'){
		data['action'] = 'add';
		btncount = btncount + 1;
		var btext =  'Сохранено';
		var onc = 'save(' + product_id + ', "delete")';
	}else{	
		data['action'] = 'delete';
		var btext =  'Сохранить';
		btncount = btncount - 1;
		var onc = 'save(' + product_id + ', "add")';
	}
	
	$.ajax({
	  type: 'GET',
	  data: data,
	  url: 'http://'+domain_url+'/api/save.set',
	  beforeSend: function(){ $(btnid).attr('onclick', ''); }, 
	  success: function(answ){
		  $(btnid + ' div').html(btext);
		  $(btnid + ' span').html(btncount);
		  $(btnid).attr('onclick', onc);
		  
			if(action=='add'){
				$(btnid).addClass('btn-save-save');
			}else{	
				
				$(btnid).removeClass('btn-save-save');
			}
	
	   }
	});

}

function like(product_id, action){
	
 	var btnid = '.btnlike_' + product_id;
 	var btncount = parseInt($(btnid + ' span').text());

 	var data =  {};
    data['product_id'] = product_id;
    	
	
	if(action=='add'){
		data['action'] = 'add';
		btncount = btncount + 1;
		var onc = 'like(' + product_id + ', "delete")';
	}else{	
		data['action'] = 'delete';
		btncount = btncount - 1;
		var onc = 'like(' + product_id + ', "add")';
	}
	
	$.ajax({
	  type: 'GET',
	  data: data,
	  url: 'http://'+domain_url+'/api/like.set',
	  beforeSend: function(){ $(btnid).attr('onclick', ''); }, 
	  success: function(answ){
		  $(btnid).attr('onclick', onc);
		  $(btnid + ' span').html(btncount);
		  
			if(action=='add'){
				$(btnid).addClass('btn-like-like');
			}else{	
				
				$(btnid).removeClass('btn-like-like');
			}
	
	   }
	});

}

function comments_load(product_id){

	var data =  {};
    data['product_id'] = product_id;

    var el = '.product-comments-' + product_id;

	$.ajax({
		dataType: "json",
		type: 'GET',
	    data: data,
	    url: 'http://'+domain_url+'/api/comments.get',
	    beforeSend: function(){ }, 
	    success: function(answ){
		  $('#comment_item_tpl').tmpl(answ).appendTo(el+' .comments');
		  $("time.timeago").timeago();
	   }
	  

	});
}


function product_preview(product_id, save_id){

		$.ajax({
		  	  dataType: "json",	
			  type: 'GET',
			  data: 'product_id=' + product_id + '&type=full&userH=' + $.cookie('userH'),
			  url: 'http://'+domain_url+'/api/products.getById', 
			  beforeSend: function(){
			  		//isloading = 1; 
			  		//$('#loading').animate({opacity: 1}, 500);
			  		$('#product-preview-container').html('');
			  		$('#product-preview-similar-products').html('');
			  		sub_limit = 0;
			  		notoload = 0;
			  		product_preview_status = 1;
			  },
			  success: function(answ){

			  		$("#product-preview-container").html('');
					$('#product_preview_tpl').tmpl(answ).appendTo('#product-preview-container');

					preview_params['store_id'] =  answ['store_id'];
					preview_params['store_category'] =  answ['store_category'];

					comments_load(product_id);

					notoload = 0;
       				get_content_items('products.similar', preview_params, '#product_item_tpl', '#product-preview-similar-products', true);

       				history.pushState(null, null, '/p/' + answ['product_id'] + '/' + answ['product_name_url']);
					$('#product-preview').show();
					document.body.style.overflow = "hidden";
					
		
			  }
		  });		



}



function get_content_items(method, params, tpl, container, is_sub_limit){

	if(!is_sub_limit){
		params['limit'] = limit;
	}else{
		params['limit'] = sub_limit;
	}

	params = $.extend(params, filter);

	if(notoload == 0){
		  if(isloading == 0){
		  $.ajax({
		  	  dataType: "json",	
			  type: 'GET',
			  data: params,
			  url: 'http://'+domain_url+'/api/'+method, 
			  beforeSend: function(){
			  		isloading = 1; 
			  		NProgress.start();
			  },
			  success: function(answ){
			  		NProgress.done();
			  		if(!answ && limit == 0){ $('.empty-page').show(); return;}

					$(tpl).tmpl(answ).appendTo(container);
					$("time.timeago").timeago();
					$('.btn').tipsy({gravity: 's'});
		
					
					
					if(!is_sub_limit){
						limit = parseInt(limit) + 50;
					}else{
						sub_limit = parseInt(sub_limit) + 50;
					}	
			
				  	if(answ==''){ notoload = 1;}
				  	isloading = 0;
				  	
			  }
		  });
		  }
	  }
}

function wr(){

	ww = $(window).width();
	wh = $(window).height();
	
	var dw = $(document).width();
	var dh = $(document).height();
	
}



