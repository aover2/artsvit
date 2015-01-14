;"use strict";

window.Utils = {
    isObject: function (object) {
        return Object.prototype.toString.call(object) === '[object Object]';
    },
    isArray: function (array) {
        return Object.prototype.toString.call(array) === '[object Array]';
    },
    isNumber: function (number) {
        return Object.prototype.toString.call(number) === '[object Number]';
    },
    toArray: function (object) {
        object = this.isArray(object) ? object : [object];
        return object;
    },

    timeout: function(cb, time) {
    	var time = time || 1 * 1000; // by def 1 sec 
    	var timer = setTimeout(function() {
    		cb();
    		clearTimeout(timer);
    	}, time);
    },

    share: function(data, share_url) {
        var self = this;

        var w = 600;
        var h = 400;
       
        var title = 'Поделиться';
        var type = data['type'];

        var site_root = 'http://' + window.location.hostname; 

        switch(type){
            case 'vk':
                var url = 'https://vk.com/share.php?url=' + site_root + '/' + share_url;
                break;

            case 'fb':
                var url = 'https://www.facebook.com/sharer/sharer.php?u=' + site_root + '/' + share_url;
                break;

            case 'tw':
                var text = data['text'];
                var url = 'https://twitter.com/intent/tweet?url=' + site_root + '/' + share_url + '&text=' + text;
                break;

            case 'gp':
                var url = 'https://plus.google.com/share?url=' + site_root + '/' + share_url;
                break;


            case 'pin':
                var media = data['media'];
                var description = data['description'] || 'I like it!';
                var url = 'http://pinterest.com/pin/create/button/?url=' + site_root + '/' + share_url +
                          '&media=' + media +
                          '&description=' + description;
                break;

            default: return false;        

        }

        var left = (screen.width/2)-(w/2);
        var top = (screen.height/2)-(h/2);

        var sw = window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);  
            
        if($.cookie('welcome')){
            mixpanel.track("WelcomeShare");
            $.removeCookie('welcome', { path: '/' });     
        }
        
        mixpanel.track("Share", {"Type": type});
        setTimeout(function() { self.popupClose() }, 5000);

    },

    popupClose: function(){
        $('.popup').hide();
        $('.popup .popup-content').css('top', $(window).height() + 'px' ); 

        document.body.style.overflow = "visible";
        notoload = 0;
        isloading = 0;
        product_preview_status = 0;

    },

    forEach: function(obj, cb) {
        if (this.isArray(obj)) {
            for (var i = 0; i < obj.length; i++) {
                cb(obj[i], i, obj);
            }
        }
        else if (this.isObject(obj)) {
            for (var key in obj) {
                if (obj.hasOwnProperty(key)) {
                    cb(obj[key], key, obj);
                }
            }
        }
    }

};