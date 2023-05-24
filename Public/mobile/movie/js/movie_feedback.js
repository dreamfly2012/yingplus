var stackgrid = new Stackgrid;

$(document).ready(function() {
    //瀑布流照片墙
    stackgrid.config.columnWidth = 120;
    stackgrid.config.gutter = 20;
    stackgrid.config.isFluid = false;
    stackgrid.config.numberOfColumns = 4;
    stackgrid.config.resizeDebounceDelay = 350;
    stackgrid.config.is_optimized = 1;
    stackgrid.config.layout = "optimized";
    stackgrid.initialize("#grid-container", ".grid-item");

    $('#as').diyUpload({
        url: upload_movie_feedback_url,
        success: function(data) {
            if (data.data.code == 0) {
                var attachmentid = data.data.info;
                $(".diySuccess").eq($(".diySuccess").length - 1).attr('data-id', data.data.info);
                $(".diyCancel").eq($(".diyCancel").length - 1).attr('data-id', data.data.info);
                $.post(add_movie_feedback_url, {
                    'attachmentid': attachmentid,
                    'aid': aid
                }, function(result) {
                    if (result.data.info.isvideo == 1) {
                        var id = result.data.info.id;
                        $.post(upload_yun_video_url, {
                            'feedback_id': id,
                            'attachmentid': attachmentid
                        }, function(result) {
                            console.log(result.data);
                            //视频
                        });
                    } else {
                        //图片
                        item = $('<a class="grid-item fancybox" rel="imagegallery" href="'+result.data.info.img_url_format+'"><img src="'+result.data.info.img_url_format+'"></a>');
                
                        // Append it to the grid-container.
                        item.appendTo("#grid-container");
                        stackgrid.config.columnWidth = 120;
                        stackgrid.config.gutter = 20;
                        stackgrid.config.isFluid = false;
                        stackgrid.config.numberOfColumns = 4;
                        stackgrid.config.resizeDebounceDelay = 350;
                        stackgrid.config.is_optimized = 1;
                        stackgrid.config.layout = "optimized";
                        stackgrid.initialize("#grid-container", ".grid-item");
                    }
                });
            } else if (data.data.code == 2) {
                var returnurl = window.location.href;
                jump_login_page(returnurl);
            } else {
                $(".diySuccess").each(function() {
                    if ($(this).attr('data-id') == undefined) {
                        $(this).parents('li').remove();
                    }
                })
                alert(data.data.message);
            }
        },
        error: function(err) {
            console.info(err);
        },
        buttonText: '选择',
        chunked: true,
        // 分片大小
        chunkSize: 50 * 1024 * 1024,
        //最大上传的文件数量, 总文件大小,单个文件大小(单位字节);
        fileNumLimit: 9,
        fileSizeLimit: 50 * 1024 * 1024,
        fileSingleSizeLimit: 50 * 1024 * 1024,
        accept: {
            title: "图片和视频",
            extensions: "gif,jpg,jpeg,bmp,png,mp4,flv,3gp,rmvb,avi",
            mimeTypes: "image/*,video/*"
        }
    });
    
    //添加编辑按钮TODO:暂时直接删除
    //$("#as").append('<div class="webuploader-pick" id="edit_upload">编辑</div>');
    //获取已上传的文件
    $.post(get_self_feedback_url, {
        aid: aid
    }, function(result) {
        if (result.data.code == 0) {
            var html = [];
            for (var i = 0; i < result.data.info.length; i++) {
                html.push('<li class="diyUploadHover">' + '<div class="viewThumb"><img src="' + result.data.info[i].img_url_format + '"></div>' + '<div class="diyCancel" data-id="' + result.data.info[i].id + '"></div>' + '<div class="diySuccess" data-id="' + result.data.info[i].id + '"></div>' + '<div class="diyFileName"></div>' + '<div class="diyBar">' + '<div class="diyProgress"></div>' + '<div class="diyProgressText">0%</div>' + '</div>' + '</li>');
            }
            $(".fileBoxUl").append(html.join(''));
        }
    });
    //获取反馈视频
    $.post(get_feedback_url, {
        aid: aid
    }, function(result) {
        if (result.data.code == 0) {
            var imghtml = [];
            var videohtml = [];
            for (var i = 0; i < result.data.info.length; i++) {
                if (result.data.info[i].isvideo == 1) {
                    videohtml.push('<a href="' + result.data.info[i].show_video_url + '">' + '<img src="' + result.data.info[i].img_url_format + '">' + '</a>');
                } else {
                    imghtml.push('<a class="grid-item fancybox" rel="imagegallery" href="'+result.data.info[i].img_url_format+'"><img src="'+result.data.info[i].img_url_format+'"></a>');
                }
            }
            $(".video-box").html(videohtml.join('')+'<div class="clearfix"></div>');
            $("#grid-container").html(imghtml.join(''));
            //瀑布流照片墙
            stackgrid.config.columnWidth = 100;
            stackgrid.config.gutter = 40;
            stackgrid.config.isFluid = 1;
            stackgrid.config.numberOfColumns = 4;
            stackgrid.config.resizeDebounceDelay = 350;
            stackgrid.config.is_optimized = 1;
            stackgrid.config.layout = "optimized";
            stackgrid.initialize("#grid-container", ".grid-item");
        }
    });

    //fancybox彈出
    $(".fancybox").fancybox({
        width:'100%',
        height:'100%'
    });
    //获取电影相关信息
    $.post(get_movie_info_url, {
        aid: aid
    }, function(result) {
        if (result.data.code == 0) {
            $("#activity_rule").html(result.data.info.rule);
            $("#movie_instruction").html(result.data.info.desc);
        }
    });
    //编辑删除
    $(document).on('click', '#edit_upload', function() {
        $(".diySuccess").css('display', 'none');
        $(".fileBoxUl li").addClass('diyUploadHover');
    })
        //删除反馈图片和视频
    $(document).on('click', ".diyCancel", function() {
        var ids = $(this).attr('data-id');
        var $this = $(this);
        if (ids == undefined) {
            return false;
        }
        if (confirm('是否真的要删除?') && ids) {
            $.post(delete_movie_feedback_url, {
                ids: ids
            }, function(result) {
                $this.parents('li').remove();
            });
        }
    })

    //feedback
    $(document).on('click','#feedback',function(result){

    })
});