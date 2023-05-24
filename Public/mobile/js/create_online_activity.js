$(document).ready(function(){
    //投票单选多选
    $('.voteitem a').click(function(){
        $(this).addClass('on');
        $(this).siblings('a').removeClass('on');
        if($(this).attr('data-type')=='single'){
            $('#option_set').val(0);
        }else{
            $('#option_set').val(1);
        }
    });

    //添加选项
    $(document).on('click',".add_more_option",function(){
        $(".question_option").append(
            '<div class="optionbox">'+
                '<a href="javascript:;" title="" class="upload_small_icon" data-imageid="0"></a>'+
                '<input type="text" name="question[]" value="">'+
                '<div class="delete_option"></div>'+
                '<div class="clear"></div>'+
            '</div>');
        
        $(".delete_option").css('display','block');
    });
    //移除选项
    $(document).on('click',".delete_option",function(){
        if($(".delete_option").length==3){
            $(this).parent('div.optionbox').remove();
            $(".delete_option").css('display','none');
        }else{
            $(this).parent('div.optionbox').remove();
        }
    });

    $(document).on('click','.upload_small_icon',function(){
        var num = $(".upload_small_icon").index($(this));
        $('.vote_image').attr('data-id',num);
        $('.vote_image').trigger('click');
    });

    $(document).on('change','.vote_image',function(){
        var $this = $(this);
        var numid = $(this).attr('data-id');
        $.ajaxFileUpload({
            url: upload_img_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: $(this).attr('id'), //文件上传域的ID
            data:{
                // width:100,
                // height:100
            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                if(data.data.code==0){
                    $.post(get_attachment_info_url,{'id':data.data.info},function(result){
                        $(".upload_small_icon").eq(numid).css('background-image','url('+result.data.info.remote_url+')').attr('data-imageid',result.data.info.id);
                    })
                }else {
                    alert(data.data.message);
                }  
            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                alert(e);
            }
        })
    });


    //提交表单
    $(document).on("click",".create-online-activity-submit",function(){
        var $this = $(this);
        if($("#title").val()==''||$('#desc').val()==''){
            swal('标题介绍不能为空');
            return false;
        }else{
            //问题验证
            var bool_imageid1 = $("input[name='question[]']").eq(0).prev().attr('data-imageid')=='0';
            var bool_imageid2 = $("input[name='question[]']").eq(1).prev().attr('data-imageid')=='0';
            var bool_text1 = $("input[name='question[]']").eq(0).val()=='';
            var bool_text2 = $("input[name='question[]']").eq(1).val()=='';
            var text_overflow = true;

            if($("#type").val()==1 && ((bool_imageid1&&bool_text1) || (bool_imageid2&&bool_text2))){
                swal('至少填写2个选项');
            }else {
                if ($("#type").val() == 1 && !bool_imageid1 && !bool_imageid2) {
                    var length = $("input[name='question[]']").length;
                    for (i = 0; i < length; i++) {
                        var text = $("input[name='question[]']").eq(i).val();
                        if (text.length > 20) {
                            swal('图片投票描述不能超过20字');
                            text_overflow = false;
                            return;
                        }
                    }
                    text_overflow = true;
                }

                //验证通过,ajax提交
                //处理问题图片
                if(text_overflow==true){
                    var question_img = "";
                    $(".upload_small_icon").each(function () {
                        question_img += $(this).attr('data-imageid') + ',';
                    });
                    
                    $("#question_img").val(question_img);
                    var param = $(".create-online-activity").serialize();
                    $this.removeClass('create-online-activity-submit');
                    $.ajax({
                        url: create_online_activity_do_url,
                        type: 'post',
                        data: param,
                        success: function (result) {
                            if (result.data.code == 0) {
                                //跳转到活动详情页
                                //alert('创建成功');
                                window.location.href = result.data.info.url;
                            } else {
                                swal(result.data.message);
                                $this.addClass('create-online-activity-submit');
                            }
                        }
                    });
                }
            }
            
        }
    });

});
