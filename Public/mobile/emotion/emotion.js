$(document).ready(function(){
    var expressionHtml = '<ul class="ui-carousel-inner face-panel-wrap">';
    for (var i = 1; i <= 5; i++) {
        expressionHtml += '<li class="ui-carousel-item face-panel face-panel-'+ i +'">';
        for(var j = 0; j < 20; j++){
            var n = 20*(i-1)+j;
            expressionHtml += '<span class="express" index="'+ n +'" alt="[em_'+ n +']"></span>';
        }
        expressionHtml += '<span class="express" index="-1" alt=""></span></li>';
    }
    expressionHtml += '</ul>';
    var bottomHtml = '<ol id="position" class="ui-carousel-indicators">' +
                        '<li class="js-active"></li>' +
                        '<li class=""></li>' +
                        '<li class=""></li>' +
                        '<li class=""></li>' +
                        '<li class=""></li>' +
                      '</ol>';
    expressionHtml += bottomHtml;
    $("#slider").append(expressionHtml);

    var slider =
      Swipe(document.getElementById('slider'), {
        continuous: true,
        callback: function(pos) {

          var i = bullets.length;
          while (i--) {
            bullets[i].className = ' ';
          }
          bullets[pos].className = 'js-active';
        }
      });
    var bullets = document.getElementById('position').getElementsByTagName('li');

    var curFocus = {
        fid: 'response_content',
        start: 0,
        end: 0
    };

    $('#response_content').blur(function() {
        curFocus.fid = 'response_content';
        curFocus.start = $(this).get(0).selectionStart;
        curFocus.end = $(this).get(0).selectionEnd;
    });

    //表情解析
    

    // 点击表情
    $('.express').on('click', function() {
        // 获取表情对应code
        var imgCode = $(this).attr('alt');
        // 获取编号判断是否为删除按钮
        var index = $(this).attr('index');
        var ta = document.querySelector('textarea');
        // 删除操作
        if(index == -1){
            if ($('#' + curFocus.fid).length) {
                var text = $('#' + curFocus.fid).val();
                // 获取光标之前的字符串
                var changedText = text.substr(0, curFocus.start);
                var len = changedText.length;
                var reg=/\[em_([0-9]*)\]$/g;
                // 删除表情code块或最后一个字符
                if(reg.test(changedText)){
                    changedText=changedText.replace(reg,"");
                }else{
                    changedText=changedText.substring(0,changedText.length-1);
                }
                var resText = changedText + text.substr(curFocus.end, text.length);
                $('#' + curFocus.fid).val(resText);
                // 调整光标位置
                curFocus.start = curFocus.end = curFocus.end - (len - changedText.length);
            }
        // 添加操作
        }else if ($('#' + curFocus.fid).length) {
            var text = $('#' + curFocus.fid).val();
            // 添加表情code块到光标位置
            var resText = text.substr(0, curFocus.start) + imgCode + text.substr(curFocus.end, text.length);
            $('#' + curFocus.fid).val(resText);
            curFocus.start = curFocus.end = curFocus.end + imgCode.length;
        }
    });
});

