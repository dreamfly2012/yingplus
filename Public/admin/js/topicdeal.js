/**
 * 话题相关处理js
 */
$(function(){
    $(document).on('click','#topic',function(){
        var params = $("#topic_form").serialize();
        var test = testTime();
        if(test) {
            $.ajax({
                url: LookTopic_URL,
                type: "POST",
                data: params,
                dataType: "html",
                success: function (data) {
                    $('.index-table').html(data);
                }

            });
        }
    });


    $('#delete').click(function(){
        var params = $("input").serialize();
        $.ajax({
            url: DeleteTopic_URL,
            type: "POST",
            data: params,
            dataType : "json",
            success : function(data){
                $('.show-topic').addClass('none');
                var item;
                $.each(data,function(i,result){
                    item= "<tr class='test-show' id="+result.id+"><td><input type='checkbox' class='checkboxes' value='1' /></td><td>"+result.subject+"</td><td>"+result.nickname+"</td><td>"+result.forum_name+
                        "</td><td>"+result.admin_name+"</td><td>"+result.deletetime+"</td><td><a class='repair_topic' title='确定要恢复吗？' href='javascript:;' lid="+result.id+">恢复</a title='确定要恢复吗？' href='javascript:;'></td></tr>";
                    $('.deleteTopic').append(item);
                });
                $('.test-show').addClass('show-topic');
            }
        });
    });

    $('#jobManage').click(function(){
        var params = $("input").serialize();
        $.ajax({
            url: JobManage_URL,
            type: "POST",
            data: params,
            dataType : "json",
            success : function(data){
                $('.show').addClass('none');
                var item;
                $.each(data,function(i,result){
                    item= "<tr class='test'><td>"+result.name+"</td><td>"+result.chinesename+"</td><td>"+result.koreaname+
                        "</td><td>"+result.groupname+"</td><td>"+result.representative+"</td><td><a title='确定要编辑吗？' href="+result.url+">编辑</a></td></tr>";
                    $('.show_job').append(item);
                });
                $('.test').addClass('show');

            }
        });
    });
    function testTime(){
        if($(".startLimit").val()&&$(".endLimit").val()==''){
            $(".endLimit").css('border','1px solid red');
            return false;
        }
        if($(".startLimit").val()==''&&$(".endLimit").val()){
           $(".startLimit").css('border','1px solid red');
            return false;
        }
        return true;
    }

    $('.startLimit').bind('click',function(){
        $(".startLimit").css('border','1px solid #ccc');
    });
    $('.endLimit').bind('click',function(){
        $(".endLimit").css('border','1px solid #ccc');
    });

    $('#job_remand').click(function(){
        var test = testTime();
        if(test){
          var params = $("input").serialize();
          $.ajax({
                url:  JobRemand_URL,
                type: "POST",
                data:params,
                dataType :"html",
                success  : function(data){
                    $('.portlet-body').html(data);
                }

          });
        }
    });

    $(document).on('click','#jobManage_see',function(){
        var params = $("input").serialize();
        $.ajax({
            url:  LookJobManage_URL,
            type: "POST",
            data:params,
            dataType :"html",
            success  : function(data){
                $(".portlet-body").html(data);
            }

        });
    });

    $(document).on('click','.repair_delete_topic',function(){
        var id = $(this).attr("lid");
        //console.log(id);
        var repair=confirm("确定要恢复吗？");
        if (repair==true){
            $.ajax({
                url: TopicRepair_URL,
                type: "post",
                data: {id:id},
                dataType :"json",
                success  : function(data){
                    //console.log(data.code);
                    if(data.code=="2"){
                        $("tr").remove('#'+id);
                    }
                }
            });
        }

    });

    $(document).on('click','.delete_button',function(){
        var id = $(this).attr("lid");
       // console.log(id);
        var repair=confirm("确定删除吗？");
        if (repair==true){
            $.ajax({
                url: TopicDeleteRe_URL,
                type: "post",
                data: {id:id},
                dataType :"json",
                success  : function(data){
                    if(data.code=="1"){
                        $("tr").remove('#'+id);

                    }

                }
            });
        }

    });

    $(document).on('click','.cancel_button',function(){
        var id = $(this).attr("lid");
        var plus = confirm("确定取消加精吗？");
        if(plus==true){
            $.ajax({
                url: TopicCancelDigest_URL,
                type: "post",
                data: {id:id},
                dataType:"json",
                success : function(data){
                    if(data.code=="1"){
                        $("tr").remove('#'+id);
                    }
                }
            });
        }
    });

    $(document).on('click','.plus_button',function(){
        var id = $(this).attr("lid");
        var plus = confirm("确定加精吗？");
        if(plus==true){
            $.ajax({
                url: TopicDigest_URL,
                type: "post",
                data: {id:id},
                dataType:"json",
                success : function(data){
                    if(data.code=="1"){
                        $("tr").remove('#'+id);
                    }
                }
            });
        }
    });

    $(document).on('click','.setTop_button',function(){
        var id = $(this).attr("lid");
        var top = confirm("确定要置顶吗?");
        if(top==true){
            $.ajax({
                url: TopicIsTop_URL,
                type: "post",
                data: {id:id},
                dataType: "json",
                success : function(data){
                    if(data.code=="1"){
                        $("tr").remove('#'+id);
                    }
                }
            });
        }
    });

    $(document).on('click','.cancelTop_button',function(){
        var id = $(this).attr("lid");
        var top = confirm("确定取消置顶吗?");
        if(top==true){
            $.ajax({
                url: TopicCancelTop_URL,
                type: "post",
                data: {id:id},
                dataType: "json",
                success : function(data){
                    if(data.code=="1"){
                        $("tr").remove('#'+id);
                    }
                }
            });
        }
    });



        $(document).on('click','.fj',function(){
        //$('.fj').click(function(){
            var fj_this = '';
            fj_this = this;
            var uid= $(fj_this).children('input').val();
            $('#userid').val(uid);
            $('#example').modal('show');
        });
        $('#success').click(function(){
            $(fj_this).html('【已封禁】');
        });

});
