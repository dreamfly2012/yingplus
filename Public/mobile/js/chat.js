function self_chat_template(username,content,userphoto,rid,uid){
	var template = '<div class="argitem" data-rid="'+rid+'">'+
        '<div class="argright fl">'+
            '<h2 class="tr myself">'+
                username+            
            '</h2>'+
            '<div>'+
                '<div class="arrow_2 fr">'+
                    '<div class="arrow_right_1">'+
                    '</div>'+
                    '<div class="arrow_right_2">'+
                    '</div>'+
                '</div>'+
                '<p class="fr tr response_content">'+
                 content +
                '</p>'+
            '</div>'+
        '</div>'+
        '<b class="fr">'+
            '<a href="javascript:;" title="" class="response_to" data-username="'+username+'" data-uid="'+uid+'" data-rid="'+rid+'">'+
                '<img src="'+userphoto+'" class="user-photo">'+
            '</a>' +
        '</b>'+
        '<div class="clear">'+
        '</div>'+
    '</div>';
    return template;
}

function other_chat_template(username,content,userphoto,rid,uid){
	var template = '<div class="argitem" data-rid="'+rid+'">'+
        '<b class="fl">'+
            '<a href="javascript:;" title="" class="response_to" data-username="'+username+'" data-uid="'+uid+'" data-rid="'+rid+'">'+
                '<img src="'+userphoto+'" class="user-photo"/>'+
            '</a>'+
        '</b>'+
        '<div class="argright fl">'+
            '<h2>'+
                username+
            '</h2>'+
            '<div>'+
                '<div class="arrow_1 fl">'+
                    '<div class="arrow_left_1">'+
                    '</div>'+
                    '<div class="arrow_left_2">'+
                    '</div>'+
                '</div>'+
                '<p class="fl response_content">'+
                    content+
                '</p>' +
            '</div>'+
        '</div>'+
        '<div class="clear">'+
        '</div>'+
    '</div>';
    return template;
}


//定时获取新信息
function getLiveInfo(){
    var lastid = $('.argitem:last').attr('data-rid');
	htmlobj=$.ajax({
        type:"POST",
        url:get_latest_response_url,
        data:{
            'pid':pid,
            'type':type,
            'lastid':lastid
        },
        dataType:'json',
        success:function(data) {
            if(data.data.info.length != 0){
                var length = data.data.info.length;
                var info = data.data.info;

                var template_arr = [];
                for(var i=length-1;i>=0;i--){
                	var username = info[i].username;
                	var content = info[i].content;
                	var userphoto = info[i].userphoto;
                	var rid = info[i].id;
                	var ruid = info[i].uid;
                	if(ruid==uid){
						template_arr.push(self_chat_template(username,content,userphoto,rid,ruid)); 
					}else{
						template_arr.push(other_chat_template(username,content,userphoto,rid,ruid)); 
					}
				}
                $(".chat_dialog").append(template_arr.join(''));
                $('.chat_dialog').parseEmotion();
                window.scrollTo(0,document.body.scrollHeight+100);
            }
        },
        async:false
    });
}

setInterval("getLiveInfo()",8000);
				


