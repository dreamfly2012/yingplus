//显示弹窗
function show_content(content){
    $('.alphalayer').fadeIn(200, function() {
        $('#ajax_form').html(content).fadeIn(200);
    });
}
function show_form(url) {
    $.ajax({
        url: url,
        method: "POST",
        data: {},
        dataType: "html",
        success: function(data) {
            $('.alphalayer').fadeIn(200, function() {
                $('#ajax_form').html(data).fadeIn(200);
            });
        },
        async: true
    });
}

function show_form_parameter(url,param){
    $.ajax({
        url: url,
        method: "POST",
        data: param,
        dataType: "html",
        success: function(data) {
            $('.alphalayer').fadeIn(200, function() {
                $('#ajax_form').html(data).fadeIn(200);
            });
        },
        async: true
    });
}

function show_judgement_form_parameter(url,param){
    $.ajax({
        url: url,
        method: "POST",
        data: param,
        dataType: "json",
        success: function(data) {
            if(data.data.code==0){
                $('.alphalayer').fadeIn(200, function() {
                    $('#ajax_form').html(data.data.info).fadeIn(200);
                });
            }else{
                alert(data.data.message);
            }
        },
        async: false
    });
}
//关闭弹窗
$(document).on('click', '.close_form', function() {
    //$('body').removeClass('bodyhide')
    $('#ajax_form').fadeOut(200, function() {
        $('.alphalayer').fadeOut(200)
    })
});;
//获取浏览器URL中的参数
function getParameterValue(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null
}
//只能输入数字
function onlyNum() {
    if (!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39))
        if (!((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105))) event.returnValue = false;
}
// 手机的格式验证
function check_phone(str) {
    var re = /^0?1[1|3|4|5|8][0-9]\d{8}$/;
    if (re.test(str) == false) {
        return false;
    } else {
        return true;
    }
}
//验证码
(function($) {
    $.fn.countdown = function(options) {
        var defaults = {
            time: 60,
            text: "秒",
            stop: 0,
            method: "text"
        }
        var options = $.extend({}, defaults, options);
        this.each(function() {
            $this = $(this);
            if (options.method == 'text') {
                old_value = $this.text();
            } else {
                old_value = $this.val();
            }
            $this.attr("disabled", true);
            interval = setInterval(function() {
                if (options.method == 'text') {
                    if (options.time > 0) {
                        options.time--;
                        $this.text(options.time + options.text);
                    } else {
                        clearInterval(interval);
                        $this.text(old_value);
                        $this.attr("disabled", false);
                    }
                } else {
                    if (options.time > 0) {
                        options.time--;
                        $this.val(options.time + options.text);
                    } else {
                        clearInterval(interval);
                        $this.val(old_value);
                        $this.attr("disabled", false);
                    }
                }
            }, 1000);
        });
    }
})(jQuery);

//分享
function share_qq(url,share_id,title,desc,summary,site,pics){
	var share_url = 'http://connect.qq.com/widget/shareqq/index.html?url='+url+'&share_id='+share_id+'&title='+title+'&desc='+desc+'&summary='+summary+'&site='+site+'&pics='+pics;
	window.location.href = share_url;
}

function share_weibo(url,share_id,title,appkey,pic){
	//255864200
	var share_url = 'http://service.weibo.com/share/share.php?url='+url+'share_id='+share_id+'&title='+title+'&appkey='+appkey+'&pic='+pic+'&searchPic=true';
	window.location.href = share_url;
}

function share_qzone(url,share_id,title,desc,summary,site,pics){
	var share_url = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url='+url+'share_id='+share_id+'&title='+title+'&desc='+desc+'&summary='+summary+'&site='+site+'&pics='+pics;
	window.location.href = share_url;
}

