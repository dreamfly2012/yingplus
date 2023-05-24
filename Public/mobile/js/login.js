$(document).ready(function(){
	//登录
	$(document).on("click",'#login',function(){
		$("#login_submit").trigger('click');
	});
	$(document).on("click",'#login_submit',function(){
		var telephone = $('#telephone').val();
		var password = $('#password').val();
		var remember = 1;
		if(telephone==""||password==""){
			//执行默认事件
		}else{
			$.post(login_handle_url,{'telephone':telephone,'password':password,'remember':remember},function(result){
				var returnurl = getParameterValue('returnurl');
				returnurl = (returnurl == null) ? index_url : returnurl;
				if(result.data.code==0){
					window.location.href = decodeURI(returnurl);
				}else{
					swal(result.data.message);
				}
			});
		}
	});
});