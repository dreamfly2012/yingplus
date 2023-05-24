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

    //获取所有包场电影
    get_all_forum_activity();

    //工作室包场电影
    function get_all_forum_activity(){
    	$.post(get_activity_list_url,{'fid':fid,'mid':mid},function(result){
	    	if(result.data.code==0){
	    		var html = [];
	    		for(var i=0;i<result.data.info.length;i++){
	    			html.push('<li>'
	            	+'<div class="pic">'
	                	+'<a href="'+result.data.info[i].href+'"><img src="'+result.data.info[i].imgsrc+'"></a>'
	                +'</div>'
	                +'<div class="txt">'
	                	+'<div class="line clearfix">'
	                    	+'<a class="name" href="'+result.data.info[i].href+'">'+ result.data.info[i].cinemaname + '</a>'
	                        +'<span class="price">￥'+result.data.info[i].ticketprice+'</span>'
	                    +'</div>'
	                    +'<div class="line clearfix">'
	                    	+'<div class="num">已报名人数：<span>'+result.data.info[i].enrollnum+'/'+result.data.info[i].enrolltotal+'</span></div>'
	                        +'<span class="time"><i></i>'+result.data.info[i].holdstart_format+'</span>'
	                    +'</div>'
	                +'</div>'
	            +'</li>');
	    		}
	    		$("#activity_list").html(html.join(''));
	    	}
	    });
    }

    function get_forum_activity(keyword){
    	$.post(get_activity_list_url,{'fid':fid,'mid':mid,'keyword':keyword},function(result){
	    	if(result.data.code==0){
	    		var html = [];
	    		for(var i=0;i<result.data.info.length;i++){
	    			html.push('<li>'
	            	+'<div class="pic">'
	                	+'<a href="'+result.data.info[i].href+'"><img src="'+result.data.info[i].imgsrc+'"></a>'
	                +'</div>'
	                +'<div class="txt">'
	                	+'<div class="line clearfix">'
	                    	+'<a class="name" href="'+result.data.info[i].href+'">'+ result.data.info[i].cinemaname + '</a>'
	                        +'<span class="price">￥'+result.data.info[i].ticketprice+'</span>'
	                    +'</div>'
	                    +'<div class="line clearfix">'
	                    	+'<div class="num">已报名人数：<span>'+result.data.info[i].enrollnum+'/'+result.data.info[i].enrolltotal+'</span></div>'
	                        +'<span class="time"><i></i>'+result.data.info[i].holdstart_format+'</span>'
	                    +'</div>'
	                +'</div>'
	            +'</li>');
	    		}
	    		$("#activity_list").html(html.join(''));
	    	}
	    });
    }
    
    //城市模糊搜索
    $("#search").keyup(function(e){
    	var keyword = $(this).val();
    	if(keyword==""){
    		get_all_forum_activity();
    	}else{
    		get_forum_activity(keyword);
    	}
    });
});