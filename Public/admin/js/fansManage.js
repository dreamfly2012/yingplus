

$(function(){
    var key = 0;
    $('.yes').click(function(){
        $('#example').modal('show');
        key = $(this).attr('date-key');
    });
    $('.no').click(function(){
        //点击不属实，此时只给举报人发送举报不属实的消息
        var uid = $(this).attr('date-report');
        var touid = $(this).attr('date-reported');
        //给举报人发送消息
        $.ajax({
            url : saveMessageAboutReport_URL,
            type: "POST",
            data: {
                'uid'     : uid,
                'touid'   : touid,
                'flag'    : 1
            },
            dataType: "json",
            success: function(data){}
        });
    });
    //举报审核属实给经纪人和举报人发送的消息
    $('.btn-success').click(function(){
        var uid = $('.yes').attr('date-report');
        var touid = $('.yes').attr('date-reported');
        var content = $('#content').val();
        var id = $('.yes').attr('date-id');
        //给举报人发送消息
        $.ajax({
            url : saveMessageAboutReport_URL,
            type: "POST",
            data: {
                'uid'     : uid,
                'touid'   : touid,
                'flag'    : 0
            },
            dataType: "json",
            success: function(data){}
        });
        //给经纪人发送消息
        $.ajax({
            url : saveMessageAboutAgant_URL,
            type: "POST",
            data: {
                'uid'     : uid,
                'touid'   : touid,
                'content' : content
            },
            dataType: "json",
            success : function(data){}
        });
        //将审核表中的审核状态置为 1，并将审核状态置为已审核
        $.ajax({
            url : disposeReport_URL ,
            type: "POST",
            data: {
                'id'     : id
            },
            dataType: "json",
            success : function(data){
                if(data){
                    $('.flag_'+key).text('已审核');
                }
            }
        });
    });

    //处理粉丝申诉部分
    $('.disable').click(function(){
        $('#example').modal('show');
        key = $(this).attr('date-key');
    });

    $('.agree').click(function(){
        var uid = $('.yes').attr('date-report');
        var touid = $('.yes').attr('date-reported');
        key = $(this).attr('date-key');
        //同意需要给申诉者发送申诉合理的消息并且将审核状态改为已审核
    });
});