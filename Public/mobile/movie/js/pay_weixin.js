function checkHasPay(trade_no){
    $.post(check_has_pay_url,{'trade_no':trade_no},function(result){
        if(result.info==0){
            window.location.href = "<{:U('MovieActivity/detail',array('aid'=>$aid))}>";
        }
    });
}

$(document).ready(function(){
    setInterval("checkHasPay(trade_no)",1000*10);
});