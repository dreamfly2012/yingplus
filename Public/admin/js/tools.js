/**
 * Created by Administrator on 2015/10/7.
 */

$(function(){
    getCities();
    var pid = $('#province').val();
    if(pid == ''){
        $('#city').append("<option value=''>--请选择--</option>");
    }
    $('#province').change(function(){
        getCities();
        var pid = $('#province').val();
        if(pid == ''){
            $('#city').append("<option value=''>--请选择--</option>");
        }
    });
});

function getCities(){
    var pid = $('#province').val();
    $.ajax({
        type : 'POST',
        url : province_city_url,
        data : "pid="+pid,
        dataType : 'json',
        success : function(data){
            $('#city').find('option').remove();
            for(var i=0;i<data.length;i++){
                if(data[i]['id'] == cid){
                    $('#city').append("<option selected value='"+data[i]['id']+"'>"+data[i]['name']+"</option>");
                }else{
                    $('#city').append("<option value='"+data[i]['id']+"'>"+data[i]['name']+"</option>");
                }
            }
        },
        async: false
    });
}

$(function(){
    $('#saveActivity').submit(function(e){
        var enrolltotal = $('#enrolltotal').val();
        var forumname = $('#forumname').val();
        var isCheckStar = true;
        enrolltotal = parseInt(enrolltotal);
        
        $.ajax({
            type :'POST',
            url : checkStar_URL,
            data : "starname="+forumname,
            dataType : 'json',
            success : function(data){
                if(!data){
                    isCheckStar = false;
                }
            },async:false,
        });
        if(!isCheckStar){
            alert('该星吧不存在！！');
            return false;
        }

    });
});