function share_weixin(url){
	$("body").append("<div id=\"weixin_qrcode_dialog\" "+
                        "style=\"position: absolute;left:50%;top:50%;padding: 10px;width: 240px;height: 300px;background: #fff;border: solid 1px #d8d8d8;z-index: 11001;font-size: 12px;\" >"+
            "<div><span style=\"float:left;\">分享到微信朋友圈</span>"+
            "<a class=\"weixin_qrcode_close\" style=\"float:right;\" onclick=\"return false;\" href=\"javascript:;\">×</a>"+
            "</div><div style=\"margin-top: 40px; margin-left: 20px;\" id=\"weixin_qr_generate\"></div>"+
            "<div style=\"margin-top: 20px;\">打开微信，点击底部的“发现”，<br>使用“扫一扫”即可将网页分享至朋友圈。</div></div>");
	$("#weixin_qrcode_dialog").css('display','block');
	$("#weixin_qr_generate").qrcode({ 
        render: "table",
        width:200,
        height:200,
        text: url,
        correctLevel: 2
    });

    $(".weixin_qrcode_close").click(function(){
        $("#weixin_qrcode_dialog").hide();
    });
}

function getWinInfo(){
    var info={};
    if (window.innerWidth)
        info.winWidth = window.innerWidth;
    else if ((document.body) && (document.body.clientWidth))
        info.winWidth = document.body.clientWidth;
    if (window.innerHeight)
        info.winHeight = window.innerHeight;
    else if ((document.body) && (document.body.clientHeight))
        info.winHeight = document.body.clientHeight;
    if (document.documentElement && document.documentElement.clientHeight && document.documentElement.clientWidth){
        info.winHeight = document.documentElement.clientHeight;
        info.winWidth = document.documentElement.clientWidth;
    }
    return info;
}


//业务逻辑
function later_alert(){
    alert('即将上映，敬请期待~');
}
function ajax_topic_page(p){
    $.ajax({
        type : "POST",
        url : topic_page_url,
        data : {'p': p,'fid':fid},
        dataType : "html",
        success : function(result) {
            //$(".topic_page").html(result);
        },
        async : false
    });
}

function ajax_page_donate_order(p){
     $.ajax({
        type : "POST",
        url : donate_order_page_url,
        data : {'p': p,'fid':fid},
        dataType : "html",
        success : function(result) {
            $(".order_block").html(result);
        },
        async : false
    });
}


function ajax_recommend_activity_page(p){
    $.ajax({
        type : "POST",
        url : recommend_activity_page_url,
        data : {'p': p,'fid':fid},
        dataType : "html",
        success : function(result) {
            $(".recommend_activity_block").html(result);
        },
        async : false
    });
}


function ajax_movie_activity_page(p){
    $.ajax({
        type : "POST",
        url : movie_activity_page_url,
        data : {'p': p,'fid':fid},
        dataType : "html",
        success : function(result) {
            $(".movie_activity_block").html(result);
        },
        async : false
    });
}


function ajax_online_activity_page(p){
    $.ajax({
        type : "POST",
        url : online_activity_page_url,
        data : {'p': p,'fid':fid},
        dataType : "html",
        success : function(result) {
            $(".online_activity_block").html(result);
        },
        async : false
    });
}

function ajax_welfare_event_page(p){
    $.ajax({
        type : "POST",
        url : welfare_event_page_url,
        data : {'p': p,'fid':fid},
        dataType : "html",
        success : function(result) {
            $(".welfare_event_block").html(result);
        },
        async : false
    });
}

function ajax_response_page(id){
    $.ajax({
        type : "POST",
        url : response_page_url,
        data : {'p': id,'fid':fid,'type':type,'pid':pid},
        dataType : "html",
        success : function(result) {
            $(".response_block").html(result);
            $('.argtext').parseEmotion();
        },
        async : false
    });
}

function ajax_hot_topic_page(id){
    $.ajax({
        type : "POST",
        url : hot_topic_page_url,
        data : {'p': id,'fid':fid},
        dataType : "html",
        success : function(result) {
            $(".hot_topic_list_block").html(result);
        },
        async : false
    });
}



function ajax_video_page(p){
    $.ajax({
        type : "POST",
        url : video_page_url,
        data : {'p': p,'fid':fid},
        dataType : "html",
        success : function(result) {
            $(".video_block").html(result);
        },
        async : false
    });
}


//点赞+1效果
function add_plus_effect(obj){
    obj.addClass('on');
}

