(function($){
    $.fn.flexImages = function(options){
        var o = $.extend({ container: '.item', object: 'img', rowHeight: 180, maxRows: 0, truncate: false }, options);
        return this.each(function(){
            var $this = $(this), $items = $(o.container, $this), items = [], i = $items.eq(0), t = new Date().getTime();
            o.margin = i.outerWidth(true) - i.innerWidth();
            $items.each(function(){
                var w = parseInt($(this).data('w')),
                    h = parseInt($(this).data('h')),
                    norm_w = w*(o.rowHeight/h), 
                    obj = $(this).find(o.object);
                items.push([$(this), w, h, norm_w, obj, obj.data('src')]);
            });
            makeGrid($this, items, o);
            $(window).off('resize.flexImages'+$this.data('flex-t'));
            $(window).on('resize.flexImages'+t, function(){ makeGrid($this, items, o); });
            $this.data('flex-t', t)
        });
    }

    function makeGrid(container, items, o, noresize){
        var x, new_w, ratio = 1, rows = 1, max_w = container.width(), row = [], row_width = 0, row_h = o.rowHeight;

        // define inside makeGrid to access variables in scope
        function _helper(lastRow){
            if (o.maxRows && rows > o.maxRows || o.truncate && lastRow) row[x][0].hide();
            else {
                if (row[x][5]) { row[x][4].attr('src', row[x][5]); row[x][5] = ''; }
                row[x][0].css({ width: new_w, height: row_h }).show();
            }
        }

        for (i=0; i<items.length; i++) {
            row.push(items[i]);
            row_width += items[i][3] + o.margin;
            if (row_width >= max_w) {
                ratio = max_w / row_width, row_h = Math.ceil(o.rowHeight*ratio), exact_w = 0, new_w;
                for (x=0; x<row.length; x++) {
                    new_w = Math.ceil(row[x][3]*ratio);
                    exact_w += new_w + o.margin;
                    if (exact_w > max_w) new_w -= exact_w - max_w + 1;
                    _helper();
                }
               
                row = [], row_width = 0;
                rows++;
            }
        }
      
        for (x=0; x<row.length; x++) {
            new_w = Math.floor(row[x][3]*ratio), h = Math.floor(o.rowHeight*ratio);
            _helper(true);
        }

       
        if (!noresize && max_w != container.width()) makeGrid(container, items, o, true);
    }
}(jQuery));

//照片墙
$('#fleximg').flexImages({rowHeight: 120, truncate: 0, maxRows: 15});

//大图查看
$('#fleximg a').fancybox();

//
//上传反馈(图片,视频)
$('.upload_feedback_image').click(function(){
    if(is_login=='true'){
        $('#upload_feedback_img').trigger('click');
    }else{
        jump_login_page();
    }
});

$('.upload_feedback_video').click(function(){
    if(is_login=='true'){
        var aid = $(this).attr('data-aid');
        window.location.href = upload_feedback_video_url + '?fid=' + fid + '&aid=' + aid;
    }else{
        jump_login_page();
    }
});


$("#upload_feedback_img").change(function(){
    var aid = $(this).attr('data-aid');
    $.ajaxFileUpload({
        url: upload_img_url, //用于文件上传的服务器端请求地址
        secureuri: false, //是否需要安全协议，一般设置为false
        fileElementId: 'upload_feedback_img', //文件上传域的ID
        data:{
            aid:aid
        },
        dataType: 'json', //返回值类型 一般设置为json
        success: function (data, status)  //服务器成功响应处理函数
        {
            if(data.data.code!=0){
                swal(data.data.message);
            }else{
                var attachmentid = data.data.info;
                $.post(add_movie_feedback_do_url,{'aid':aid,'attachmentid':attachmentid},function(result){
                    if(result.data.code==0){
                        swal('上传反馈成功');
                    }else{
                        swal(result.data.message);
                    }
                });
            }

        },
        error: function (data, status, e)//服务器响应失败处理函数
        {
            swal(e);
        }
    });
});