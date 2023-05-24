$(document).ready(function(){
    //发送验证码
    $("#sendcaptcha").click(function(){
        var telephone = $("#telephone").val();
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

    $('#forgetpassword').click(function(){
        $('#forgetpassword_submit').trigger('click');
    });
    $("#forgetpassword_submit").click(function(){
        var captcha = $("#captcha").val();
        var telephone = $("#telephone").val();
        var password = $("#password").val();
        var rpassword = $("#rpassword").val();
        if(captcha==''||telephone==""||password==""||rpassword==""){
            //执行html5 required
        }else if(password!=rpassword){
            swal('两次密码输入不一致');
        }else{
            $.post(forgetpassword_handle_url,{'telephone':telephone,'password':password,'captcha':captcha},function(result){
                var returnurl = getParameterValue('returnurl');
                returnurl = (returnurl == null) ? index_url : returnurl;
                if(result.data.code==0){
                    window.location.href = returnurl;
                }else{
                    swal(result.data.message);
                }
            });
        }
    });
});