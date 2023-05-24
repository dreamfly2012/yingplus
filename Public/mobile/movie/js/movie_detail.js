function add_comment(content) {
    $.post(add_activity_response_url, {
        'content': content
    }, function (result) {
        if (result.data.code == 0) {
            var arr = [];
            arr.push('<div class="mine" data-id="'+result.data.info.id+'">' + '<div class="name">' + result.data.info.username + '</div>' + '<div class="clearfix">' + '<img class="avatar" src="' + result.data.info.userphoto + '"/>' + '<div class="bubble">' + result.data.info.content + '</div>' + '</div>' + '</div>');
            $("#message_box_content").append(manhuaReplace(arr.join('')));
            $('#message_box_content').scrollTop($('#message_box_content')[0].scrollHeight);
            $("#content").val('');
        } else if (result.data.code == 2) {
            jump_login_page();
        }
    });
}

function get_init_comment() {
    $.post(get_activity_response_url, {
        'id': aid,
        'number': 10
    }, function (result) {
        if (result.data.code == 0) {
            var length = result.data.info.length;
            var arr = [];
            if(length!=0){
                $("#message_bar_username").html(result.data.info[0].username);
                $("#message_bar_content").html(result.data.info[0].content.replace(/<[^>]+>/g, ""));
                for (var i = length - 1; i >= 0; i--) {
                    var classname = (result.data.info[i].uid == uid) ? 'mine' : 'other';
                    arr.push('<div class="' + classname + '" data-id="'+result.data.info[i].id+'">' + '<div class="name">' + result.data.info[i].username + '</div>' + '<div class="clearfix">' + '<img class="avatar" src="' + result.data.info[i].userphoto + '"/>' + '<div class="bubble">' + result.data.info[i].content + '</div>' + '</div>' + '</div>');
                }
                $("#message_box_content").html(manhuaReplace(arr.join('')));
             }
        }
    });
}

function get_new_comment(){
    $.post(get_activity_response_url, {
        'id': aid,
        'number': 10,
        'lastid':$("#message_box_content").children('div:last').attr('data-id')
    }, function (result) {
        if (result.data.code == 0) {
            var length = result.data.info.length;
            var arr = [];
            if(length!=0){
                $("#message_bar_username").html(result.data.info[0].username);
                $("#message_bar_content").html(result.data.info[0].content.replace(/<[^>]+>/g, ""));
                for (var i = length - 1; i >= 0; i--) {
                    var classname = (result.data.info[i].uid == uid) ? 'mine' : 'other';
                    arr.push('<div class="' + classname + '" data-id="'+result.data.info[i].id+'">' + '<div class="name">' + result.data.info[i].username + '</div>' + '<div class="clearfix">' + '<img class="avatar" src="' + result.data.info[i].userphoto + '"/>' + '<div class="bubble">' + result.data.info[i].content + '</div>' + '</div>' + '</div>');
                }
                $("#message_box_content").append(manhuaReplace(arr.join('')));
            }
        }
    });
}

function scrollBottom() {
    $('#message_box_content').scrollTop($('#message_box_content')[0].scrollHeight);
}

