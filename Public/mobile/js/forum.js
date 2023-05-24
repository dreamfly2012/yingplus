$(function(){
	$('.aotab ul li').each(function(index, element) {
		$(this).click(function(){
			$(this).find('img').removeClass('gray');
			$(this).siblings('li').find('img').addClass('gray');
			$('.tab').eq(index).show().siblings('.tab').hide();
		});
	});
});