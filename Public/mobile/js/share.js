$(document).ready(function(){
	//话题分享
	$('.share_topic_weibo').click(function(){
		var url = window.location.href;
		var share_id = '';
		var title = encodeURIComponent($('.votename.h1').text());
		var appkey = 255864200;
		var pic = '';
		if($('.votetext').find('img').length!=0){
			pic = $('.votetext').find('img')[0].src;
		}
		share_weibo(url,share_id,title,appkey,pic);
	});
	$('.share_topic_qq').click(function(){
		var url = window.location.href;
		var share_id = '';
		var title = encodeURIComponent($('.votename.h1').text());
		var desc = encodeURIComponent($('.votename.h1').text());
		var summary = encodeURIComponent($('.votename.h1').text());
		var site = '影加';
		var pics = '';
		if($('.votetext').find('img').length!=0){
			pics = $('.votetext').find('img')[0].src;
		}
		share_qq(url,share_id,title,desc,summary,site,pics)
	});
	$('.share_topic_qzone').click(function(){
		var url = window.location.href;
		var share_id = '';
		var title = encodeURIComponent($('.votename.h1').text());
		var desc = encodeURIComponent($('.votename.h1').text());
		var summary = encodeURIComponent($('.votename.h1').text());
		var appkey = 255864200;
		var site = '影加';
		var pics = '';
		if($('.votetext').find('img').length!=0){
			pics = $('.votetext').find('img')[0].src;
		}
		share_qzone(url,share_id,title,desc,summary,site,pics);
	});
	$('.share_topic_weixin').click(function(){
		var url = window.location.href;
		share_weixin(url);
	});


	//包场活动分享
	$('.share_movie_activity_weibo').click(function(){
		var url = window.location.href;
		var share_id = '';
		var title = encodeURIComponent("我参加了#夏有乔木梅格妮包场观影#活动，快来为#吴亦凡#助力票房（"+url+"）！");
		var appkey = 255864200;
		var pic = 'http://yingplus.80shihua.com/uploads/MovieActivity/20160411/570b00e6b0ee7.jpg';
		share_weibo(url,share_id,title,appkey,pic);
	});
	$('.share_movie_activity_qq').click(function(){
		var url = window.location.href;
		var share_id = '';
		var title = "我参加了%23夏有乔木梅格妮包场观影%23活动，快来为%23吴亦凡%23助力票房（"+url+"）！";
		var desc = "我参加了%23夏有乔木梅格妮包场观影%23活动，快来为%23吴亦凡%23助力票房（"+url+"）！";
		var summary = "我参加了%23夏有乔木梅格妮包场观影%23活动，快来为%23吴亦凡%23助力票房（"+url+"）！";
		var site = '影加';
		var pics = 'http://yingplus.80shihua.com/uploads/MovieActivity/20160411/570b00e6b0ee7.jpg';
		share_qq(url,share_id,title,desc,summary,site,pics)
	});
	$('.share_movie_activity_qzone').click(function(){
		var url = window.location.href;
		var share_id = '';
		var title = "我参加了%23夏有乔木梅格妮包场观影%23活动，快来为%23吴亦凡%23助力票房（"+url+"）！";
		var desc = "我参加了%23夏有乔木梅格妮包场观影%23活动，快来为%23吴亦凡%23助力票房（"+url+"）！";
		var summary = "我参加了%23夏有乔木梅格妮包场观影%23活动，快来为%23吴亦凡%23助力票房（"+url+"）！";
		var appkey = 255864200;
		var site = '影加';
		var pics = 'http://yingplus.80shihua.com/uploads/MovieActivity/20160411/570b00e6b0ee7.jpg';
		share_qzone(url,share_id,title,desc,summary,site,pics);
	});
	$('.share_movie_activity_weixin').click(function(){
		var url = window.location.href;
		share_weixin(url);
	});

	//线上活动
	$('.share_online_activity_weibo').click(function(){
		var url = window.location.href;
		var share_id = '';
		var activity_title = encodeURIComponent($('h1.votename').text());
		var title = encodeURIComponent('我参加了#梅格妮+#的线上活动#'+activity_title+'#，赶紧来围观（'+url+'）！');
		var appkey = 255864200;
		var pic = '';
		if($('.poster_img').length!=0){
			pic = $('.poster_img').children('a.fancybox').eq(0).attr('href');
		}
		share_weibo(url,share_id,title,appkey,pic);
	});
	$('.share_online_activity_qq').click(function(){
		var url = window.location.href;
		var share_id = '';
		var activity_title = $('.h1.votename').text();
		var title = '我参加了%23梅格妮+%23的线上活动%23'+activity_title+'%23，赶紧来围观（'+url+'）！';
		var desc = '我参加了%23梅格妮+%23的线上活动%23'+activity_title+'%23，赶紧来围观（'+url+'）！';
		var summary = '我参加了%23梅格妮+%23的线上活动%23'+activity_title+'%23，赶紧来围观（'+url+'）！';
		var site = '影加';
		var pics = '';
		if($('.poster_img').length!=0){
			pics = $('.poster_img').children('a.fancybox').eq(0).attr('href');
		}
		
		share_qq(url,share_id,title,desc,summary,site,pics)
	});
	$('.share_online_activity_qzone').click(function(){
		var url = window.location.href;
		var share_id = '';
		var activity_title = $('.h1.votename').text();
		var title = '我参加了%23梅格妮+%23的线上活动%23'+activity_title+'%23，赶紧来围观（'+url+'）！';
		var desc = '我参加了%23梅格妮+%23的线上活动%23'+activity_title+'%23，赶紧来围观（'+url+'）！';
		var summary = '我参加了%23梅格妮+%23的线上活动%23'+activity_title+'%23，赶紧来围观（'+url+'）！';
		var appkey = 255864200;
		var site = '影加';
		var pics = '';
		if($('.poster_img').length!=0){
			pics = $('.poster_img').children('a.fancybox').eq(0).attr('href');
		}
		share_qzone(url,share_id,title,desc,summary,site,pics);
	});
	$('.share_online_activity_weixin').click(function(){
		var url = window.location.href;
		share_weixin(url);
	});

	
});