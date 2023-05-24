$(document).ready(function () {
    //获取影院信息
    $.post(get_activity_info_url, {'id': aid}, function (result) {
        if(result.data.code==0){
            var enrolltotal = result.data.info.enrolltotal;
            var enrollnum = result.data.info.enrollnum;
            $(".movie_title").text(result.data.info.movie_title);
            $("#cinemaname").text(result.data.info.cinemaname);
            $("#detailaddress").text(result.data.info.detailaddress_format);
            $("#time").text(result.data.info.holdstart_format);
            $("#sponsor").text(result.data.info.sponsor);
            $("#num").text(enrollnum+' / '+enrolltotal);
        }
    });

    //执行拍照
    $("#scan_qrcode").click(function(){
        $("#upload_qrcode").trigger('click');
    });
    $(document).on("change", "#upload_qrcode", function () {
        $.ajaxFileUpload({
            url: save_capture_img_url, //用于文件上传的服务器端请求地址
            secureuri: false, //是否需要安全协议，一般设置为false
            fileElementId: 'upload_qrcode', //文件上传域的ID
            data: {'aa': 'aa'},
            dataType: 'json', //返回值类型 一般设置为json
            success: function (data, status) //服务器成功响应处理函数
            {
                if (data.data.code != 0) {
                    alert(data.data.info);
                } else {
                    var attachmentid = data.data.info;
                    //通过id获取url
                    $.post(get_qrcode_content_url, {attachmentid: attachmentid}, function (result) {
                        if(result.data.code==1){
                            alert(result.data.message);
                        }else if(result.data.code==0){
                            window.location.href = result.data.info;
                        }else{
                            console.log('500 server error');
                        }

                    });
                }

            },
            error: function (data, status, e) {
                alert(e);
            }
        });
    });

    //报名列表
    $.post(get_enroll_info_url,{'id':aid},function(result){
        if(result.data.code==0){
            var arr = [];
            for(var i=0;i<result.data.info.length;i++){
                var status = (result.data.info[i].check_status == 0) ? '未到场':'已到场';
                var status_class = (result.data.info[i].check_status == 0) ? 'un-exchange':'exchange';
                arr.push('<li>'
                +'<div class="txt">'
                    +'<div class="line clearfix">'
                        +'<a class="name" href="javascript:;">'+result.data.info[i].username+' <span>（购票：'+result.data.info[i].goods_amount+'张）</span> </a>'
                        +'<span class="'+status_class+'">'+status+'</span>'
                    +'</div>'
                    +'<div class="line clearfix">'
                        +'<div class="num">手机号：：<span>'+result.data.info[i].telephone+'</span></div>'
                        +'<span class="time">'+result.data.info[i].pay_time_format+'</span>'
                    +'</div>'
                +'</div>'
            +'</li>');
            }
            $("#enroll_list").html(arr.join(''));
        }
    })

});