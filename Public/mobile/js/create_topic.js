var lock_add_topic = false;
$(document).ready(function(){
	//上传图片
    $('.choose_topic_img').click(function(){
        $('#upload_topic_image').trigger('click');
    });
    //上传图片到云
    $(document).on("change","#upload_topic_image",function(){
        $.ajaxFileUpload({
            url: upload_img_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'upload_topic_image', //文件上传域的ID
            data:{

            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                //尝试进行json解析
                if(data.data.code==0){
                    //添加到评论框
                    $.post(get_attachment_info_url,{'id':data.data.info},function(result){
                        if(result.data.code==0){
                            $('#topic_content').append('<img src="'+result.data.info.remote_url+'">');
                        }
                    });
                }else{
                    swal(data.data.message)
                }
            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                swal(e);
            }
        });
    });

    //添加话题
    $(document).on('click','.add_topic_do',function() {
        var fid = $(this).attr('data-fid');
        var subject = $('#topic_subject').val();
        var content = $('#topic_content').html();
        var _this = $(this);
        if (subject == '' || content == '') {
            swal('话题标题和内容不能为空!');
            return false;
        }

        if (content.length < 10) {
            swal('话题内容长度不能少于10个字');
            return false;
        }

        _this.removeClass('add_topic_do');
        
        $.post(add_topic_do_url, {'fid': fid, 'subject': subject, 'content': content}, function (result) {
            if (result.data.code == 0) {
                window.location.href = topic_url + '?tid=' + result.data.info.tid;
            } else if(result.data.code == 2){
                jump_login_page();
            }else{
                _this.addClass('add_topic_do');
                swal(result.data.message);
            }
        });
        
        
        

        
    });
	
});