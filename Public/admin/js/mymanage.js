/**
 * Created by Administrator on 2015/10/7.
 */

//封禁用户的操作
$(function(){
    $('#starname').change(function(){
        var starname = $('#starname').val();
        $.ajax({
            type :'POST',
            url : checkStar_URL,
            data : "starname="+starname,
            dataType : 'json',
            success : function(data){
                if(!data){
                    alert('该明星不存在！！');
                }
            }
        });
    });

    //对封禁进行操作
    $('.banuser').click(function(){
        var banVal = $(this).text();
        var id = $(this).attr('data-id');
        var status = $(this).attr('data-status');
        var banuser = this;
        $.ajax({
            type : 'POST',
            url :banUserManage_URL,
            data :{
                'id': id,
                'status' : status
            },
            dataType : 'json',
            success : function(data){
                if(status == '1'){
                    $(banuser).text('【已解禁】');
                }else{
                    $(banuser).text('【取消封禁】');
                    $(banuser).attr('data-status',1);
                }
            }
        });
    });
});

//查找用户的js操作

$(document).ready(function(e){
    var flag = 0;
    var conditionArr = new Array();
    $('#add').click(function(){
        $('#error').css('display','none');
        var $select = $('#select').val();
        var $beginnum = $('#beginnum').val();
        var $endnum = $('#endnum').val();
        var beginnum = parseInt($beginnum);
        var endnum = parseInt($endnum);
        if($select == ''||$beginnum == '' ||$endnum == ''){
            $('#error').css('display','block');
        }else if(isNaN(beginnum) || isNaN(endnum)||beginnum < 0 || endnum < 0 || (endnum - beginnum) <=0){
            $('#error').css('display','block');
        }else{
            flag++;
            var selectCondition = $('#select').val();
            var beginnum = $('#beginnum').val();
            var endnum = $('#endnum').val();
            var tem = selectCondition +':'+ beginnum + ':' + endnum;
            conditionArr[flag-1] = tem;
            var html = "<label>条件："+selectCondition+" 在"+beginnum+"-"+endnum+" 之间"+"<img class='delcondition' style='margin-left: 280px' src='/public/admin/img/u272.png'/></label>";
            $('#panel').append(html);
            $('#panel').show();
            //能追加上之后把追加的内容从下拉列表去除
            $("select[name=condition] option").each(function() {
                if ($(this).val() == selectCondition) {
                    $(this).remove();
                    $('#beginnum').val('');
                    $('#endnum').val('');
                }
            });
        }
    });

    $(document).on('click','.delcondition',function(e){
        if($(this).parent().length>0){
            var deltext = $(this).parent().text().split('：')[1].split(' ')[0];
            for(var i=0;i<conditionArr.length;i++){
                if(conditionArr[i].split(':')[0] == deltext){
                    conditionArr[i] = '0';
                }
            }
            $(this).parent().remove();
        }else{
            $('#panel').hide();
        }

    });
    $('#find').click(function(){
        var nickname = $('#nickname').val();
        var begintime = $('#d4311').val();
        var endtime = $('#d4312').val();
        $.ajax({
            type : 'POST',
            url : getFansInfoList_URL,
            data : {
                'nickname':nickname,
                'begintime':begintime,
                'endtime':endtime,
                'selectArr':conditionArr
            },
            dataType : 'html',
            success : function(data){
                $('#fansShow').html(data);
            }
        });
        //$('#panel label').remove();
        //for(var i=0;i<conditionArr.length;i++){
        //    conditionArr[i]='0';
        //}

    });
});


//处理活动表单提交字段限制

$(function(){
    $('#activity').submit(function(e){
        var $beginnum = $('#beginnum').val();
        var $endnum = $('#endnum').val();
        var beginnum = parseInt($beginnum);
        var endnum = parseInt($endnum);
    });


    //首页推荐活动的设置
    $('.shouye').click(function(){
        var aid = $(this).attr('data-id');
        $.ajax({
            type : 'POST',
            url : getHomeRA_URL,
            data : {},
            dataType : 'json',
            success : function(data){
                if(data){
                    alert('首页推荐活动成功！！');
                    $.ajax({
                        type : 'POST',
                        url : homeRecommendActivity_URL,
                        data : {
                            'aid':aid,
                        },
                        dataType : 'html',
                        success : function(data){}
                    });
                }else{
                    alert('首页推荐活动总数已经超过3个，请到【首页推荐活动管理】删除活动再进行此操作！！');
                }
            }
        });
    });
});