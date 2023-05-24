$(document).ready(function(){
    var telephoneIsExist = false;
    function checktelephone(telephone){
        $.ajax({
            url : check_telephone_url,
            type: "POST",
            cache:false,
            data: {'telephone':telephone},
            dataType: "json",
            success: function(result){
                if(result.data.code!=0){
                    telephoneIsExist = false;
                }else{
                    if(result.data.info==true){
                        telephoneIsExist = true;
                    }else{
                        telephoneIsExist = false;
                    }
                }
            },
            async : false
        });
    }

    //发送验证码
    $(document).on("click","#sendcaptcha",function(){
        var telephone = $("#telephone").val();
        var type = $(this).attr('data-type');
        if(!check_phone(telephone)){
            alert('手机号码格式不正确');
            return false;
        }else{
            checktelephone(telephone);
            if(telephoneIsExist&&type=='reigster'){
                alert('手机号已经被注册');
                return false;
            }

            if(!telephoneIsExist&&type=='forgetpassword'){
                alert('手机号尚未被注册');
                return false;
            }

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
                        alert(result.data.message);
                    }
                 },
                async : true
            });
        }
    });

    //enter登陆
    $(document).on('focus','#password',function(){
        $(document).keydown(function (event) {
            if(event.keyCode == 13 || event.keyCode ==10){
                $('.login_sure').trigger('click');
            }
        });
    });

    // 登陆表单验证
    $(document).on('click','.login_sure',function(){
        var telephone = $('#telephone').val();
        var password = $('#password').val();
        //手机号不能为空
        if(telephone == ''){
            alert('手机号不能为空!');
            return;
        }
        //验证手机格式
        if(!check_phone(telephone)){
            alert('手机号格式不正确!');
            return;
        }
        
        //验证密码不能为空
        if(password==''){
            alert('密码不能为空!');
            return;
        }
       
        //清空验证信息
        var param = $("#loginform").serialize();
        htmlobj = $.ajax({
            type : "POST",
            url : login_url,
            data : param,
            dataType : "json",
            success : function(data) {
                if(data.data.code == 0){
                    var old_href = window.location.protocol+'//' + window.location.host + window.location.pathname
                    window.location.href = old_href+'?sso=login';
                } else{
                    alert(data.data.message);
                }
            },
            async : true
        });
    });

    //注册表单验证
    $(document).on('click','.registered_sure',function(){
        var telephone = $('#telephone').val();
        var password  = $('#password').val();
        var captcha = $('#captcha').val();document

        //手机号不能为空
        if(telephone == ''){
            alert('手机号不能为空');
            return;
        }
        //验证手机号格式
        if(!check_phone(telephone)){
            alert('手机号格式不正确');
            return;
        }
        
        //验证码验证
        if(captcha == ''){
            alert('验证码不能为空');
            return;
        }
        //密码验证
        if(password == ''){
            alert('密码不能为空');
            return;
        }
        //注册协议验证
        if(!$("#agree").prop('checked')){
            alert('请同意注册协议');
            return;
        }
        
        var param = $("#registerform").serialize();
        htmlobj = $.ajax({
            type : "POST",
            url : register_url,
            data : param,
            dataType : "json",
            success : function(data) {
                if(data.data.code==0){
                    var old_href = window.location.protocol+'//' + window.location.host + window.location.pathname
                    window.location.href = old_href+'?sso=login';
                } else {
                    alert(data.data.message);
                }
            },
            async : true
        });
    });

    //忘记密码表单验证
    $(document).on('click','.forgetpassword_sure',function(){
        var telephone = $('#telephone').val();
        var captcha  = $('#captcha').val();
        var password = $('#password').val();
        var confirm_password = $('#confirm_password').val();
        //手机号验证
        if(telephone==''){
            alert('手机号不能为空');
            return;
        }
        //手机号验证
        if(!check_phone(telephone)){
            alert('手机号格式不正确');
            return;
        }
        //验证码验证
        if(captcha == ''){
            alert('验证码不能为空！');
            return;
        }
        //新密码验证
        if(password == ''){
            alert('新密码不能为空');
            return;
        }
        //确认密码验证
        if(confirm_password == ''){
            alert('确认密码不能为空');
            return;
        }
        //确认密码验证
        if(password != confirm_password){
            alert('二次输入密码不相等！');
            return;
        }
    
        var param = $("#forgetpasswordform").serialize();
        htmlobj = $.ajax({
            type : "POST",
            url : forgetpassword_url,
            data : param,
            dataType : "json",
            success : function(data) {
                if(data.data.code==0){
                    var old_href = window.location.protocol+'//' + window.location.host + window.location.pathname
                    window.location.href = old_href+'?sso=login';
                } else {
                    alert(data.data.message);
                }
            },
            async : true
        });
    });
});