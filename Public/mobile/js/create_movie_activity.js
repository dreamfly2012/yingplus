$(document).ready(function() {
    //验证码
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
    
    //省份联动
    $(document).on('change','#holdprovince',function() {
        var pid = $(this).val();
        $.ajax({
            type: 'POST',
            url: get_cities_by_provine_url,
            data: "pid=" + pid,
            dataType: 'json',
            success: function(data) {
                $('#holdcity').empty();
                for (var i = 0; i < data.data.info.length; i++) {
                    $('#holdcity').append("<option value='" + data.data.info[i]['id'] + "'>" + data.data.info[i]['name'] + "</option>");
                }
            },
            async: false
        });
    });

    //表单提交
    $(document).on('click',".create-movie-submit",function(){
        var $this = $(this);
        var captcha = $("#captcha").val();
        var telephone = $("#telephone").val();
        var expecttime = $("#expecttime").val();
        if(expecttime==""){
            swal("日期不能为空");
            return false;
        }else if(telephone==""){
            swal("手机号不能为空");
            return false;
        }else if(!check_phone(telephone)) {
            swal("手机号格式不正确");
            return false;
        }else if(captcha==""){
            swal("验证码不能为空");
            return false;
        }else{
            $this.removeClass("create-movie-submit");
            dataPara = $('.create-movie-activity').serialize();
            
            $.ajax({
                url: create_movie_activity_do_url,
                type: 'post',
                data: dataPara,
                success: function(result){
                    if(result.data.code==0){
                        $(".show_success_text").html('<p class="success_text">你已成功申请，我们会加快审核以及与影院协商，会以系统消息及电话形式再次与您敲定，请保持电话畅通及查看系统消息</p><a href="/" class="votesubmit">返回首页</a>');
                    }else{
                        swal(result.data.message);
                        $this.addClass("create-movie-submit");
                    }
                }
            });
            

        }
    });

    //影院选择
    $(document).on("change","#holdcity,#holdprovince",function() {
        var pid = $("#holdprovince").val();
        var cid = $("#holdcity").val();
        var fid = $("#fid").val();
        var mid = $("#mid").val();
        $.ajax({
            type: 'POST',
            url: get_cinemas_by_place_url,
            data: "pid=" + pid + "&cid=" + cid,
            dataType: 'json',
            success: function(result) {
                $('#cinemaid').empty();
                if (result.data.code == 0) {
                    $('.div_cinema').removeClass('none');
                    for (var i = 0; i < result.data.info.length; i++) {
                        $('#cinemaid').append("<option value='" + result.data.info[i]['id'] + "'>" + result.data.info[i]['title'] + "</option>");
                    }
                } else {
                    $('.div_cinema').addClass('none');
                } 
            },
            async: false
        });
    });

    //影院选择赋值
    $(document).on('click','#cinemaid',function(){
        get_select_cinema();
    });

    //影院名称赋值
    function get_select_cinema(){
        $("#cinemaname").val($("#cinemaid").find("option:selected").text());
    }

    
    

});