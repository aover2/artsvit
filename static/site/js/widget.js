;(function(jQuery) {
  "use strict"; 

  $ = jQuery;

  var domain_url = window.location.hostname;
  if (domain_url === 'localhost') domain_url += ':8888';  
  

  $(document).ready(function() {

      var elms = {
          price: $('.input-price'),
          currency: $('#currency_id'),
          section: $('#section_id'),
          images: $('.image-slider .image'),
          slider: $('.image-slider ul'),
          navigateBtn: $('.navigate-btn')
      };

      var deltaW = elms.images.width(),
          imageCount = elms.images.length, 
          index = 0;


      function getIndex(index, isLeft) {
        var index = parseInt(index),
            delta = isLeft ? -1 : 1;

        index += delta;
        if (index >= imageCount) index = 0;
        else if (index < 0) index = imageCount-1;

        return index;
      };

      function sliding(delta) {   
        elms.slider.animate({marginLeft: "-"+delta}, 400);  
      }

      var valueMapper = {
        price: function(index) {
          return window.prices? window.prices[index]:'';
        },
        currency: function(index) {
          return window.currency_id? window.currency_id[index]:'';
        },
        title: function(index) {
          return window.titles? window.titles[index]:'';
        }
      };  

      function selectImage(index) {
        if (index >= 0 && index < imageCount) {
          elms.images.removeClass('_selected_');
          $(elms.images[index]).addClass('_selected_');
          $('input[type=hidden][name=image]').val($(elms.images[index]).find('img').attr('src'));
          // elms.currency.val(valueMapper.currency(index));
          // $('.currency-switcher a').removeClass('active');
          // $('.currency-switcher a[data-id='+valueMapper.currency(index)+']').addClass('active');
          // elms.price.val(valueMapper.price(index));
          elms.navigateBtn.removeClass('active');
          elms.navigateBtn.filter('[data-id='+index+']').addClass('active');
          // $('.input-title').val(valueMapper.title(index));
          sliding(index*deltaW);
        }
      }
      selectImage(0, 0);

      elms.navigateBtn.on('click', function(event) {
        var self = $(this);
        elms.navigateBtn.removeClass('active');
        self.addClass('active'); 
        index = self.attr('data-id');
        selectImage(index);
      });

      if (imageCount <= 1) {
        $('.navigate').hide();
      }
      else {
        $('.navigate').show();
        $('.arrows span').on('click', function(event) {
          var isLeft = $(this).hasClass('left');
          index = getIndex(index, isLeft);
          selectImage(index);
        });
      }

      $('body').on('click', '.btn-share', function() {
        var self = $(this), url = encodeURIComponent(self.parent('div').data('url'));
        Utils.share(self.data(), url);
      });

      function isValidField(el) {
        if (el && typeof el.val === 'function')
        return el.val()===0 || el.val()==='';
      };

      $('#widget_form').on('submit', function(event) {
        event.preventDefault();
        var data = $(this).serialize();

        if (isValidField(elms.price)) {
          alert('Укажите цену');
          return;
        }
        else if (isValidField(elms.currency)) {
          alert('Выберите валюту');
          return;
        }
        else if (isValidField(elms.section)) {
          alert('Выберите категорию');
          return;
        }

        $.ajax({
            type: 'GET',
            data: data,
            url: 'http://'+domain_url+'/api/products.add',
            beforeSend: function(){ $('.btn-submit').attr('disabled','disabled'); $('.btn-submit').val('Сохраняется...'); }, 
            success: function(answ){ 

              var data = $.parseJSON(answ);
              if (data.status !== 'error') {
                $('#widget_form').hide();
                $('#widget .saved').show();

                mixpanel.track("Product Save Bookmarklet");

                var view_url = 'http://'+domain_url+'/p/'+ data['id'] + '/' + data['name_url'];
                $('#widget .btn-view').attr("href", view_url);

                $('.share-btns').attr('data-url', "p/"+data.id+"/"+data.name_url+"?utm_campaign=share");
                $('.share-btns').find('.btn-tw').attr('data-text', data.name + ' - ');
                $('.share-btns').find('.btn-pin')
                  .attr('data-media', data.img)
                  .attr('data-description', data.description);

                /* Automatically hide bookmarklet popup after 5 sec left */
                Utils.timeout(function(){
                  window.parent.postMessage({action:'scrubbly:frame:hide'}, "*");
                }, 10000);   

              } else {
                console.log(data);
                alert('Приносим извинения, но на данный момент товары невозможно сохранить. Попробуйте позже');
              }
               
            }
          });
      });

      $('.need-to-authorize').on('click', function(event) {
        window.parent.postMessage({action:'scrubbly:frame:authorize'}, "*");
      });

      $('.switcher a').click(function() {
        var self = $(this), switcher = self.parent('.switcher');
        var dataid = self.data('id'), inputid = '#' + switcher.data('name');

        $(inputid).val(dataid);
        switcher.children('.active').removeClass('active');
        self.addClass('active');
      });

  });
}(jQuery));
