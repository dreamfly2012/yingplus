function showLogin(){
	$('body').addClass('bodyhide')
	$('.alphalayer').fadeIn(200,function(){
		$('.loginbox').fadeIn(200);
	});
}
//showLogin();

function hideLogin(){
	$('body').removeClass('bodyhide')
	$('.loginbox').fadeOut(200,function(){
		$('.alphalayer').fadeOut(200);
	});
}
$('.closelayer').click(hideLogin);
$('.picroll').bxSlider();
$(".guestlist").niceScroll({cursorborder:"",cursorwidth:"10px",cursorcolor:"#ffc916",boxzoom:false,railpadding: { top: 3, right: 3, left: 3, bottom:3 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4',});
$(".onlinelist").niceScroll({cursorborder:"",cursorwidth:"10px",cursorcolor:"#ffc916",boxzoom:false,railpadding: { top: 3, right: 3, left: 3, bottom:3 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4',});
//切换在线列表
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

// function addmoreargs (){
// 	var num = parseInt($('.selectitems:last span').text());
// 	var html1 = '<div class="selectitems"><span>';
// 	var html2 = '</span><input type="text"></div>';
// 	$('.addargswrap').append(html1+(num += 1)+html2);
// }
$('.addmorelink').click(function(){
	
});


function showAct(){
	$('body').addClass('bodyhide')
	$('.alphalayer').fadeIn(200,function(){
		$('.addactive').fadeIn(200);
	});
}
//showAct();

function hideAct(){
	$('body').removeClass('bodyhide')
	$('.addactive').fadeOut(200,function(){
		$('.alphalayer').fadeOut(200);
	});
}
$('.closebtn2').click(hideAct);

$("#datepick").on("click",function(e){
	e.stopPropagation();
	$(this).lqdatetimepicker({
		css : 'datetime-day',
		dateType : 'D',
		selectback : function(){

		}
	});
});




















