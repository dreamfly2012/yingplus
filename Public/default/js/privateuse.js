setTimeout(function(){
	$('body').click(function(e){
		$('.mark_v').addClass('mark_vMove');
		$('.tree').addClass('treeMove');
		$('.pagepointer').addClass('pagepointerMove');
		$('.bike').addClass('bikeMove2');
		$('.mark_h').addClass('mark_hMover');
		$('.video').addClass('videoMove');
		$('.share').addClass('shareMove');
		$('.releaselimit').addClass('releaselimitMove');
		$('.roundinfo').addClass('roundinfoMove');
		$('.roundlist').addClass('roundlistMove');
		$('.pagepointer').css({'position':'fixed'});
	});	
},3000);
$(".starparagraph").niceScroll({cursorborder:"",cursorcolor:"#2c5722",boxzoom:false,railpadding: { top: 0, right: 3, left: 3, bottom:0 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4',});
$('.guestwrap ul li:odd').addClass('on');
$(".guestwrap").niceScroll({cursorborder:"",cursorcolor:"#2c5722",boxzoom:false,railpadding: { top: 0, right: 3, left: 3, bottom:0 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4',});
$(".comment").niceScroll({cursorborder:"",cursorcolor:"#2c5722",boxzoom:false,railpadding: { top: 0, right: 3, left: 3, bottom:0 },cursoropacitymin: 1, cursoropacitymax: 1,background:'#e5ebe4',});
$('.commidline').height($('.ulyes').height()>$('.ulno').height()?$('.ulyes').height():$('.ulno').height());
function showStorylayer(){
	$('body').addClass('bodyhide');
	$('.alphalayer').fadeIn(200,function(){
		$('.writebox1').fadeIn(200);
	});
}
$('.writeyourstory').click(showStorylayer);
function hideStorylayer(){
	$('body').removeClass('bodyhide')
	$('.writebox1').fadeOut(200,function(){
		$('.alphalayer').fadeOut(200)
	})
}
$('.closebtn').click(hideStorylayer);
function showStorylayer2(){
	$('body').addClass('bodyhide');
	$('.alphalayer').fadeIn(200,function(){
		$('.writebox2').fadeIn(200);
	});
}
$('.wannawrite').click(showStorylayer2);
function hideStorylayer2(){
	$('body').removeClass('bodyhide')
	$('.writebox2').fadeOut(200,function(){
		$('.alphalayer').fadeOut(200)
	})
}
$('.closebtn2').click(hideStorylayer2);

var ff1_top = $(".staritem:eq(0)").offset().top-600;
var ff2_top = $(".staritem:eq(1)").offset().top-600;
var ff3_top = $(".staritem:eq(2)").offset().top-600;
var ff4_top = $(".staritem:eq(3)").offset().top-600;
$(window).scroll(function(){
	var scroH = $(this).scrollTop();
	if(scroH<ff1_top){
		set_cur(0);
	} 
	if(scroH>=ff1_top){
		set_cur(1);
	} 
	if(scroH>=ff2_top){
		set_cur(2);
	} 
	if(scroH>=ff3_top){
		set_cur(3);
	}
	if(scroH>=ff4_top){
		set_cur(4);
	}
});
function set_cur(n){
	$('.ppdotted').each(function(index, element) {
		index = index + 1;
		if(n == index){
			$(this).find('a').addClass('active');
			$(this).siblings('.ppdotted').find('a').removeClass('active')
		} else {
			$(this).find('a').removeClass('active');
		}
	});
}
