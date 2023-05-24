$(document).ready(function() {
    $(".option li").click(function() {
        $(this).addClass('click').siblings().removeClass('click').siblings(".jian_1").removeClass('blue');
        var index = $(".option li").index($(this));
        $('.table').eq(index).show().siblings().hide();
    });


    $(".option li").click(function() {
        $(this).children(".line_3").css("display", "block").parent().siblings().children(".line_3").hide();
    });

    $(".jian_1").click(function() {
        $(".line_3").hide();
    });

    //url触发点击事件
    var tab_index = getParameterValue('tab');
    console.log(tab_index);
    if(tab_index!=null){
        $('.option li').eq(tab_index).trigger('click');
    }


});
