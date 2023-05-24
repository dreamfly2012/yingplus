$(document).ready(function(){
	//获取活动相关讯息(aid)
	$.post(get_activity_info_url,{'id':aid},function(result){
		if(result.data.code==0){
			$(".movie_title").html(result.data.info.movie_title);
			$("#success_img").attr('src','');
			$("#detail_link").attr('href',result.data.info.detail_url);
		}
	});

	//获取订单相关讯息
	$.post(get_order_info_url,{'trade_no':trade_no},function(result){
		if(result.data.code==0){
			
		}
	});

});