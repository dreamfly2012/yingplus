//选择支付方式
var menuElsFlg = 'check';

function showEvent(valId, url) {
    document.getElementById(menuElsFlg).className = 'un-check';
    document.getElementById(valId).className = "check";
    menuElsFlg = valId;
}
if(is_login=='false'){
    jump_login_page();
}

$(document).ready(function() {
    $("#sendcaptcha").click(function() {
        var telephone = $("#telephone").val();
        if (!check_phone(telephone)) {
            alert('手机号码格式不正确');
            return false;
        } else {
            $(this).countdown({
                time: 60,
                text: "秒",
                stop: 0,
                method: "val"
            });
            $.ajax({
                url: send_captcha_url,
                type: "POST",
                cache: false,
                data: {
                    'telephone': telephone
                },
                dataType: "json",
                success: function(result) {
                    if (result.data.code != 0) {
                        alert(result.data.message);
                    }
                },
                async: true
            });
        }
    });
    //增加
    $(".add").click(function() {
            var t = $(this).parent().find('input[class*=text_box]');
            t.val(parseInt(t.val()) + 1)
            setTotal();
        })
        //减少
    $(".min").click(function() {
        var t = $(this).parent().find('input[class*=text_box]');
        t.val(parseInt(t.val()) - 1)
        if (parseInt(t.val()) < 0) {
            t.val(0);
        }
        setTotal();
    })

    function setTotal() {
        var s = 0;
        $(".price-list").each(function() {
            s += parseInt($(this).find('input[class*=text_box]').val()) * parseFloat($(this).find('span[class*=price]').text());
        });
        $("#total").html(s.toFixed(2));
    }
  
    //获取电影相关信息包括价格
    $.post(get_activity_info_url, {
        'id': aid
    }, function(result) {
        if (result.data.code == 0) {
            $(".movie_title").html(result.data.info.movie_title);
            $("#cinemaname").html(result.data.info.cinemaname);
            $("#price").html(result.data.info.ticketprice);
            $("#detailaddress").html(result.data.info.detailaddress_format);
            $("#activity_rule").html(result.data.info.activity_rule);
            setTotal();
        }
    });
    //支付点击
    $("#pay").click(function() {
        var method = $(".check").attr('data-pay-id');
        var quantity = $("#quantity").val();
        var captcha = $("#captcha").val();
        var telephone = $("#telephone").val();
        var valid = false;
        if (!check_phone(telephone)) {
            alert('手机号格式不正确');
            return false;
        } else {
            $.ajax({
                type: "POST",
                url: check_captcha_url,
                data: {
                    'telephone': telephone,
                    'captcha': captcha
                },
                dataType: "json",
                async: false,
                success: function(result) {
                    if (result.data.code == 0) {
                        valid = true;
                    } else {
                        valid = false;
                    }
                }
            });
        }
        if (!valid) {
            alert('验证码不正确!');
            return false;
        }
        $.post(get_order_url, {
            'method': method,
            'quantity': quantity,
            'captcha': captcha,
            'telephone': telephone,
            'aid': aid,
            'captcha': captcha
        }, function(result) {
            if (result.data.code == 0) {
                if(method==1){
                    $('body').append(result.data.info);
                }else{
                    window.location.href=result.data.info;
                }
                
            } else {
                alert(result.data.message);
            }
        });
    });
});