$(document).ready(function () {
    //获取活动详情
    $.post(get_activity_info_url, {
        'id': aid
    }, function (result) {
        if (result.data.info.length != 0) {
            var arr = [];
            arr.push('<div class="name">' + result.data.info.cinemaname + '<span class="price" href="#"><em>￥</em>' + result.data.info.ticketprice + '</span>' + '</div>' + '<div class="address">' + result.data.info.detailaddress_format + '</div>' + '<div class="other">' + '人限：<span>' + result.data.info.enrollnum + ' / ' + result.data.info.enrolltotal + '</span>  时间：<span>' + result.data.info.holdstart_format + '</span>' + '</div>' + '<div class="other">' + '发起人：<i>' + result.data.info.sponsor + '</i>' + '</div>');
            $("#activity_info").html(arr.join(''));
            $("#activity_rule").html(result.data.info.activity_rule);
            $(".forumname").html(result.data.info.forumname + '工作室');
            $("#movie_title").html(result.data.info.movie_title);
            if (uid != result.data.info.uid) $("#scan-code").addClass('hide');
        }
    });
    //根据用户,获取活动状态显示
    $.post(get_activity_status_url, {
        'id': aid
    }, function (result) {
        if (result.data.code == 0) {
            var time = parseInt(((new Date()).getTime() / 1000));
            var holdstart = result.data.info.holdstart;
            var enrollendtime = result.data.info.enrollendtime;
            var isenroll = result.data.info.isenroll;
            if (time < enrollendtime) {
                //未开始，未报名
                if (isenroll == 0) {
                    $("#enroll").text('报名');
                } else {
                    $("#enroll").text('再来一张');
                }
            } else if (time < holdstart) {
                $("#enroll").addClass('end');
                $("#enroll").text('停止报名');
                $("#enroll").removeAttr('id');
            } else {
                $("#enroll").addClass('end');
                $("#enroll").text('已结束');
                $("#enroll").removeAttr('id');
            }
        }
    });
    //即时聊天框定位
    var height = $(window).height();
    height = height - 27;
    $(".fixed-message").css("bottom", '-' + height + 'px');
    //展开即时聊天框
    scrollBottom();
    $("#pen").click(function () {
        if ($(".fixed-message").css("z-index") == "2") {
            $(".fixed-message").animate({
                bottom: '0rem'
            });
            $(".fixed-message").css('z-index', '3');
            scrollBottom();
        } else {
            $(".fixed-message").animate({
                bottom: '-' + height
            });
            $(".fixed-message").css('z-index', '2');
        }
    });
    //报名活动
    $(document).on("click","#enroll",function () {
        if (is_login == 'false') {
            jump_login_page();
        } else {

            window.location.href = pay_url;
        }
    });
    //即时通讯,页面加载滚动条到底部
    get_init_comment();//立即执行一次
    setInterval("get_new_comment()",6000);

    //添加评论
    $("#content").keydown(function(event){
        if (event.ctrlKey && (event.keyCode == 13 || event.keyCode == 10)) {
            var str = $('#content').val();
            $('#content').val(str+'\r\n');
        } else if(!event.ctrlKey && !event.shiftKey && (event.keyCode == 13 || event.keyCode ==10)){
            event.returnValue = false;
            $("#send").trigger('click');
            return false;
        }
    });
    $("#send").click(function () {
        var content = $("#content").val();
        if (content == "") {
            alert('评论内容不能为空');
        } else {
            add_comment(content);
        }
    });

    //选择表情
    $("#choose_face").manhuaHtmlArea({
        Event: "click",
        Left: -75,
        Top: -200,
        id: "content"
    });


    //工作室加入/退出
    $.post(get_forum_user_url, {'fid': fid}, function (result) {
        if (result.data.code == 0) {
            if (result.data.info.forum_info != null) {
                if (result.data.info.forum_info.status == 0) {
                    $("#forum-join").attr('id', 'forum-exit').removeClass('join').addClass('joined').text('已加入');
                }
            }
        }
    });

    //加入工作室
    $(document).on('click', '#forum-join', function () {
        if (is_login == 'false') {
            jump_login_page();
        } else {
            $.post(join_forum_url, {'fid': fid}, function (result) {
                if (result.data.code == 0) {
                    $("#forum-join").attr('id', 'forum-exit').removeClass('join').addClass('joined').text('已加入');
                } else {
                    alert(result.data.message);
                }
            });
        }
    });

    //退出工作室
    $(document).on('click', '#forum-exit', function () {
        if (is_login == 'false') {
            jump_login_page();
        } else {
            $.post(exit_forum_url, {'fid': fid}, function (result) {
                if (result.data.code == 0) {
                    $("#forum-exit").attr('id', 'forum-join').removeClass('joined').addClass('join').text('加入');
                } else {
                    alert(result.data.message);
                }
            });
        }
    });

    $("#scan-code").click(function () {
        window.location.href = scan_code_url;
    });

    //发送图片
    $("#choose_img").click(function () {
        $("#send_img").trigger('click');
    });

    //发送图片
    $(document).on('change', "#send_img", function () {
        $.ajaxFileUpload({
            url: upload_face_img_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'send_img', //文件上传域的ID
            data: {
                aid: $("#send_img").attr('data-aid')
            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                if (data.data.code == 0) {
                    $.post(get_attachment_url, {'id': data.data.info}, function (result) {
                        add_comment('<img src="' + result.data.info.path_format + '" />');
                    })
                } else {
                    alert(data.data.message);
                }
            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                alert(e);
            }
        });
    });

    $('#feedback').click(function(){
        window.location.href = feedback_url;
    });

});
