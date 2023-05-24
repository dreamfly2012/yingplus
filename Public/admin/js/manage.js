 $(function(){
    $(document).on("click",'#choose-all',function(){
        if(this.checked){
            $(":checkbox[name='check']").prop('checked',true);//attr("checked",true);
        }else{
            $(":checkbox[name='check']").prop('checked',false);
        }
    });


    $(document).on('click','#all-delete',function(){
        var repair=confirm("确定要批量删除吗？");
        if (repair==true) {
            var array = {};
            var i =0;
            $(":checkbox[name='check']").each(function() {
                if (this.checked) {
                    array["test["+i+"]"] = $(this).attr("lid");
                    $("tr").remove('#'+$(this).attr("lid"));
                    i++;
                }

            });
            $.ajax({
                type: "POST",
                url: TopicAllDELETETop_URL,                
                data: array,
                dataType: "json",
                success: function (data) {
                }

            });
        }

    });

    $(document).on('click','#all-plus',function(){
        var repair=confirm("确定要批量加精吗？");
        if (repair==true) {
            var array = {};
            var i =0;
            $(":checkbox[name='check']").each(function() {
                if (this.checked) {
                    array["test["+i+"]"] = $(this).attr("lid");
                    $("tr").remove('#'+$(this).attr("lid"));
                    i++;
                }

            });
            $.ajax({
                type: "POST",
                url: TopicAllPlusTop_URL,
                //traditional: true,
                data:array,
                success: function (data) {

                }

            });
        }

    });

    $(document).on('click','#all-top',function(){
        var repair=confirm("确定要批量置顶吗？");
        if (repair==true) {
            var array = {};
            var i =0;
            $(":checkbox[name='check']").each(function() {
                if (this.checked) {
                    array["test["+i+"]"] = $(this).attr("lid");
                    $("tr").remove('#'+$(this).attr("lid"));
                    i++;
                }
            });
            $.ajax({
                type: "POST",
                url: TopicAllTop_URL,
                data: array,
                success: function (data) {
                }

            });
        }

    });

    $(document).on('click','#all-repair',function(){
        var repair=confirm("确定要批量恢复吗？");
        if (repair==true) {
            var array = {};
            var i =0;
            $(":checkbox[name='check']").each(function() {
                if (this.checked) {
                    array["test["+i+"]"] = $(this).attr("lid");
                    $("tr").remove('#'+$(this).attr("lid"));
                    i++;
                }
            });
            $.ajax({
                type: "POST",
                url: TopicAllRepairTop_URL,
                data: array,
                success: function (data) {
                }

            });
        }

    });

    $(document).on('click','#cancel-plus',function(){
        var repair=confirm("确定要批量取消加精吗？");
        if (repair==true) {
            var array = {};
            var i =0;
            $(":checkbox[name='check']").each(function() {
                if (this.checked) {
                    array["test["+i+"]"] = $(this).attr("lid");
                    $("tr").remove('#'+$(this).attr("lid"));
                    i++;
                }
            });
            $.ajax({
                type: "POST",
                url: TopicAllCancelPlusTop_URL,
                data: array,
                success: function (data) {
                }

            });
        }

    });

    $(document).on('click','#cancel-top',function(){
        var repair=confirm("确定要批量取消置顶吗？");
        if (repair==true) {
            var array = {};
            var i =0;
            $(":checkbox[name='check']").each(function() {
                if (this.checked) {
                    array["test["+i+"]"] = $(this).attr("lid");
                    $("tr").remove('#'+$(this).attr("lid"));
                    i++;
                }
            });
            $.ajax({
                type: "POST",
                url: TopicAllCancelTop_URL,
                data: array,
                success: function (data) {
                }

            });
        }

    });
    $(document).on('click','#quxiao',function(){
        history.back(-1);
    });
    $(document).on('click','img',function(){
        $(this).css('z-index','199');
        if(parseInt($(this).css('width'))==60){
            $(this).animate({position:'absolute',width:'800px',height:'600px'},200);
        }else{
            $(this).animate({position:'absolute',width:'60px',height:'60px'},200);
        }

    });

   $(document).on('click','.edit_config',function(){
          var name = $(this).parent().prev().prev().attr('lid');
          var value = $(this).parent().prev().attr('lid');
          var config_id = $(this).parent().attr('lid');        
          $('.config_name').val(name);
          $('.config_value').val(value);
          $('.config_id').val(config_id);
          $('.config_all').show();
          $('.edit_var_value').show();
   });
   
   $(document).on('click','.config_reset',function(){
         $('.config_all').hide();
         $('.edit_var_value').hide();
   });
   $(document).on('click','.config_add',function(){
         $('.config_all').hide();
         $('.add_var_value').hide();
   });
   
    $(document).on('click','.email_add',function(){
         $('.config_all').hide();
         $('.add_var_value').hide();
   });
   $(document).on('click','.delete_config',function(){
        var dalete=confirm("确定要删除变量吗？");
        if (dalete==true){   
         var config_id = $(this).parent().attr('lid');  
         $(this).parent().parent().remove();   
         $.ajax({
                type: "POST",
                url: DELETEDATA_URL,
                data: {id:config_id},
                dataType: "json",
                success: function (data) {
                }
           });
        }
   });
   
   $(document).on('click','.edit_email',function(){
          var name = $(this).parent().prev().attr('lid');           
          var email_update_id = $(this).parent().attr('lid');        
          $('.email_update').val(name);          
          $('.email_update_id').val(email_update_id);
          $('.config_all').show();
          $('.edit_var_value').show();
   });
   $(document).on('click','.delete_email',function(){
        var dalete=confirm("确定要删除邮箱吗？");
        if (dalete==true){   
         var email_id = $(this).parent().attr('lid');     
         var $this=$(this);
         $.ajax({
                type: "POST",
                url: DELETE_EMAIL_DATA_URL,
                data: {id:email_id},
                dataType: "json",
                success: function (data) {
                  if(data.status==1){
                    $this.parent().parent().remove();
                  }
                }
           });
        }
   });
    $(document).on('click','#add_var',function(){
          $('.config_all').show();
          $('.add_var_value').show();
    });
    
    $(document).on('click','.tongbu_file',function(){
        var file=confirm("确定要同步数据库吗？");
        if (file==true){
             $.ajax({
                type: "POST",
                url: DATATONGBU_URL,               
                dataType: "json",
                success: function(data) {
                     if(data.code==1){
                        alert('同步成功');                        
                    }else{
                        alert('同步失败');
                    }
                }
           });
        }
    });
    $(document).on('click','#add_sql',function(){
        var file=confirm("确定要新变量导进数据库吗？");
        if (file==true){
             $.ajax({
                type: "POST",
                url: ADDTONGBU_URL,               
                dataType: "json",
                success: function(data) {
                     if(data.code==1){
                         location.reload(RELOADTONGBU_URL);
                    }
                }
           });
        }
    });
    
    $(document).on('click','.delete_forum',function(){
        var lid = $(this).attr('lid');           
        if(confirm('确定删除工作室吗?')){
            $(this).parent().parent().remove();
           $.ajax({
               type: "POST",
               url: DeleteForumData_URL,
               data:{id:lid},
               dataType: 'json',
               success:function(data){
                   
               }
           }); 
        }        
    });

     $(document).on('click','.delete_block',function(){
        var lid = $(this).parent().attr('lid');           
        if(confirm('确定删除这条记录吗?')){
            $(this).parent().parent().remove();
           $.ajax({
               type: "POST",
               url: DeleteBlockData_URL,
               data:{id:lid},
               dataType: 'json',
               success:function(data){
                   // if(data.content=='1'){

                   // }
               }
           }); 
        }        
    });


     $('.nickname').keyup(function(e) {
        var currKey=e.keyCode||e.which||e.charCode;
        if(currKey != 38 && currKey != 40 ){
            var search = $(".nickname").val();
            if(search.trim()!=""&&search.trim()!=null){
            $.post(SEARCH_USER_NAME, {search: search}, function (result) {
                     var length = result.user.length;
                     var  temp_search = '';
                     if(length>0){
                        for (i = 0; i < length; i++) {
                            temp_search += "<li class='user_message' data-id='" + result.user[i].id + "' lid = '"+result.user[i].nickname +"'>" +result.user[i].nickname + "</li>";
                        }
                       $('.user_name').html(temp_search);
                       $('.user_name').show();
                    }else{
                        var content = "名字不存在";
                        $('.user_name').html(content);
                        $('.user_name').show();
                    }
            });
          }else{
                
                $('.user_name').hide();
            }
        }
    });
    
    $(document).on('click','.user_message',function(){
        var user_message_value  = $(this).attr('lid');
        var id = $(this).attr('data-id');
        $('.nickname').val(user_message_value);
        $('.uid').val(id);
        $('.user_name').hide();
    });
});