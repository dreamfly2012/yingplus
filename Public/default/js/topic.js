$(document).ready(function(){
	// $('.argitem').each(function(index, element) {
	// 	var _this = $(this);
	// 	_this.find('.anslink').click(function(){
	// 		if(_this.find('.anstextarea').css('display')=='none'){
	// 			$(this).html('收起');
	// 			_this.find('.anstextarea').slideDown(200);
	// 		}
	// 		else {
	// 			$(this).html('回复');
	// 			_this.find('.anstextarea').slideUp(200);
	// 		}
	// 	});
	// });
	$('.heart').each(function(index, element) {
		$(this).click(function(){
			$(this).find('img').addClass('on');
			$(this).siblings('.heart').find('img').removeClass('on')
		});
	});
	$(".onlinelist").niceScroll({cursorborder:"",cursorwidth:"10px",cursorcolor:"#ffc916",boxzoom:false,railpadding: { top: 3, right: 3, left: 3, bottom:3 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4',});
	$(".guestlist").niceScroll({cursorborder:"",cursorwidth:"10px",cursorcolor:"#ffc916",boxzoom:false,railpadding: { top: 3, right: 3, left: 3, bottom:3 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4',});
	$(".fix_response_topic").click(function(){
		$('#response_content').focus();
	});

	

});
