var bool_captcha = false;
//验证验证码
function check_captcha(telephone,captcha){
    $.ajax({
        type:"POST",
        url:check_captcha_url,
        data:{
            'telephone':telephone,
            'captcha':captcha
        },
        dataType:'json',
        success:function(result) {
            if(result.data.code == 0){
                bool_captcha = true;
            }else{
                bool_captcha = false;
            }
        },
        async:false
    })
}
$(document).ready(function(){
    // Date demo initialization
    $('#expect_date').mobiscroll().date({
        theme: 'mobiscroll',     // Specify theme like: theme: 'ios' or omit setting to use default 
        mode: 'mixed',       // Specify scroller mode like: mode: 'mixed' or omit setting to use default 
        minDate: minDate,
        maxDate: maxDate,
        display: 'modal', // Specify display mode like: display: 'bottom' or omit setting to use default 
        lang: 'zh'        // Specify language like: lang: 'pl' or omit setting to use default 
    });

    $('#cinemaname').mobiscroll().select({
        theme: 'mobiscroll',     // Specify theme like: theme: 'ios' or omit setting to use default 
        mode: 'mixed',       // Specify scroller mode like: mode: 'mixed' or omit setting to use default 
        display: 'modal', // Specify display mode like: display: 'bottom' or omit setting to use default 
        lang: 'zh'        // Specify language like: lang: 'pl' or omit setting to use default 
    });

    $('#hold_place').mobiscroll().select({
        theme: 'mobiscroll',     // Specify theme like: theme: 'ios' or omit setting to use default 
        mode: 'mixed',       // Specify scroller mode like: mode: 'mixed' or omit setting to use default 
        group: 'true',
        display: 'modal', // Specify display mode like: display: 'bottom' or omit setting to use default 
        lang: 'zh'        // Specify language like: lang: 'pl' or omit setting to use default 
    });

    //验证码
    $(document).on('click',"#sendcaptcha",function(){
        var telephone = $("#telephone").val();
        if(check_phone(telephone)){
            $(this).countdown({
                method:'value'
            });
            $.post(sene_captcha_url,{'telephone':telephone},function(){});
        }else{
            swal('手机号格式不正确');
        }
    });
    

    //创建活动
    $(document).on('click',"#submit",function(){
        var telephone = $("#telephone").val();
        var captcha = $("#captcha").val();
        var type = $(this).attr('data-type');
        if(!check_phone(telephone)){
            swal('填写正确的手机号');
            return false;
        }
        if(captcha==""){
            swal('请填写验证码');
            return false;
        }
        check_captcha(telephone,captcha);
        if(!bool_captcha){
            swal('验证码错误');
            return false;
        }
        if(type=='normal'){
            var cid = $('#hold_place').val();
            var expect_date = $('#expect_date').val();
            var telephone = $('#telephone').val();
            $("#submit").attr('id','disabled_submit');
            $.post(create_noraml_activity_url,{'holdcity':cid,'mid':mid,'fid':fid,'expecttime_date':expect_date,'telephone':telephone},function(result){
                if(result.data.code==0){
                    swal({   
                        title: "提交成功",   
                        text: result.data.message,   
                        type: "success",   
                        showCancelButton: false,   
                        confirmButtonColor: "#AEDEF4",   
                        confirmButtonText: "确定" 
                    }, function(){   
                        window.location.href= movie_index_url;
                    });
                    //$("#disabled_submit").attr('id','submit');
                }else{
                    swal(result.data.message);
                    $("#disabled_submit").attr('id','submit');
                }
            });
        }else if(type=='recognition'){
            $.post(create_recognition_activity_url,{},function(){

            });
        }


    });

    
    //地区选择判断影院
    $(document).on("change","#hold_place",function() {
        var cid = $(this).val();
        $.ajax({
            type: 'POST',
            url: get_activity_type_url,
            data: "cid=" + cid + "&fid=" + fid + "&mid=" + mid,
            dataType: 'json',
            success: function(result) {
                if(result.data.code==0){
                	if(result.data.info.type=='recognition'){
                        $('#enrolltotal').val(result.data.info.encrolltotal);
                        $('#enrolltotal').parent('li').addClass('show').removeClass('hide');
                        $('#price').val(result.data.info.price);
                        $('#price').parent('li').addClass('show').removeClass('hide');
                        $('#detail_address').val(result.data.info.detailaddress);
                        $('#detail_address').parent('li').addClass('show').removeClass('hide');
                        $('#expect_date').mobiscroll('destroy');
                        $('#submit').attr('data-type','recognition');
                	}else if(result.data.info.type=='normal'){
                        if(result.data.info.cinemas!=null){
                            var html = [];
                            for(var i=0;i<result.data.info.cinemas.length;i++){
                                html.push('<option id="'+result.data.info.cinemas[i].id+'">'+result.data.info.cinemas[i].title+'</option>');
                            }
                            $("#cinemaname").html(html.join(''));
                            $("#cinemaname_dummy").val($("#cinemaname").val());
                            $("#cinemaname").parent('li').addClass('show').removeClass('hide');
                        }
                        $('#expect_date').mobiscroll().date({
                            theme: 'mobiscroll',     // Specify theme like: theme: 'ios' or omit setting to use default 
                            mode: 'mixed',       // Specify scroller mode like: mode: 'mixed' or omit setting to use default 
                            minDate: minDate,
                            maxDate: maxDate,
                            display: 'modal', // Specify display mode like: display: 'bottom' or omit setting to use default 
                            lang: 'zh'        // Specify language like: lang: 'pl' or omit setting to use default 
                        });

                        $('#submit').attr('data-type','normal');
                        $('#enrolltotal').parent('li').addClass('hide').removeClass('show');
                        $('#price').parent('li').addClass('hide').removeClass('show');
                        $('#detail_address').parent('li').addClass('hide').removeClass('show');
                	}
                }
            },
            async: false
        });
    });

});