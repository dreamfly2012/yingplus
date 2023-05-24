$(function() {  
    $("textarea[maxlength]").bind('input propertychange', function() {  
        var maxLength = $(this).attr('maxlength');  
        if ($(this).val().length > maxLength) {  
            $(this).val($(this).val().substring(0, maxLength));  
        }  
    });

    $("#msgform input:text, #msgform textarea").first().focus(); 

    $("#add").click(function(){
    	var content = $('#content').val();
    	var aj = $.ajax({  
		    url:add_msg_do_url,// 跳转到 action  
		    data:{  
		        'content':content
		    },  
		    type:'post',  
		    cache:false,  
		    dataType:'json',  
		    success:function(data) {  
		        if(data.msg =="true" ){  
		            window.location.reload();  
		        }else{  
		            alert("添加失败！");   
		        }  
		     },  
		     error : function() {  
		          // view("异常！");  
		          alert("异常！");  
		     }  
		});
    });
});