function base64_encode(str){
    var c1, c2, c3;
    var base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";                
    var i = 0, len= str.length, string = '';

    while (i < len){
            c1 = str.charCodeAt(i++) & 0xff;
            if (i == len){
                    string += base64EncodeChars.charAt(c1 >> 2);
                    string += base64EncodeChars.charAt((c1 & 0x3) << 4);
                    string += "==";
                    break;
            }
            c2 = str.charCodeAt(i++);
            if (i == len){
                    string += base64EncodeChars.charAt(c1 >> 2);
                    string += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
                    string += base64EncodeChars.charAt((c2 & 0xF) << 2);
                    string += "=";
                    break;
            }
            c3 = str.charCodeAt(i++);
            string += base64EncodeChars.charAt(c1 >> 2);
            string += base64EncodeChars.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
            string += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
            string += base64EncodeChars.charAt(c3 & 0x3F)
    }
    return string;
}

function base64_decode(str){
    var c1, c2, c3, c4;
    var base64DecodeChars = new Array(
            -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
            -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63, 52, 53, 54, 55, 56, 57,
            58, 59, 60, 61, -1, -1, -1, -1, -1, -1, -1, 0,  1,  2,  3,  4,  5,  6,
            7,  8,  9,  10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
            25, -1, -1, -1, -1, -1, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36,
            37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1,
            -1, -1
    );
    var i=0, len = str.length, string = '';

    while (i < len){
            do{
                    c1 = base64DecodeChars[str.charCodeAt(i++) & 0xff]
            } while (
                    i < len && c1 == -1
            );

            if (c1 == -1) break;

            do{
                    c2 = base64DecodeChars[str.charCodeAt(i++) & 0xff]
            } while (
                    i < len && c2 == -1
            );

            if (c2 == -1) break;

            string += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));

            do{
                    c3 = str.charCodeAt(i++) & 0xff;
                    if (c3 == 61)
                            return string;

                    c3 = base64DecodeChars[c3]
            } while (
                    i < len && c3 == -1
            );

            if (c3 == -1) break;

            string += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));

            do{
                    c4 = str.charCodeAt(i++) & 0xff;
                    if (c4 == 61) return string;
                    c4 = base64DecodeChars[c4]
            } while (
                    i < len && c4 == -1
            );

            if (c4 == -1) break;

            string += String.fromCharCode(((c3 & 0x03) << 6) | c4)
    }
    return string;
}

//

//烟火
function fire(){
    var fworks = new Fireworks();

    fworks.startFireworks(500,500);

    fworks.startFireworks(400,200);

    fworks.startFireworks(800,300);

    fworks.startFireworks(600,200);

    fworks.startFireworks(800,100);

    fworks.startFireworks(1000,500);

    var isOn = 0, sets, fx, toAnimate = "#effect", settings = {
        animation:8,
        animationType: "in",
        backwards: false,
        easing: "easeOutQuint",
        speed: 1000,
        sequenceDelay: 100,
        startDelay: 0,
        offsetX: 100,
        offsetY: 50,
        restoreHTML: true
    };

    $("#effect").css('display','block');
    fx = $("#effect");
    $.cjTextFx(settings);
    $.cjTextFx.animate(toAnimate);
    

    setTimeout(function(){
        $('canvas').remove();
        $("#effect").css('display','none');
    },3000);
}



