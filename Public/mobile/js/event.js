$(document).ready(function(){
    $('#apply_welfare').click(function(){
        var telephone = $('#welfare_telephone').val();
        var captcha = $('#welfare_captcha').val();
        if(!check_phone(telephone)){
            swal('手机号码格式不正确');
            return false;
        }
        $.post(check_captcha_url,{'telephone':telephone,'captcha':captcha},function(result){
            if(result.data.code==0){
                $.post(apply_welfare_url,{'telephone':telephone},function(result_info){
                    if(result_info.data.code==0){
                        swal('您的申请已提交，请等待工作人员联系~');
                    }else{
                        swal('申请失败');
                    }
                });
            }else{
                swal('验证码不正确');
            }
        });
    });

    $("#sendcaptcha").click(function(){
        var telephone = $("#welfare_telephone").val();
        if(!check_phone(telephone)){
            swal('手机号码格式不正确');
            return false;
        }else{
            $(this).countdown({
                time : 60,
                text : "秒",
                stop : 0,
                method : "text"
            });
            $.ajax({
                url : send_captcha_url,
                type: "POST",
                cache:false,
                data: {'telephone':telephone},
                dataType: "json",
                success: function(result){
                    if(result.data.code!=0){
                        swal(result.data.message);
                    }
                 },
                async : true
            });
        }
    });
});
