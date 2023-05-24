function response_template(userphoto,username,content,format_time,rid){
    var template = '<div class="argitem">'+
        '<b><a href="javascript:;" title=""><img src="'+userphoto+'"></a></b>'+
        '<div class="argright">'+
            '<h2>'+username+'</h2>'+
            '<p>'+
                content+
            '</p>'+
            '<div class="regtime">'+
                '<span>'+format_time+'</span>'+
                '<a href="javascript:;" title="回复" class="response_to" data-username="'+username+'" data-rid="'+rid+'"></a>'+
                '<div class="clear"></div>'+
            '</div>'+
        '</div>'+
        '<div class="clear"></div>'+
    '</div>';
    return template;
}

$(document).ready(function(){
    //评论表情
    if($('.argright').length!=0){
        $('.argright').parseEmotion();
    }


    //回复处理
    $(document).on('click','#add_response_do',function(){
        _this = $(this);
        if(is_login=='true'){
            var fid = $(this).attr('data-fid');
            var pid = $(this).attr('data-pid');
            var type = $(this).attr('data-type');
            var rid = $(this).attr('data-rid');
            var content = $('#response_content').val();
            if(content==''){
                swal('回复内容不能为空！');
            }else{
                _this.removeAttr('id');
                $.post(add_response_do_url,{'fid':fid,'pid':pid,'type':type,'content':content,'rid':rid},function(result){
                    if(result.data.code==0){
                        var userphoto = result.data.info.userphoto;
                        var username = result.data.info.username;
                        var content = result.data.info.content;
                        var format_time = result.data.info.format_time;
                        var rid = result.data.info.id;
                        var template = response_template(userphoto,username,content,format_time,rid);
                        $('.response_block').prepend(template);
                        $('#response_content').val('');
                        _this.attr('id','add_response_do');
                    }else{
                        swal(result.data.message);
                    }
                });
            }
        }else{
            jump_login_page();
        }
    });

    //@处理
    $(document).on('click','.response_to',function(){
        var rid = $(this).attr('data-rid');
        var username = $(this).attr('data-username');
        $('#add_response_do').attr('data-rid',rid);
        $('#response_content').val('@'+username+' ');
        $('#response_content').focus();
    });
});