$(document).ready(function(){
	//表情
	$('.chat_dialog').parseEmotion();

	//@
	$(document).on('click','.response_to',function(){
		var username = $(this).attr('data-username');
		var rid = $(this).attr('data-rid');
		$('#add_response_do').attr('data-rid',rid);
		$('#response_content').val('@'+username+' ');
		$('#response_content').focus();
	});

	window.scrollTo(0,document.body.scrollHeight+100);

	//查看更多
	$(document).on('click','.see_more',function(){
		var _this = $(this);
		var p = $(this).attr('data-page');
		var pid = $(this).attr('data-pid');
		var type = $(this).attr('data-type');
		$.post(get_more_message_url,{'p':p,'pid':pid,'type':type},function(result){
			if(result.data.code==0){
				var length = result.data.info.data.length;
				var info = result.data.info.data;
				var template = '';
				if(length!=0){
					for(i=length-1;i--;i>=0){
						var username = info[i].username;
						var content = info[i].content;
						var userphoto = info[i].userphoto;
						var rid = info[i].id;
						var ruid = info[i].uid;
						if(ruid==uid){
							template += self_chat_template(username,content,userphoto,rid,ruid); 
						}else{
							template += other_chat_template(username,content,userphoto,rid,ruid); 
						}
						
					}
					_this.attr('data-page',parseInt(p)+1);
					$(template).insertAfter(_this);
					$('.chat_dialog').parseEmotion();
				}else{
					_this.removeClass('see_more').addClass('no_more');
					_this.text('没有更多信息了');
				}
			}
		});
	});

	

	//回复处理
	$(document).on('click','#add_response_do',function(){
		var _this = $(this);
		if(is_login=='true'){
			var fid = $(this).attr('data-fid');
			var pid = $(this).attr('data-pid');
			var type = $(this).attr('data-type');
			var rid = $(this).attr('data-rid');
			var content = $('#response_content').val();
			var content = trim(content);
			if(content==''){
				swal('回复内容不能为空！');
			}else{
				_this.removeAttr('id');
				$.post(add_response_do_url,{'fid':fid,'pid':pid,'type':type,'content':content,'rid':rid},function(result){
					if(result.data.code==0){
						var username = result.data.info.username;
						var content = result.data.info.content;
						var userphoto = result.data.info.userphoto;
						var rid = result.data.info.id;
						var uid = result.data.info.uid;						
						var template = self_chat_template(username,content,userphoto,rid,uid);
						$('.chat_dialog').append(template);
						$('.chat_dialog').parseEmotion();
						$('#response_content').val('');
						window.scrollTo(0,document.body.scrollHeight+100);
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

	//选择表情
	$('.choose_face').bind({
		click: function(event){
			if(! $('#sinaEmotion').is(':visible')){
				$(this).sinaEmotion();
				var top = parseInt($('#sinaEmotion').css('top'));
				var top = top-200;
				$('#sinaEmotion').css({'left':'0','top':top+'px'});
				event.stopPropagation();
			}
		}
	});

	//上传图片
	$('.choose_img').click(function(){
		if(is_login=='true'){
			$('#upload_message_image').trigger('click');
		}else{
			jump_login_page();
		}
		
	});

	//上传图片到云
	$(document).on("change","#upload_message_image",function(){
		var fid = $(this).attr('data-fid');
		$.ajaxFileUpload({
			url: upload_img_url, //用于文件上传的服务器端请求地址
			secureuri: false, //是否需要安全协议，一般设置为false
			fileElementId: 'upload_message_image', //文件上传域的ID
			data:{

			},
			dataType: 'json', //返回值类型 一般设置为json
			success: function (data, status)  //服务器成功响应处理函数
			{
				//尝试进行json解析
				if(data.data.code==0){
					$.post(get_attachment_info_url,{'id':data.data.info},function(result){
						if(result.data.code==0){
							var fid = $('#add_response_do').attr('data-fid');
							var pid = $('#add_response_do').attr('data-pid');
							var type = $('#add_response_do').attr('data-type');
							var rid = $('#add_response_do').attr('data-rid');
							var content = '<img src="'+result.data.info.remote_url+'"/>';
							
							$.post(add_response_do_url,{'fid':fid,'pid':pid,'type':type,'content':content,'rid':rid},function(result){
								if(result.data.code==0){
									var username = result.data.info.username;
									var content = result.data.info.content;
									var userphoto = result.data.info.userphoto;
									var rid = result.data.info.id;
									var uid = result.data.info.uid;						
									var template = self_chat_template(username,content,userphoto,rid,uid);
									$('.chat_dialog').append(template);
									$('#response_content').val('');
									$('.chat_dialog')[0].scrollTop = $('.chat_dialog')[0].scrollHeight+100;
								}else{
									swal(result.data.message);
								}
							});
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

	//工作室人搜索
	$('#search_user').keydown(function(e){
		if(e.keyCode==13){
			$('.search_user').trigger('click');
		}
	});
	

	$('.search_user').click(function(e){
		var nickname = $('#search_user').val();
		var fid = $(this).attr('data-fid');
		var type= $(this).attr('data-type');
		if(nickname==''){
			return false;
		}else{
			$.post(search_user_url,{'keyword':nickname},function(result){
				if(result.data.code==0){
					window.location.href = result.data.info;
				}
			});
		}
	});


});