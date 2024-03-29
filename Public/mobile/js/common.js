//判断设备
function isIOS() {
    var u = navigator.userAgent;
    var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Adr') > -1; //android终端
    var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
    if (isiOS) {
        return true;
    } else {
        return false;
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

function trim(str) { //删除左右两端的空格
    　
    return str.replace(/(^\s*)|(\s*$)/g, "");
}　
function ltrim(str) { //删除左边的空格
    　
    return str.replace(/(^\s*)/g, "");
}　
function rtrim(str) { //删除右边的空格
    　
    return str.replace(/(\s*$)/g, "");
}

//登录
function jump_login_page() {
    var returnurl = encodeURI(window.location.href);
    window.location.href = login_url + '?returnurl=' + returnurl;
}

//获取浏览器URL中的参数
function getParameterValue(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20')) || null
}

//只能输入数字
function onlyNum() {
    if (!(event.keyCode == 46) && !(event.keyCode == 8) && !(event.keyCode == 37) && !(event.keyCode == 39))
        if (!((event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 96 && event.keyCode <= 105)))
            event.returnValue = false;
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




//分享
function share_qq(url, share_id, title, desc, summary, site, pics) {
    var share_url = 'http://connect.qq.com/widget/shareqq/index.html?url=' + url + '&share_id=' + share_id + '&title=' + title + '&desc=' + desc + '&summary=' + summary + '&site=' + site + '&pics=' + pics;
    window.location.href = share_url;
}

function share_weibo(url, share_id, title, appkey, pic) {
    //255864200
    var share_url = 'http://service.weibo.com/share/share.php?url=' + url + 'share_id=' + share_id + '&title=' + title + '&appkey=' + appkey + '&pic=' + pic + '&searchPic=true';
    window.location.href = share_url;
}

function share_qzone(url, share_id, title, desc, summary, site, pics) {
    var share_url = 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' + url + 'share_id=' + share_id + '&title=' + title + '&desc=' + desc + '&summary=' + summary + '&site=' + site + '&pics=' + pics;
    window.location.href = share_url;
}

function share_weixin(url) {
    $("body").append("<div id=\"weixin_qrcode_dialog\" " +
        "style=\"position: absolute;left:50%;top:50%;padding: 10px;width: 240px;height: 300px;background: #fff;border: solid 1px #d8d8d8;z-index: 11001;font-size: 12px;\" >" +
        "<div><span style=\"float:left;\">分享到微信朋友圈</span>" +
        "<a class=\"weixin_qrcode_close\" style=\"float:right;\" onclick=\"return false;\" href=\"javascript:;\">×</a>" +
        "</div><div style=\"margin-top: 40px; margin-left: 20px;\" id=\"weixin_qr_generate\"></div>" +
        "<div style=\"margin-top: 20px;\">打开微信，点击底部的“发现”，<br>使用“扫一扫”即可将网页分享至朋友圈。</div></div>");
    $("#weixin_qrcode_dialog").css('display', 'block');
    $("#weixin_qr_generate").qrcode({
        render: "table",
        width: 200,
        height: 200,
        text: url,
        correctLevel: 2
    });

    $(".weixin_qrcode_close").click(function() {
        $("#weixin_qrcode_dialog").hide();
    });
}


function base64_encode(str) {
    var c1, c2, c3;
    var base64EncodeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
    var i = 0,
        len = str.length,
        string = '';

    while (i < len) {
        c1 = str.charCodeAt(i++) & 0xff;
        if (i == len) {
            string += base64EncodeChars.charAt(c1 >> 2);
            string += base64EncodeChars.charAt((c1 & 0x3) << 4);
            string += "==";
            break;
        }
        c2 = str.charCodeAt(i++);
        if (i == len) {
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

function base64_decode(str) {
    var c1, c2, c3, c4;
    var base64DecodeChars = new Array(-1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63, 52, 53, 54, 55, 56, 57,
        58, 59, 60, 61, -1, -1, -1, -1, -1, -1, -1, 0, 1, 2, 3, 4, 5, 6,
        7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24,
        25, -1, -1, -1, -1, -1, -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36,
        37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1
    );
    var i = 0,
        len = str.length,
        string = '';

    while (i < len) {
        do {
            c1 = base64DecodeChars[str.charCodeAt(i++) & 0xff]
        } while (
            i < len && c1 == -1
        );

        if (c1 == -1) break;

        do {
            c2 = base64DecodeChars[str.charCodeAt(i++) & 0xff]
        } while (
            i < len && c2 == -1
        );

        if (c2 == -1) break;

        string += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));

        do {
            c3 = str.charCodeAt(i++) & 0xff;
            if (c3 == 61)
                return string;

            c3 = base64DecodeChars[c3]
        } while (
            i < len && c3 == -1
        );

        if (c3 == -1) break;

        string += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));

        do {
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

function later_alert() {
    swal('即将上映,敬请期待~');
}


$(document).ready(function() {
    //导航
    $('.hamburger').click(function() {
        $(this).toggleClass('is-active');
        $('.nav').toggleClass('menuanimate');
        $('.alphabg').toggleClass('menuanimate');
    });

    $('.alphabg').click(function() {
        $('.hamburger').removeClass('is-active');
        $('.nav').removeClass('menuanimate');
        $(this).removeClass('menuanimate');
    });

    //分享关闭
    $('.close_share').click(function() {
        $('.share').css('display', 'none');
    });
    //打开分享按钮
    $('.open_share').click(function() {
        $('.share').css('display', 'block');
    });
    //登陆
    $(document).on('click', ".head_login", function() {
        window.location.href = login_form_url;
    });
    //注册
    $(document).on('click', ".head_sign", function() {
        window.location.href = register_form_url;
    });
    //忘记密码
    $(document).on('click', ".forget_password", function() {
        window.location.href = forgetpassword_form_url;
    });
    //QQ登陆
    $(document).on('click', ".qq_login", function() {
        var backurl = getParameterValue('returnurl') == null ? 'http://yingplus.80shihua.com' : getParameterValue('returnurl');
        backurl = base64_encode(backurl);
        window.location.href = "https://graph.qq.com/oauth2.0/authorize?client_id=101543335&response_type=code&display=mobile&redirect_uri=http://yingplus.80shihua.com/Login/loginByQQ/backurl/" + backurl;
    });
    //微博登陆
    $(document).on('click', ".weibo_login", function() {
        var backurl = getParameterValue('returnurl');
        window.location.href = "https://api.weibo.com/oauth2/authorize?client_id=3573788978&redirect_uri=http://yingplus.80shihua.com/Login/loginByWeibo?backurl=" + backurl;
    });
    //fancybox
    $('.fancybox').fancybox();
    $('.guitem_movie_activity p img').fancybox({
        'afterClose': function() {
            $('.guitem_movie_activity p img').css('display', 'block');
        }
    });


    $('.response_content img').fancybox({
        'afterClose': function() {
            $('.response_content img').css('display', 'block');
        }
    });

    $('.poster_img img').fancybox({
        'afterClose': function() {
            $('.poster_img img').css('display', 'block');
        }
    });



    //加入工作室
    $(document).on('click', '.joinforum', function() {
        var $this = $(this);
        if (is_login == 'true') {
            var fid = $(this).attr('data-fid');
            $.post(join_forum_url, { 'fid': fid }, function(result) {
                if (result.data.code == 0) {
                    window.location.reload();
                } else {
                    swal(result.data.message);
                }
            });
        } else {
            jump_login_page();
        }
    });


    //退出工作室
    $(document).on('click', '.exitforum', function() {
        var $this = $(this);
        if (is_login == 'true') {
            var fid = $(this).attr('data-fid');
            $.post(exit_forum_url, { 'fid': fid }, function(result) {
                if (result.data.code == 0) {
                    $this.text('+ 加 入');
                    window.location.reload();
                } else {
                    swal(result.data.message);
                }
            });
        } else {
            $('.head_login').trigger('click');
        }
    });


    //工作室图片上传
    $(document).on('click', '.upload_forumpicture', function() {
        if (is_login == 'true') {
            $("#upload_forumpicture").trigger('click');
        } else {
            jump_login_page();
        }
    });

    //上传图片到云
    $(document).on('change', "#upload_forumpicture", function() {
        var fid = $(this).attr('data-fid');
        $.ajaxFileUpload({
            url: upload_forumpicture_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'upload_forumpicture', //文件上传域的ID
            data: {

            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function(data, status) //服务器成功响应处理函数
                {
                    //尝试进行json解析
                    if (data.data.code == 0) {
                        //添加到照片墙
                        $.post(add_forumpicture_url, { 'attachmentid': data.data.info, 'fid': fid }, function(result) {
                            if (result.data.code == 0) {
                                swal('上传成功');
                                window.location.reload();
                            } else {
                                swal(result.data.message);
                            }
                        });

                    } else {
                        alert(data.data.message)
                    }
                },
            error: function(data, status, e) //服务器响应失败处理函数
                {
                    swal(e);
                }
        });
    });

    //创建线上活动
    $(document).on('click', '.create_online_activity', function() {
        if (is_login == 'true') {
            var fid = $(this).attr('data-fid');
            $.post(check_in_forum_url, { 'fid': fid }, function(result) {
                if (result.data.code == 0) {
                    if (result.data.info.status == true) {
                        window.location.href = create_online_activity_url + '?fid=' + fid;
                    } else {
                        swal({ 'title': result.data.message }, function() {
                            $(window).scrollTop(0); //滚动到加入按钮位置
                        });
                    }

                } else {
                    swal({ 'title': result.data.message }, function() {
                        $(window).scrollTop(0); //滚动到加入按钮位置
                    });
                }
            });

        } else {
            jump_login_page();
        }
    });

    //创建包场活动
    $(document).on('click', '.create_movie_activity', function() {
        if (is_login == 'true') {
            var fid = $(this).attr('data-fid');
            $.post(get_crete_movie_activity_url, { 'fid': fid }, function(result) {
                if (result.data.code == 0) {
                    window.location.href = result.data.info;
                } else {
                    swal({ 'title': result.data.message }, function() {
                        $(window).scrollTop(0); //滚动到加入按钮位置
                    });
                }
            });

        } else {
            jump_login_page();
        }
    });

    //创建话题
    $(document).on('click', '.create_topic', function() {
        if (is_login == 'true') {
            var fid = $(this).attr('data-fid');
            $.post(get_create_topic_url, { 'fid': fid }, function(result) {
                if (result.data.code == 0) {
                    window.location.href = result.data.info;
                } else if (result.data.code == 2) {
                    jump_login_page();
                } else {
                    swal({ 'title': result.data.message }, function() {
                        $(window).scrollTop(0); //滚动到加入按钮位置
                    });

                }
            });
        } else {
            jump_login_page();
        }
    });

    //视频上传
    $('.upload_video').click(function() {
        if (is_login == 'true') {
            window.location.href = upload_video_url + '?fid=' + fid;
        } else {
            jump_login_page();
        }
    });


    //工作室视频上传
    $(document).on('click', '.upload_forum_video', function() {
        var videoid = $(this).attr('data-id');
        var title = $(this).attr('data-title');
        if (videoid == 0 || title == '') {
            swal('上传出错');
        } else {
            $.post(add_forum_video_url, { 'fid': fid, 'title': title, 'videoid': videoid }, function() {

            });
        }
    });


    //检查是否点赞
    $('.vote_response').each(function() {
        var pid = $(this).attr('data-pid');
        var type = $(this).attr('data-type');
        var _this = $(this);
        $.post(check_favor_url, { 'pid': pid, 'type': type }, function(result) {
            if (result.data.code == 0) {
                if (result.data.info == true) {
                    _this.children('img').attr('src', '/Public/mobile/images/heart_2.png');
                }
            }
        });
    });

    //点赞
    $(document).on("click", '.vote_response', function() {
        if (is_login == 'true') {
            var pid = $(this).attr('data-pid');
            var type = $(this).attr('data-type');
            var _this = $(this);
            var vote_num = parseInt($(this).children('.vote_num').text());
            $.post(add_favor_url, { 'pid': pid, 'type': type }, function(result) {
                if (result.data.code == 0) {
                    _this.children('img').attr('src', '/Public/mobile/images/heart_2.png');
                    _this.children('.vote_num').text(vote_num + 1);
                } else {
                    swal('你已经投过票了~');
                }
            });
        } else {
            jump_login_page();
        }
    });

    //表情
    $('.guestlist').parseEmotion();

    //返回顶部
    // browser window scroll (in pixels) after which the "back to top" link is shown
    var offset = 300,
        //browser window scroll (in pixels) after which the "back to top" link opacity is reduced
        offset_opacity = 1200,

        //grab the "back to top" link
        $back_to_top = $('.cd-top');

    //hide or show the "back to top" link
    $(window).scroll(function() {
        ($(this).scrollTop() > offset) ? $back_to_top.addClass('cd-is-visible'): $back_to_top.removeClass('cd-is-visible cd-fade-out');
        if ($(this).scrollTop() > offset_opacity) {
            $back_to_top.addClass('cd-fade-out');
        }
    });

    //smooth scroll to top
    $back_to_top.on('click', function(event) {
        event.preventDefault();
        $('body,html').scrollTop(0);
    });
});
