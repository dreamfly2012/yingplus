function ActivityListTemplate(html,activity_img,acitivity_href,cinemaname,money,enrollnum,enrolltotal,time){
    html += '<li>'
            +'<a href="'+acitivity_href+'"><div class="pic">'
            +'<img src="'+activity_img+'">'
            +'</div></a>'
            +'<div class="txt">'
            +'<div class="line clearfix">'
            +'<a class="name" href="'+acitivity_href+'">'+cinemaname+' </a>'
            +'<span class="price">￥<span>'+money+'</span></span>'
            +'</div>'
            +'<div class="line clearfix">'
            +'<div class="num">已报名人数：<span>'+enrollnum+'/'+enrolltotal +'</span></div>'
            +'<span class="time"><i></i>'+time+'</span>'
            +'</div>'
            +'</div>'
            +'</li>';
    return html;        
}

$(document).ready(function(){
    //轮播图获取
    $.post(get_carousel_url,{mid:mid},function(result){
        var html = [];
        var now = parseInt(((new Date()).getTime()/1000));
        var indexhtml = [];
        for(var i=0; i<result.data.info.movie.mobile_banner_info.length;i++){
            indexhtml.push('<a href="javascript:;">'+(i+1)+'</a>');
            html.push('<li><span class="img-1"></span></li>');
        }
        
        $('#carousel').html(html.join(''));
        $('.flicking-con').html(indexhtml.join(''));
        if(now>result.data.info.movie.releasetime){
            $("#boxoffice").text(result.data.info.box_office+'万');
            $("#boxtext").text('全国票房突破');
        }else{
            var day = parseInt((result.data.info.movie.releasetime-now)/(60*60*24));
            $("#boxoffice").text(day+'天');
            $("#boxtext").text('距离影片上映还有');
        }
        
        for(var i=0; i<result.data.info.movie.mobile_banner_info.length;i++){
            var banner = result.data.info.movie.mobile_banner_info[i];
            $('#carousel').children('li').eq(i).children('span').css({"background-size":"100% 100%","background-image":"url('"+ banner +"')","background-repeat":"no-repeat"});
        }

        
        //轮播插件加载
        $dragBln = false;

        $(".main-image").touchSlider({
            flexible : true,
            speed : 200,
            paging : $(".flicking-con a"),
            counter : function (e){
                $(".flicking-con a").removeClass("on").eq(e.current-1).addClass("on");
            }
        });

        $(".main-image").bind("mousedown", function() {
            $dragBln = false;
        });

        $(".main-image").bind("dragstart", function() {
            $dragBln = true;
        });

        $(".main-image a").click(function(){
            if($dragBln) {
                return false;
            }
        });

        timer = setInterval(function(){
            $("#btn_next").click();
        }, 5000);

        $(".main-visual").hover(function(){
            clearInterval(timer);
        },function(){
            timer = setInterval(function(){
                $("#btn_next").click();
            },5000);
        });

        $(".main-image").bind("touchstart",function(){
            clearInterval(timer);
        }).bind("touchend", function(){
            timer = setInterval(function(){
                $("#btn_next").click();
            }, 5000);
        });
    });

    //包场信息获取
    $.post(get_seminar_url,{mid:mid},function(result){
        if(result.data.info.length!=0){
            var html = "";
            for(var i=0;i<result.data.info.length;i++){
                if(result.data.info[i].fid==16){
                    $(".studio").eq(0).find('img').attr('src',result.data.info[i].forum_img);
                    $(".studio").eq(0).find('.screenings b').text(result.data.info[i].baochang_num);
                    if(result.data.info[i].isfavor==0){
                        $(".studio").eq(0).find('.praise').removeClass('praise').addClass('praise-null');
                        $(".studio").eq(0).find('.praise-null span').text(result.data.info[i].favors);
                    }else{
                        $(".studio").eq(0).find('.praise span').text(result.data.info[i].favors);
                    }
                }
            }
        }
    });

    //包场活动详情获取
    $.post(get_forum_activity_url,{fid:16,mid:mid},function(result){
        var infolist = result.data.info;
        if(infolist.length!=0){
            var html = "";
            for(var i=0;i<infolist.length;i++){
                var time = new Date(parseInt(infolist[i].holdstart)*1000).toLocaleDateString();
                html = ActivityListTemplate(html,infolist[i].movieimg,infolist[i].href,infolist[i].cinemaname,infolist[i].money,infolist[i].enrollnum,infolist[i].enrolltotal,time);
            }
            $(".screen-list").eq(0).html(html);
        }
    });

    //支持
    $(".zhichi").click(function () {
        var sid = $(this).attr('data-sid');
        var val = parseInt($(this).find('span').text());
        var $this = $(this);
        if($this.hasClass('praise')){
            return false;
        }
        $.post(zhichi_url,{sid:sid},function(result){
            if(is_login=='false'){
                jump_login_page();
            }else{
                $this.removeClass('praise-null').addClass('praise').find('span').text(val+1);
            }
        });

    });

    //创建活动
    $("#create_activity").click(function(){
        if(is_login=='false'){
            jump_login_page();
        }else{
            window.location.href = create_activity_url+'?mid='+mid + '&fid=16';
        }
    });

});