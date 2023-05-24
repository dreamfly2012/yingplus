function later_alert(){
	swal('即将上映,敬请期待~');
}

$(document).ready(function(){

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
