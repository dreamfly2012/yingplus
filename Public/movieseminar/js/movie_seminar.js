var danmu_interval;
function send() {
    var arr = ['#00f', '#0f0', '#f00'];
    var text = document.getElementById('text').value;
    var color = arr[Math.floor(Math.random() * arr.length)];
    var position = document.getElementById('position').value;
    var time = $('#danmu').data("nowtime") + 5;
    var size = document.getElementById('text_size').value;
    var text_obj = '{ "text":"' + text + '","color":"' + color + '","size":"' + size + '","position":"' + position + '","time":' + time + '}';
    $.post(add_comment_url, {
        content: text,
        order:time,
        mid:mid
    });
    var text_obj = '{ "text":"' + text + '","color":"' + color + '","size":"' + size + '","position":"' + position + '","time":' + time + ',"isnew":""}';
    var new_obj = eval('(' + text_obj + ')');
    $('#danmu').danmu("add_danmu", new_obj);
    document.getElementById('text').value = '';
}


$(document).ready(function () {
    $("#danmu").danmu({
        left: 0,
        top: 0,
        height: "100%",
        width: "100%",
        speed: 20000,
        //danmuss:danmuss,
        nowtime:0,
        opacity: 1,
        font_size_small: 16,
        font_size_big: 24,
        top_botton_danmu_time: 6000
    });


    function timedCount(){
        var time = $('#danmu').data("nowtime");

        if(time>maxtime){
            getDanmu();
        }

        t=setTimeout(function(){timedCount();},50);

    }
    getDanmu();
    timedCount();

    function getDanmu() {
        $('#danmu').danmu("danmu_stop");
        $('#danmu').danmu("danmu_resume");
        $.ajax({
            url: get_comment_url,
            data: {
                "mid": mid
            },
            type: 'post',
            cache: false,
            dataType: 'json',
            async: false,
            success: function (data) {
                if (data.data.code == 0) {
                    for (var i = 0; i < data.data.info.length; i++) {
                        var color_arr = ['#00f', '#0f0', '#f00'];
                        var color = color_arr[Math.floor(Math.random() * color_arr.length)];
                        var danmuobj = {};
                        danmuobj.text = data.data.info[i].content;
                        danmuobj.color = color;
                        danmuobj.size = 0;
                        danmuobj.position = 0;
                        danmuobj.time = data.data.info[i].order;
                        (uid == data.data.info[i].uid) ? danmuobj.isnew = '' : '';
                        danmuss[i] = danmuobj;
                        $('#danmu').danmu("add_danmu", danmuobj);
                    }
                }
            },
            error: function () {
                console.log("异常！");
            }
        });
    }




    //初始化弹幕
    $('#danmu').danmu('danmu_start');

    var key_arr = {0:'one',1:'two',2:'three',3:'four',4:'five',5:'six'};

    //初始化热门城市
    //吴亦凡
    $.post(get_hot_province_url,{fid:16,mid:mid},function(result){
        if(result.data.info.length!=0){
            var html = "";
            for(var i=0;i<result.data.info.length;i++){
                html += '<div class="p city_public '+ key_arr[i] +' city" data-pid="'+result.data.info[i].id +'" data-fid="'+result.data.info[i].fid+'" data-mid="'+ result.data.info[i].mid +'">'+result.data.info[i].name+'</div>';
            }
            $(".citybox_one").html(html);
        }
    });
    //韩庚
    $.post(get_hot_province_url,{fid:4,mid:mid},function(result){
        if(result.data.info.length!=0){
            var html = "";
            for(var i=0;i<result.data.info.length;i++){
                html += '<div class="p city_public '+ key_arr[i] +' city" data-pid="'+result.data.info[i].id +'" data-fid="'+result.data.info[i].fid+'" data-mid="'+ result.data.info[i].mid +'">'+result.data.info[i].name+'</div>';
            }
            $(".citybox_two").html(html);
        }
    });



     //省份悬浮弹出影院
    $(document).on('click','.citybox .city',function () {
        var $this = $(this);
        var pid = $(this).attr('data-pid');
        var mid = $(this).attr('data-mid');
        var fid = $(this).attr('data-fid');
        $.post(get_activity_info_url,{fid:fid,mid:mid,pid:pid},function(result){
            if(result.data.info.length!=0){
                var html = [];
                //console.log(result.data);
                for(var i=0;i<result.data.info.length;i++){
                    html.push('<li class="p optionbox_same" >'
                                +'<a href="'+result.data.info[i].href+'">'
                                    +'<div class="cinema_one">'
                                        +'<div class="p topic">'
                                            +result.data.info[i].cinemaname
                                            +'<img class="more" src="/Public/movieseminar/img/more.png"/>'
                                        +'</div>'
                                    +'</div>'
                                    +'<div class="cinema_two">'
                                        +'<div class="p content_same content_one ">'
                                            +'城市'
                                        +'</div>'
                                        +'<div class="p content_same content_two">'
                                            +'人限'
                                        +'</div>'
                                        +'<div class="p content_same content_one">'
                                            +'票价'
                                        +'</div>'
                                    +'</div>'
                                    +'<div class="cinema_three">'
                                        +'<div class="p data_same data_one">'
                                            +result.data.info[i].city
                                        +'</div>'
                                        +'<div class="p data_same data_two">'
                                            +'余'+result.data.info[i].enrollnum+'/'+result.data.info[i].enrolltotal
                                            +'<img class="wei" src="/Public/movieseminar/img/wei.png"/>'
                                        +'</div>'
                                        +'<div class="p data_same data_one">'
                                            +result.data.info[i].money+'元'
                                        +'</div>'
                                    +'</div>'
                                +'</a>'
                            +'</li>');



                    
                }
                $this.parents('.citybox').siblings('.show_province').children('ul').html(html.join(''));
            }


        });

        $(this).parents('.citybox').siblings(".show_province").show();
    });

    //查询省份包场
    $(".search").keyup(function(e){
        var $this = $(this);
        var keyword = $this.val();
        var fid = $(this).attr('data-fid');
        var mid = $(this).attr('data-mid');
        if (e.keyCode != 13) {
            $.post(get_province_url,{keyword:keyword,fid:fid,mid:mid},function(result){
                if(result.data.info.length!=0){
                    var html = "";
                    for(i=0;i<result.data.info.length;i++){
                        html += '<div class="p pulldown_inside" data-pid="'+result.data.info[i].id +'" data-fid="'+result.data.info[i].fid +'" data-mid="'+result.data.info[i].mid +'">'+result.data.info[i].name+'</div>';
                    }
                    $this.siblings(".pulldown").html(html);
                    $this.siblings(".pulldown").show();
                }
            });
        }
    });
    //点击搜索弹出下拉  隐藏下拉
    $('.search').focus(function () {

    });

    $(document).on('click',"body:not(.search)",function () {
        if ($(".search").val() != '') {
            $(".search").siblings(".pulldown").hide();
        }
    });
    //点击下拉在搜索框显示下拉内容
    $(document).on("click",".pulldown_inside",function () {
        var $this = $(this);
        var text = $(this).text();
        var fid = $(this).attr('data-fid');
        var mid = $(this).attr('data-mid');
        var pid = $(this).attr('data-pid');
        $(this).parent("div").siblings('.search').val(text);
        $.post(get_activity_info_url,{fid:fid,mid:mid,pid:pid},function(result){
            if(result.data.info.length!=0){
                var html = [];
                //console.log(result.data);
                for(var i=0;i<result.data.info.length;i++){
                    html.push('<li class="p optionbox_same" >'
                                +'<a href="'+result.data.info[i].href+'">'
                                    +'<div class="cinema_one">'
                                        +'<div class="p topic">'
                                            +result.data.info[i].cinemaname
                                            +'<img class="more" src="/Public/movieseminar/img/more.png"/>'
                                        +'</div>'
                                    +'</div>'
                                    +'<div class="cinema_two">'
                                        +'<div class="p content_same content_one ">'
                                            +'城市'
                                        +'</div>'
                                        +'<div class="p content_same content_two">'
                                            +'人限'
                                        +'</div>'
                                        +'<div class="p content_same content_one">'
                                            +'票价'
                                        +'</div>'
                                    +'</div>'
                                    +'<div class="cinema_three">'
                                        +'<div class="p data_same data_one">'
                                            +result.data.info[i].city
                                        +'</div>'
                                        +'<div class="p data_same data_two">'
                                            +'余'+result.data.info[i].enrollnum+'/'+result.data.info[i].enrolltotal
                                            +'<img class="wei" src="/Public/movieseminar/img/wei.png"/>'
                                        +'</div>'
                                        +'<div class="p data_same data_one">'
                                            +result.data.info[i].money+'元'
                                        +'</div>'
                                    +'</div>'
                                +'</a>'
                            +'</li>');
                }
                $this.parents('.pulldown').siblings('.show_province').children('ul').html(html.join(''));
                $this.parents('.pulldown').siblings('.show_province').show();

            }else{
                var html = "";
                //console.log(result.data);

                html += '<li>'
                            +'<div class="none" style="display:block;!important">'
                                +'<div class="p none_cinema">'
                                    +'该省份还没有已发起的包场'
                                +'</div>'
                                +'<div class="none_content">'
                                    +'<div class="p none_join create_movie_activity" data-pid="'+pid+'" data-mid="'+mid+'" data-fid="'+fid+'">'
                                        +'点击发起包场'
                                    +'</div>'
                                +'</div>'
                            +'</div>'
                        +'</li>';

                $this.parents('.pulldown').siblings('.show_province').children('ul').html(html);
                $this.parents('.pulldown').siblings('.show_province').show();
            }


        });

        $(this).parents('.citybox').siblings(".show_province").show();
        $(".search").siblings(".pulldown").hide();
    });

    //基本信息countdown
    $.ajax({
        url: get_basicinfo_url,
        data: {
            mid:mid
        },
        type: 'post',
        cache: false,
        dataType: 'json',
        success: function (data) {
            if (data.data.code == 0) {
                var countdown = parseInt(((new Date(parseInt(data.data.info.releasetime) * 1000))-(new Date()))/(1000*3600*24));
                countdown = countdown.toString();
                var a = countdown.substr(1,1); //个位
                var b = countdown.substr(0,1); //十位数
                $("#countdown").children('div').eq(0).addClass('countdown_'+b);
                $("#countdown").children('div').eq(1).addClass('countdown_'+a);
            }
        },
        error: function () {
            console.log("异常！");
        }
    });


    //支持&场数
    //获取支持赞数
    $.ajax({
        url: get_seminar_url,
        data: {
            mid:mid
        },
        type: 'post',
        cache: false,
        dataType: 'json',
        async: false,
        success: function (data) {
            if (data.data.code == 0) {
                for (var i = 0; i < data.data.info.length; i++) {
                    if(data.data.info[i].fid==4){
                        $(".zhichi_1").text(data.data.info[i].favors);
                        if(data.data.info[i].isfavor==1){
                            $(".zhichi_1").removeClass('zhichi').addClass('zhichi_clicked');
                        }
                        var baochang_num = data.data.info[i].baochang_num;
                        var a;
                        var b;
                        if(baochang_num<10){
                            a = baochang_num;
                            b = 0;
                        }else{
                            a = baochang_num.substr(1,1); //个位
                            b = baochang_num.substr(0,1); //十位数
                        }
                        $(".changci_down").children('div').eq(0).addClass('baochang_'+b);
                        $(".changci_down").children('div').eq(1).addClass('baochang_'+a);
                    }else if(data.data.info[i].fid==16){
                        $(".zhichi_2").text(data.data.info[i].favors);
                        if(data.data.info[i].isfavor==1){
                            $(".zhichi_2").removeClass('zhichi').addClass('zhichi_clicked');
                        }
                        var baochang_num = data.data.info[i].baochang_num;
                        var a;
                        var b;
                        if(baochang_num<10){
                            a = baochang_num;
                            b = 0;
                        }else{
                            a = baochang_num.substr(1,1); //个位
                            b = baochang_num.substr(0,1); //十位数
                        }
                        $(".changci_up").children('div').eq(0).addClass('baochang_'+b);
                        $(".changci_up").children('div').eq(1).addClass('baochang_'+a);
                    }
                }
            }
        },
        error: function () {
            console.log("异常！");
        }
    });

    $(".zhichi").click(function () {
        var sid = $(this).attr('data-sid');
        var val = parseInt($(this).text());
        var $this = $(this);
        if($this.hasClass('zhichi_clicked')){
            return false;
        }
        $.post(zhichi_url,{sid:sid},function(result){
            if(is_login=='false'){
                show_login();
            }else{
                show_short_message(result.content);
                $this.removeClass('zhichi').addClass('zhichi_clicked').text(val+1);
            }
        });
        
    });

    //回车键与输入按钮绑定
    $(document).on('focus', '.input_danmu', function () {
        $(document).keydown(function (event) {
            if (event.keyCode == 13) {
                $("#enter").click();
            }
        });
    });

    $(document).on('focus', '.search', function () {
        $(document).keydown(function (event) {
            if (event.keyCode == 13) {
                $("#search_button_one").click();
            }
        });
    });

    //创建普通包场活动
    $(document).on('click',".create_movie_activity",function(){
        var $div = $(this).parent('div').next('div').children(':first');
        var fid = $(this).attr('data-fid');
        var mid = $(this).attr('data-mid');
        if(is_login=='false'){
            show_login();
        }else{
            $.post(create_movie_activity_url,{fid:fid,mid:mid},function(result){
                if(result.status==0){
                    show_content(result.info);
                }else if(result.status==1){
                    show_short_message('请先加入工作室');
                }

            });
        }

    });
}); 