$(document).ready(function(){

    //投票征集分页
    $(document).on('click', '.more_info', function(){
        var p = $(this).attr('data-page');
        $.post(get_more_poster_info_url,{p:p},function(result){
            if(result.status==0){
                $('.main_huodonging_poster').append(result.info);
                $('.more_info').attr('data-page',result.pageid);
            }else{
                $('.more_info').text(result.info).removeAttr('data-page');
            }
        });
    });
    //投票征集海报删除
    $(document).on('click','.main_huodonging_poster_delete',function(){
        var oid = $(this).attr('data-oid');
        $.post(delete_poster_url,{oid:oid},function(result){
            if(result.status==0){
                show_short_message(result.info);
                window.location.reload();
            }else if(result.status==1){
                show_login_form(__SHOW_LOGIN_URL__,1,'collect-delete-img');
            }else{
                show_short_message(result.info);
            }
        });
    });

    //投票征集，上传自己的海报
    $(document).on('click', '.upload_my_poster', function() {
        if(is_login=="true"){
            $(".upload_poster_img").trigger('click');
        }else{
            $('.head_login').trigger('click');
        }
        
    });

    //上传征集
    $(document).on("change", '.upload_poster_img',function(){
        var aid = $(this).attr('data-aid');
        $.ajaxFileUpload({
            url: upload_poster_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'upload_poster_img', //文件上传域的ID
            data:{
                'aid':aid
            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                if(data.data.code!=0){
                    alert(data.data.message);
                }else{
                    var attachmentid = data.data.info;
                    var param = 'attachmentid='+attachmentid+'&aid='+aid;
                    show_judgement_form_parameter(add_zhengji_url, param)
                }

            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                alert(e);
            }
        })
    });

    //图片征集上传
    $(document).on('click', '.add-zhengji-do', function() {
        if($("#desc").val().length>=20){
            alert('描述请少于20字');
        }else{
            var attachmentid = $(this).attr('data-attachmentid');
            var aid = $(this).attr('data-aid');
            var desc = $("#desc").val();
            $.post(add_poster_do_url,{'attachmentid':attachmentid,'desc':desc,'aid':aid},function(result){
                window.location.reload();
            });
        }
    });


    //单选投票
    $(document).on('click','.single-check',function(){
        $('.single-check').removeClass('checked').addClass('uncheck');
        $(this).removeClass('uncheck').addClass('checked');
    });

    //多选投票
    $(document).on('click','multi-check',function(){
        if($(this).hasClass('checked')){
            $(this).removeClass('checked').addClass('uncheck');
        }else{
            $(this).removeClass('uncheck').addClass('checked');
        }
    });

    //投票提交
    $(document).on('click','.submit_vote',function(){
        var aid = $(this).attr('data-aid');
        var isimg = $(this).attr('data-isimg');
        var join_number = parseInt($('.join_number').text());
        var choices = '';
        var single_length = $(".single-check").length;
        var ismultiselect = 0;
        if(single_length!=0){
            var checked_single_length = $(".single-check:checked").length;
            if(checked_single_length==0){
                alert('请至少选择一项');
                return ;
            }
            $(".single-check:checked").each(function(){
                choices += $(this).attr('data-choice')+',';
            });
        }else{
            var checked_multiselect_length = $(".multi-check:checked").length;
            if(checked_multiselect_length==0){
                alert('请至少选择一项');
                return ;
            }
            $(".multi-check:checked").each(function(){
                choices += $(this).attr('data-choice')+',';
            });
            ismultiselect = 1;
        }
        
        $.post(submit_vote_url,{'aid':aid,ismultiselect:ismultiselect,choices:choices},function(result){
            if(result.data.code==0){
                //alert(result.data.message);
                var vote_result_url = "";
                if(isimg==0){
                    vote_result_url = get_vote_text_result_url;
                }else{
                    vote_result_url = get_vote_image_result_url;
                }
                //
                $.post(vote_result_url,{'aid':aid},function(result){
                    $('#screen_crop').html(result);
                    $('.join_number').text(join_number+1);
                    html2canvas($('#screen_crop'), {
                        useCORS,
                        onrendered: function(canvas) {
                            var img = canvas.toDataURL("image/png");
                            $.post(share_weibo_img_url,{'img_data':img,'id':aid},function(){

                            });
                        }
                    });
                })

                //window.location.reload();
            }else{
                $('.head_login').trigger('click');
            }
        });
    });
});
