$(document).ready(function() {
	//底部导航
	$('.footblank').height($('.footico').height()+12);

    //反馈图片上传
    $('.upload_feedback_image').click(function(){
        if(is_loign='true'){
            $('#upload_feedback_img').trigger('click');
        }else{
            $('.head_login').trigger('click');
        }

    });
    //反馈图片展示
    if($('.flex-images').children().length!=0){
        $('.flex-images').flexImages({rowHeight:200,maxRow: 100});
    }

    $("#upload_feedback_img").change(function(){
        var aid = $(this).attr('data-aid');
        $.ajaxFileUpload({
            url: add_movie_feedback_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'upload_feedback_img', //文件上传域的ID
            data:{
                aid:aid
            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                if(data.data.code!=0){
                    alert(data.data.message);
                }else{
                    var attachmentid = data.data.info;
                    $.post(add_movie_feedback_do_url,{'aid':aid,'attachmentid':attachmentid},function(result){
                        if(result.data.code==0){
                            alert('上传反馈成功');
                        }
                    });
                }

            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                alert(e);
            }
        });
    });

    //反馈视频上传
    $("#movie_video_feedback").click(function(){
        $.post(upload_video_url,{'aid':$("#movie_video_feedback").attr('data-aid')},function(result){
            show_content(result);
        });
    });
    
    //海报上传
    $("#upload_poster_img").change(function(){
        $.ajaxFileUpload({
            url: url_poster_upload, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'upload_poster_img', //文件上传域的ID
            data:{
                aid:$("#upload_poster_img").attr('data-aid')
            },
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status)  //服务器成功响应处理函数
            {
                try{
                    response = eval('(' + data + ')');
                    show_short_message(response.info);
                    window.location.reload();
                }catch(e){
                    show_short_message('系统错误');
                }

            },
            error: function (data, status, e)//服务器响应失败处理函数
            {
                alert(e);
            }
        })
    });
    
    //视频上传
    $("#upload_movie").click(function(){
        $.post(__UPLOAD_ACTIVITY_MOVIE_URL__,{aid:aid},function(result){
            show_content(result.info);
        });
    });

    
    //删除图片处理
    $(".self_delete_poster").click(function(event){
        if(confirm('是否要删除?')){
            var ids = $(this).attr('data-id') + ',';
            $.ajax({
                type: "POST",
                url: __DELETE_FEEDBACK_URL__,
                data: {'ids':ids},
                dataType: "json",
                success: function(data){
                    show_short_message(data.info);
                    window.location.reload();
                }
            });
            return false;
        }else{
            return false;
        }
        
    });

    //删除海报图片处理
    $(document).on("click",".delete_poster",function(){
        if($(this).text()=='删除图片'){
            $(this).text('确定');
            $(this).after('<span class="cancel_delete_poster">取消</span>');
            $('.movie_bottom_poster_img').each(function(){
                var id = $(this).attr('data-id');
                $(this).parent('div').removeClass('fancybox');
                $('<input type="checkbox" id="delete_poster" data-id="'+id+'" class="delete_poster_checkbox">').insertAfter($(this));
            });
        }else{
            var ids = "";
            $(".delete_poster_checkbox:checked").each(function(){
                ids = ids + $(this).attr('data-id') + ',';
            });
            if(ids==""){
                alert('删除内容不能为空');
            }else{
                $.post(__DELETE_POSTER_URL__,{ids:ids},function(result){
                    show_short_message(result.info);
                    window.location.reload();
                });
                $('.movie_bottom_poster_img').each(function(){
                    $(this).parent('div').addClass('fancybox');
                });
                $(this).text('删除图片');
            }
         }
        
    });

    //取消删除海报图片
    $(document).on("click",".cancel_delete_poster",function(){
        $('.delete_poster').text('删除图片');
        $('.movie_bottom_poster_img').each(function(){
            $(this).parent('div').addClass('fancybox');
        });
        $(".delete_poster_checkbox").remove();
        $(this).remove();
    });

    //删除反馈海报视频
    $(document).on("click",".delete_feedback",function(){
        if($(this).text()=='删除'){
            $(this).text('确定');
            $(this).after('<span class="cancel_delete_feedback">取消</span>');
            $(".feedback_back_img").each(function(){
                var id = $(this).attr('data-id');
                $(this).parent('div').removeClass('fancybox');
                $('<input type="checkbox"  data-id="'+id+'" class="delete_feedback_checkbox">').insertAfter($(this));
            });
        }else{
            var ids = "";
            $(".delete_feedback_checkbox:checked").each(function(){
                ids = ids + $(this).attr('data-id') + ',';
            });
            if(ids==""){
                alert('删除内容不能为空');
            }else{
                $.post(__DELETE_FEEDBACK_URL__,{ids:ids},function(result){
                    show_short_message(result.info);
                    window.location.reload();
                });
                $('.movie_bottom_poster_img').each(function(){
                    $(this).parent('div').addClass('fancybox');
                });
                $(this).text('删除');
            }
        }

    });

    //取消删除反馈视频
    $(document).on("click",".cancel_delete_feedback",function(){
        $('.delete_feedback').text('删除');
        $('.feedback_back_img').each(function(){
            $(this).parent('div').addClass('fancybox');
        });
        $(".delete_feedback_checkbox").remove();
        $(this).remove();
    });
    
       

    //活动报名
    $(".movie-activity-enroll").click(function(){
        var aid = $(this).attr('data-aid');
        if(is_login=='true'){
            $.post(get_enroll_url,{'aid':aid},function(result){
                if(result.data.code==0){
                    window.location.href=result.data.info;
                } else{
                    alert(result.data.message);
                }
            });
        }else{
            jump_login_page();
        }
        
    });

    //回复设置
    $(document).on('click','.movie_jishi_list_item',function(){
        $(this).addClass('movie_jishi_list_item_current').siblings().removeClass('movie_jishi_list_item_current');
        var fid = $(this).attr('data-fid');
        var aid = $(this).attr('data-aid');
        var tid = $(this).attr('data-tid');
        var username = $(this).attr('data-username');
        var editor_id = $(this).attr('data-editorid');
        var content = eval(editor_id).getPlainTxt();
        if(content=='参与回复'){
            eval(editor_id).setContent('@' + username + '&nbsp;'); //设置回复用户名
        }else{
            eval(editor_id).setContent(content+' @' + username + '&nbsp;'); //设置回复用户名
        }
        eval(editor_id).focus(true);
    });
    

    //活动封面图片
    $(document).on('click', '.change_main_img', function() {
        $(".upload_cover_img").trigger('click');
    });

    //反馈上传图片
    $(document).on('click', '.movie_feedback', function() {
        $(".upload_feedback_img").trigger('click');
    });

    //海报图片上传
    $(document).on('click', '#upload_poster', function() {
        $(".upload_poster_img").trigger('click');
    });

    //反馈图片展示
    if($('#flex-images1').children().length!=0){
        $('#flex-images1').flexImages({rowHeight:70,maxRow: 100});
    }


    //加载fancybox
    $(".fancybox").fancybox({

    });

    

    //反馈截图处理
    $(document).on('click', '.confirm-crop', function() {
        var avatar = $('#activity-img img').attr('src');
        var primary = $('#primary').val();
        var x = $('#x').val();
        var y = $('#y').val();
        var w = $('#w').val();
        var h = $('#h').val();
        var aid = $('#aid').val();
        $.ajax({
            type: 'post', // 提交方式 get/post
            url: produceFeedBackImg_url, // 需要提交的 url
            data: {
                'primary':primary,
                'avatar': avatar,
                'x': x,
                'y': y,
                'w': w,
                'h': h,
                'aid':aid
            },
            success: function(data) { // data 保存提交后返回的数据，一般为 json 数据
                close_form();
                window.location.reload();
            }
        });
    });

    //海报截图处理
    $(document).on('click', '.poster-confirm-crop', function() {
        var avatar = $('#activity-img img').attr('src');
        var primary = $('#primary').val();
        var x = $('#x').val();
        var y = $('#y').val();
        var w = $('#w').val();
        var h = $('#h').val();
        var aid = $('#aid').val();
        $.ajax({
            type: 'post', // 提交方式 get/post
            url: producePosterImg_url, // 需要提交的 url
            data: {
                'primary':primary,
                'avatar': avatar,
                'x': x,
                'y': y,
                'w': w,
                'h': h,
                'aid':aid
            },
            success: function(data) { // data 保存提交后返回的数据，一般为 json 数据
                close_form();
                window.location.reload();
            }
        });
    });

    //封面截图处理
    $(document).on('click', '.cover-confirm-crop', function() {
        var avatar = $('#activity-img img').attr('src');
        var primary = $('#primary').val();
        var x = $('#x').val();
        var y = $('#y').val();
        var w = $('#w').val();
        var h = $('#h').val();
        var aid = $('#aid').val();
        $.ajax({
            type: 'post', // 提交方式 get/post
            url: produceCoverImg_url, // 需要提交的 url
            data: {
                'primary':primary,
                'avatar': avatar,
                'x': x,
                'y': y,
                'w': w,
                'h': h,
                'aid':aid
            },
            success: function(data) { // data 保存提交后返回的数据，一般为 json 数据
                close_form();
                window.location.reload();
            }
        });
    });

});