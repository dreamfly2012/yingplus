$(document).ready(function() {
    $("#introduce").click(function() {
        $("#content_two").show();
        $("#content_one").hide();
    });
});
$(document).ready(function() {
    $("#shuo").click(function() {
        $("#content_one").show();
        $("#content_two").hide();
    });
});
$(document).ready(function() {
    $("#comment").click(function() {
        $("#table_two").show();
        $("#table_one").hide();
    });
});
$(document).ready(function() {
    $("#number").click(function() {
        $("#table_one").show();
        $("#table_two").hide();
    });
});


//捐款支付处理
//
// $('.go_donation').click(function(){
//     show_form(show_donation_form_url);
// });

function ajax_page_donate_order(p){
    $.ajax({
        type : "POST",
        url : donate_order_page_url,
        data : {'p': p,'fid':fid},
        dataType : "html",
        success : function(result) {
            $(".order_block").html(result);
        },
        async : false
    });
}