$(document).ready(function() {
    //工作室聊天列表美化滚动条
    var guestlistscroll = $(".guestlist").niceScroll({cursorborder:"",cursorwidth:"10px",cursorcolor:"#ffc916",boxzoom:false,railpadding: { top: 3, right: 3, left: 3, bottom:3 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4'});
    $(".onlinelist").niceScroll({cursorborder:"",cursorwidth:"10px",cursorcolor:"#ffc916",boxzoom:false,railpadding: { top: 3, right: 3, left: 3, bottom:3 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4'});
    //照片墙
    $('.picroll').bxSlider();
    //登陆
    $(document).on('click',".head_login",function(){
        show_form(login_form_url);
    });
    //注册
    $(document).on('click',".head_sign",function(){
        show_form(register_form_url);
    });
    //忘记密码
    $(document).on('click',".forget_password",function(){
        show_form(forgetpassword_form_url);
    });
    //QQ登陆
    $(document).on('click',".qq_login",function(){
        var href = window.location.href;
        var backurl = getParameterValue('returnurl')==null ? href : getParameterValue('returnurl');
        //backurl = base64_encode(backurl);
        window.location.href="https://graph.qq.com/oauth2.0/authorize?client_id=101543335&response_type=code&display=pc&redirect_uri=http://yingplus.80shihua.com/Login/loginByQQ?backurl="+backurl;
    });
    //微博登陆
    $(document).on('click',".weibo_login",function(){
        var backurl = window.location.href;
        window.location.href="https://api.weibo.com/oauth2/authorize?client_id=3573788978&redirect_uri=http://yingplus.80shihua.com/Login/loginByWeibo?backurl="+backurl;
    });
    //fancybox
    $('.fancybox').fancybox();
    $('.guitem_movie_activity p img').fancybox({
        'afterClose':function(){
            $('.guitem_movie_activity p img').css('display','block');
        }
    });
    $('.guitem_forum p img').fancybox({
        'afterClose':function(){
            $('.guitem_forum p img').css('display','block');
        }
    });
    
    
    
    //加入工作室
    $(document).on('click','.joinforum',function() {
    	var $this = $(this);
        if (is_login == 'true') {
        	var fid = $(this).attr('data-fid');
        	$.post(join_forum_url,{'fid':fid},function(result){
        		if(result.data.code==0){
        			$this.text('已加入');
                    if($('#forum_fensi_num').length!=0){
                        var num = parseInt($('#forum_fensi_num').text());
                        $('#forum_fensi_num').text(num+1);
                    }
        			$this.removeClass('joinforum').addClass('exitforum');
                    fire();
        		}else{
        			alert(result.data.message);
        		}
        	});
        } else {
            $('.head_login').trigger('click');
        }
    });
    //退出工作室
    $(document).on('click','.exitforum',function() {
    	var $this = $(this);
        if (is_login == 'true') {
        	var fid = $(this).attr('data-fid');
        	$.post(exit_forum_url,{'fid':fid},function(result){
        		if(result.data.code==0){
        			$this.text('+ 加 入');
                    if($('#forum_fensi_num').length!=0){
                        var num = parseInt($('#forum_fensi_num').text());
                        $('#forum_fensi_num').text(num-1);
                    }
        			$this.removeClass('exitforum').addClass('joinforum');
        		}else{
        			alert(result.data.message);
        		}
        	});
        } else {
            $('.head_login').trigger('click');
        }
    });
    //工作室图片上传
    $(document).on('click','.upload_forumpicture',function(){
    	if(is_login=='true'){
    		$("#upload_forumpicture").trigger('click');
    	}else{
    		$('.head_login').trigger('click');
    	}
	});
	//上传图片到云
    $(document).on('change',"#upload_forumpicture",function(){
    	var fid = $(this).attr('data-fid');
        $.ajaxFileUpload({
            url: upload_forumpicture_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'upload_forumpicture', //文件上传域的ID
            data:{
                
            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                //尝试进行json解析
                if(data.data.code==0){
                	//添加到照片墙
                	$.post(add_forumpicture_url,{'attachmentid':data.data.info,'fid':fid},function(result){
                		if(result.data.code==0){
                            alert('上传成功');
                			window.location.reload();
                		}else{
                            alert(result.data.message);
                        }
                	});
                	
                }else{
                	alert(data.data.message)
                }
            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                alert(e);
            }
        });
    });

    //即时回复聊天
    //初始化滚动条到底部
    if( $('.guestlist').length!=0){
        $('.guestlist')[0].scrollTop = $('.guestlist')[0].scrollHeight+200;
    }

    //即时通讯回复
    $(document).on('click','.addresponse',function(){
        if(is_login=='true'){
            var fid = $(this).attr('data-fid');
            var pid = $(this).attr('data-pid');
            var type = $(this).attr('data-type');
            var source = $(this).attr('data-source');
            var preview_img = $('.message_preview_span').html();
            preview_img = (preview_img == null) ? '' : preview_img; 
            var content = preview_img + $('#response_content').val();
            $.post(add_response_do_url,{'fid':fid,'pid':pid,'type':type,'content':content},function(result){
                if(result.data.code==0){
                    if(source=='forum'){
                        listtype = 'guitem_forum';
                    }else if(source='movie_activity'){
                        listtype = 'guitem_movie_activity';
                    }

                    $('.guestlist').append('<div class="'+listtype+'">'+
                    '<h2 data-rid="'+result.data.info.id+'" data-username="'+result.data.info.username+'" class="response_to"><img src="'+result.data.info.userphoto+'">'+result.data.info.username+'</h2>'+
                    '<p>'+
                        result.data.info.content +
                    '</p>'+
                '</div>');
                    $('.guestlist').parseEmotion();
                    $('#response_content').val('');
                    $('.preview_img').html('');
                    $('.sendface').removeClass('sendface_upload');
                    $('.guestlist')[0].scrollTop = $('.guestlist')[0].scrollHeight;

                    
                }
            });
        }else{
            $('.head_login').trigger('click');
        }
    });

    //工作室人搜索
    $('.search_forum_user').keyup(function(e){
        var nickname = $(this).val();
        var fid = $(this).attr('data-fid');
        var length = $('.onlinelist ul').children('li').length;
        console.log(nickname);
        if(nickname==''){
            $('.onlinelist ul').children('li').each(function(){
                $(this).css('display','block');
            });
        }else{
            for(i=0;i<length;i++){
                var current_element = $('.onlinelist ul').children('li').eq(i);
                if(current_element.text().search(nickname)!=-1){
                    current_element.css('display','block');
                }else{
                    current_element.css('display','none');
                }
            }
        }
    });

    //工作室切换在线列表
    $('.fanstab ul li').each(function(index, element) {
        var _this = $(this);
        _this.click(function(e){
            _this.addClass('on').siblings('li').removeClass('on');
            $('.tabwrap').eq(index).show().siblings('.tabwrap').hide();
        });
    });
    $('.piclist ul li:first').css({'width':'528px','height':'323px','margin-bottom':'5px'});
    $('.piclist ul li:first').find('h2').addClass('videofirst')
    $('.piclist ul li').hover(function(){
        $(this).find('h2').stop(true,true).animate({'bottom':'0px'},200);
    },function(){
        $(this).find('h2').stop(true,true).animate({'bottom':'-46px'},200);
    });

    //创建线上活动
    $(document).on('click','.create_online_activity',function(){
        if(is_login=='true'){
            var fid = $(this).attr('data-fid');
            param = 'fid='+fid;
            show_judgement_form_parameter(create_online_activity_url,param);
        }else{
            $('.head_login').trigger('click');
        }
    });

    //创建包场活动
    $(document).on('click','.create_movie_activity',function(){
        if(is_login=='true'){
            var fid = $(this).attr('data-fid');
            param = 'fid='+fid;
            show_judgement_form_parameter(create_movie_activity_url,param);
        }else{
            $('.head_login').trigger('click');
        }
    });

    //创建话题
    $(document).on('click','.create_topic',function(){
        if(is_login=='true'){
            var fid = $(this).attr('data-fid');
            param = 'fid='+fid;
            show_judgement_form_parameter(create_topic_url,param);
        }else{
            $('.head_login').trigger('click');
        }
    });

    //表情
    $('.guestlist').parseEmotion();
    //评论表情
    if($('.argtext').length!=0){
        $('.argtext').parseEmotion();
    }
    
    //选择表情
    $('.choose_face').bind({
        click: function(event){
            if(! $('#sinaEmotion').is(':visible')){
                $(this).sinaEmotion();
                event.stopPropagation();
            }
        }
    });

    //上传图片
    $('.choose_img').click(function(){
        $('#upload_message_image').trigger('click');
    });
    //上传图片到云
    $(document).on("change","#upload_message_image",function(){
        var fid = $(this).attr('data-fid');
        $.ajaxFileUpload({
            url: upload_forumpicture_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'upload_message_image', //文件上传域的ID
            data:{
                
            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                //尝试进行json解析
                if(data.data.code==0){
                    //添加到照片墙
                    $.post(get_attachment_img_url,{'id':data.data.info},function(result){
                        if(result.data.code==0){
                            $('.preview_img').html('<span class="message_preview_span"><img src="'+result.data.info.remote_url+'"></span><span class="delete_preview">删除</span>');
                            $('.sendface').addClass('sendface_upload');
                        }
                    });
                }else{
                    alert(data.data.message)
                }
            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                alert(e);
            }
        });
    });
    //删除即时预览上传图
    $(document).on('click','.delete_preview',function(){
        $('.preview_img').html('');
        $('.sendface').removeClass('sendface_upload');
    });

    //视频上传
    $(document).on('click','.upload_video',function(){
        var call_back_button = $(this).attr('data-call-back-button');
        if(is_login=='true'){
            show_judgement_form_parameter(upload_video_url,'fid='+fid+'&call_back_button='+call_back_button);
        }else{
            $('.head_login').trigger('click');
        }
    });

    //工作室视频上传
    $(document).on('click','.upload_forum_video',function(){
        var videoid = $(this).attr('data-id');
        var title = $(this).attr('data-title');
        if(videoid==0||title==''){
            alert('上传出错');
        }else{
            $.post(add_forum_video_url,{'fid':fid,'title':title,'videoid':videoid},function(){
                
            });
        }
    });

    

    //评论回复
    //检查是否点赞
    $('.vote_response').each(function(){
        var pid = $(this).attr('data-pid');
        var type = $(this).attr('data-type');
        var _this = $(this);
        $.post(check_response_favor_url,{'pid':pid,'type':type},function(result){
            if(result.data.code==0){
                if(result.data.info==true){
                    _this.children('img').attr('src','/Public/default/images/good_28.jpg');
                }
            }
        });
    });

    //点赞
    $(document).on("click",'.vote_response',function(){
        if(is_login=='true'){
            var pid = $(this).attr('data-pid');
            var type = $(this).attr('data-type');
            var _this = $(this);
            var vote_num = parseInt($(this).children('.vote_num').text());
            $.post(add_response_favor_url,{'pid':pid,'type':type},function(result){
                if(result.data.code==0){
                    _this.children('img').attr('src','/Public/default/images/good_28.jpg');
                    _this.children('.vote_num').text(vote_num+1);
                    add_plus_effect(_this.children('b'));
                }else{
                    alert('你已经投过票了~');
                }
            });
        }else{
            $('.head_login').trigger('click');
        }
    });

    
    //评论
    $(document).on('click','.response_to',function(){
        var username = $(this).attr('data-username');
        var rid = $(this).attr('data-rid');
        $('#add_response_do').attr('data-rid',rid);
        $('#response_content').val('@'+username+' ');
        $('#response_content').focus();
    });

    //回复处理
    $(document).on('click','#add_response_do',function(){
        if(is_login=='true'){
            var fid = $(this).attr('data-fid');
            var pid = $(this).attr('data-pid');
            var type = $(this).attr('data-type');
            var rid = $(this).attr('data-rid');
            var preview_img = $('.message_preview_span').html();
            preview_img = (preview_img == null) ? '' : preview_img; 
            var content = preview_img + $('#response_content').val();
            if(content==''){
                alert('回复内容不能为空！');
            }else{
                $.post(add_response_do_html_url,{'fid':fid,'pid':pid,'type':type,'content':content,'rid':rid},function(result){
                    if(result.data.code==0){
                        $('.response_block').prepend(result.data.info);
                        $('#response_content').val('');
                        if($('.argtext').length!=0){
                            $('.argtext').parseEmotion();
                        }
                    }else{
                        alert(result.data.message);
                    }
                });
            }
        }else{
            $('.head_login').trigger('click');
        }
    });

    //表情
    $('.argtext').parseEmotion();
   


});