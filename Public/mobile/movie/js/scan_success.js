$(document).ready(function(){
	//获取影院信息
    $.post(get_activity_info_url, {'id': aid}, function (result) {
        if(result.data.code==0){
            var enrolltotal = result.data.info.enrolltotal;
            var enrollnum = result.data.info.enrollnum;
            $(".movie_title").text(result.data.info.movie_title);
        }
    });
    //获取二维码中的信息(兑换密码表)
    $.post(get_exchange_code_info_url,{'aid':aid,'code':code},function(result){
    	if(result.data.code==0){
    		$("#username").text(result.data.info.username);
    		$("#telephone").text(result.data.info.telephone);
    		$("#ticketnum").text(result.data.info.goods_amount);
    	}else{
    		alert(result.data.message);
    		window.history.go(-1);
    	}
    });

    //确认到场
    $("#confirm").click(function(){
        $.post(confirm_presence_url,{'code':code,'aid':aid},function(result){
            if(result.data.code==0){
                if(result.data.info.check_status==1){
                    alert('扫码成功~');
                    window.location.href = result.data.info.returnurl;
                }
            }else{
                alert(result.data.message);
            }
        });
    });

});