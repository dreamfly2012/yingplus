(function(){

    var editor = null;

    UM.registerWidget('emotion',{

        tpl: "<link type=\"text/css\" rel=\"stylesheet\" href=\"<%=emotion_url%>emotion.css\">" +
            "<div class=\"edui-emotion-tab-Jpanel edui-emotion-wrapper\">" +
            "<ul class=\"edui-emotion-Jtabnav edui-tab-nav\">" +
            "<li class=\"edui-tab-item\"><a data-context=\".edui-emotion-Jtab0\" hideFocus=\"true\" class=\"edui-tab-text\">张小盒</a></li>" +
            "<li class=\"edui-tab-item\"><a data-context=\".edui-emotion-Jtab1\" hideFocus=\"true\" class=\"edui-tab-text\">豌豆荚</a></li>" +
            "<li class=\"edui-tab-item\"><a data-context=\".edui-emotion-Jtab2\" hideFocus=\"true\" class=\"edui-tab-text\">冷先森</a></li>" +
            "<li class=\"edui-tab-item\"><a data-context=\".edui-emotion-Jtab3\" hideFocus=\"true\" class=\"edui-tab-text\">兔斯基</a></li>" +
            "<li class=\"edui-tab-item\"><a data-context=\".edui-emotion-Jtab4\" hideFocus=\"true\" class=\"edui-tab-text\">暴漫动态</a></li>" +
            "<li class=\"edui-tab-item\"><a data-context=\".edui-emotion-Jtab5\" hideFocus=\"true\" class=\"edui-tab-text\">暴漫静态</a></li>" +
            "<li class=\"edui-emotion-tabs\"></li>" +
            "</ul>" +
            "<div class=\"edui-tab-content edui-emotion-JtabBodys\">" +
            "<div class=\"edui-emotion-Jtab0 edui-tab-pane\"></div>" +
            "<div class=\"edui-emotion-Jtab1 edui-tab-pane\"></div>" +
            "<div class=\"edui-emotion-Jtab2 edui-tab-pane\"></div>" +
            "<div class=\"edui-emotion-Jtab3 edui-tab-pane\"></div>" +
            "<div class=\"edui-emotion-Jtab4 edui-tab-pane\"></div>" +
            "<div class=\"edui-emotion-Jtab5 edui-tab-pane\"></div>" +
            "</div>" +
            "<div class=\"edui-emotion-JtabIconReview edui-emotion-preview-box\">" +
            "<img src=\"<%=cover_img%>\" class=\'edui-emotion-JfaceReview edui-emotion-preview-img\'/>" +
            "</div>",
        sourceData: {
            emotion: {
                tabNum:6, //切换面板数量
                SmilmgName:{ 'edui-emotion-Jtab0':['zxh_0', 50], 'edui-emotion-Jtab1':['wdj_', 30], 'edui-emotion-Jtab2':['lxs_0', 30], 'edui-emotion-Jtab3':['t_00', 40], 'edui-emotion-Jtab4':['bd_00', 40], 'edui-emotion-Jtab5':['b_00', 60]}, //图片前缀名
                imageFolders:{ 'edui-emotion-Jtab0':'pczxh/', 'edui-emotion-Jtab1':'wdj/', 'edui-emotion-Jtab2':'lxs/', 'edui-emotion-Jtab3':'tsj/', 'edui-emotion-Jtab4':'baodong_d/', 'edui-emotion-Jtab5':'baodong/'}, //图片对应文件夹路径
                imageCss:{'edui-emotion-Jtab0':'pczxh', 'edui-emotion-Jtab1':'wdj', 'edui-emotion-Jtab2':'lxs', 'edui-emotion-Jtab3':'tsj', 'edui-emotion-Jtab4':'baodong_d', 'edui-emotion-Jtab5':'bDong'}, //图片css类名
                imageCssOffset:{'edui-emotion-Jtab0':42, 'edui-emotion-Jtab1':30, 'edui-emotion-Jtab2':42, 'edui-emotion-Jtab3':35, 'edui-emotion-Jtab4':35, 'edui-emotion-Jtab5':35}, //图片偏移
                SmileyInfor:{
                    'edui-emotion-Jtab0':['+1', '哀伤', '饱', '濒死', '病', '吃货', '不满', '呆', '倒吊', '点子', '顶', '鼓掌', '害羞', '汗', '呵呵呵', '加油', '奸笑', '奸诈', '惊讶', '囧', '看', '抠鼻子', '哭', '困', '乐', '溜走', '路过', '买你妹的房纸', '敲锣', 'KISS', '去你妹的工作', '扇风', '失落', '刷牙', '逃命', '跳舞', '听音乐', '偷笑', '吐', '无语', '喜', '笑', '兴奋', '鸭子跳', '咬', '？', '礼物', '赞', '震惊', '追钱'],
                    'edui-emotion-Jtab1':['乖乖睡','集合啦','我靠，美女！','好伤心','玩呢，没空！','好热','yes','好困','你瞅啥','','','','','','','','',''],
                    'edui-emotion-Jtab2':['不想动', '吃惊', '吹口哨', '打气', '打坐', '等待', '愤怒', '幻想', '惊喜', '开森', '哭啼', '哭着跑', '没钱', '怕鬼', '抛媚眼', '七夕', '骑马舞', '升天', '石化', '数钱', '偷吃', '偷笑', '万圣节', '无聊', '无奈', '无语', '妩媚', '捉急', '转晕', '撞豆腐', '赘肉'],
                    'edui-emotion-Jtab3':['Kiss', 'Love', 'Yeah', '啊！', '背扭', '顶', '抖胸', '88', '汗', '瞌睡', '鲁拉', '拍砖', '揉脸', '生日快乐', '摊手', '睡觉', '瘫坐', '无聊', '星星闪', '旋转', '也不行', '郁闷', '正Music', '抓墙', '撞墙至死', '歪头', '戳眼', '飘过', '互相拍砖', '砍死你', '扔桌子', '少林寺', '什么？', '转头', '我爱牛奶', '我踢', '摇晃', '晕厥', '在笼子里', '震荡'],
                    'edui-emotion-Jtab4':['坏淫', '抓狂', '悲戚', '痛苦', '犯贱', '奸笑', '屌丝', '高呼', '感叹', '感动', '憋屈', '舒爽', '坏笑', '屁颠', '瞳术', '耍杂', '姚脸', '孤独', '暴走', '潇洒','失望','同桌','变脸','嘚瑟','腹黑男','腹黑女','够拽','白领','喵尼玛','汪尼玛','嘿嘿尼玛','勾引','发泄','操操操'],
                    'edui-emotion-Jtab5':['心照不宣', '蓝脸', '困惑', '够拽', '瞪眼', '藐视', '思考', '呵呵', '想要妹子', '幻想', '无奈的笑', '为什么', '黑人歪嘴', '歪嘴笑', '苦逼', '面瘫', '咯咯咯', '生气', '高兴', '喜极而泣', '告诉我', '有了', '好吧', '孤独一生', '嘎嘎', '暴躁', '紫歪嘴', '红歪嘴', '这不科学', '笑而不语', '围观', '牛仔歪嘴', '方肘子', '震惊', '惊讶', '颠覆三观', '尼美藐视', '尼美呵呵', '尼美发呆', '尼美有了', '猫嘴笑', '思春', '尼美苦逼', '笑尿', '尼美咯咯', '尼美歪嘴', '尼美姚', '暴走尼美', '娇羞', '尼美震惊', '孤独尼美','尼美大哭','尼美愤怒','尼美嘎嘎','尼美愤懑','尼美不解']
                }
            }
        },
        initContent:function( _editor, $widget ){

            var me = this,
                emotion = me.sourceData.emotion,
                lang = _editor.getLang( 'emotion' )['static'],
                emotionUrl = UMEDITOR_CONFIG.UMEDITOR_HOME_URL + 'dialogs/emotion/',
                options = $.extend( {}, lang, {
                    emotion_url: emotionUrl
                }),
                $root = me.root();

            if( me.inited ) {
                me.preventDefault();
                this.switchToFirst();
                return;
            }

            me.inited = true;

            editor = _editor;
            this.widget = $widget;

            emotion.SmileyPath = _editor.options.emotionLocalization === true ? emotionUrl + 'images/' : "http://tb2.bdstatic.com/tb/editor/images/";
            emotion.SmileyBox = me.createTabList( emotion.tabNum );
            emotion.tabExist = me.createArr( emotion.tabNum );

            options['cover_img'] = emotion.SmileyPath + (editor.options.emotionLocalization ? '0.gif' : 'default/0.gif');

            $root.html( $.parseTmpl( me.tpl, options ) );

            me.tabs = $.eduitab({selector:".edui-emotion-tab-Jpanel"});

            //缓存预览对象
            me.previewBox = $root.find(".edui-emotion-JtabIconReview");
            me.previewImg = $root.find(".edui-emotion-JfaceReview");

            me.initImgName();

        },
        initEvent:function(){

            var me = this;

            //防止点击过后关闭popup
            me.root().on('click', function(e){
                return false;
            });

            //移动预览
            me.root().delegate( 'td', 'mouseover mouseout', function( evt ){

                var $td = $( this),
                    url = $td.attr('data-surl') || null;

                if( url ) {
                    me[evt.type]( this, url , $td.attr('data-posflag') );
                }

                return false;

            } );

            //点击选中
            me.root().delegate( 'td', 'click', function( evt ){

                var $td = $( this),
                    realUrl = $td.attr('data-realurl') || null;

                if( realUrl ) {
                    me.insertSmiley( realUrl.replace( /'/g, "\\'" ), evt );
                }

                return false;

            } );

            //更新模板
            me.tabs.edui().on("beforeshow", function( evt ){

                var contentId = $(evt.target).attr('data-context').replace( /^.*\.(?=[^\s]*$)/, '' );

                evt.stopPropagation();

                me.updateTab( contentId );

            });

            this.switchToFirst();

        },
        initImgName: function() {

            var emotion = this.sourceData.emotion;

            for ( var pro in emotion.SmilmgName ) {
                var tempName = emotion.SmilmgName[pro],
                    tempBox = emotion.SmileyBox[pro],
                    tempStr = "";

                if ( tempBox.length ) return;

                for ( var i = 1; i <= tempName[1]; i++ ) {
                    tempStr = tempName[0];
                    if ( i < 10 ) tempStr = tempStr + '0';
                    if(tempName[0] == 'wdj_'){
                        tempStr = tempStr + i + '.png';
                    }else{
                        tempStr = tempStr + i + '.gif';
                    }
                    tempBox.push( tempStr );
                }
            }

        },
        /**
         * 切换到第一个tab
         */
        switchToFirst: function(){
            this.root().find(".edui-emotion-Jtabnav .edui-tab-text:first").trigger('click');
        },
        updateTab: function( contentBoxId ) {

            var me = this,
                emotion = me.sourceData.emotion;

            me.autoHeight( contentBoxId );

            if ( !emotion.tabExist[ contentBoxId ] ) {

                emotion.tabExist[ contentBoxId ] = true;
                me.createTab( contentBoxId );

            }

        },
        autoHeight: function( ) {
            this.widget.height(this.root() + 2);
        },
        createTabList: function( tabNum ) {
            var obj = {};
            for ( var i = 0; i < tabNum; i++ ) {
                obj["edui-emotion-Jtab" + i] = [];
            }
            return obj;
        },
        mouseover: function( td, srcPath, posFlag ) {

            posFlag -= 0;

            $(td).css( 'backgroundColor', '#ACCD3C' );

            this.previewImg.css( "backgroundImage", "url(" + srcPath + ")" );
            posFlag && this.previewBox.addClass('edui-emotion-preview-left');
            this.previewBox.show();

        },
        mouseout: function( td ) {
            $(td).css( 'backgroundColor', 'transparent' );
            this.previewBox.removeClass('edui-emotion-preview-left').hide();
        },
        insertSmiley: function( url, evt ) {
            var obj = {
                src: url
            };
            obj._src = obj.src;
            editor.execCommand( 'insertimage', obj );
            if ( !evt.ctrlKey ) {
                //关闭预览
                this.previewBox.removeClass('edui-emotion-preview-left').hide();
                this.widget.edui().hide();
            }
        },
        createTab: function( contentBoxId ) {

            var faceVersion = "?v=1.1", //版本号
                me = this,
                $contentBox = this.root().find("."+contentBoxId),
                emotion = me.sourceData.emotion,
                imagePath = emotion.SmileyPath + emotion.imageFolders[ contentBoxId ], //获取显示表情和预览表情的路径
                positionLine = 11 / 2, //中间数
                iWidth = iHeight = 35, //图片长宽
                iColWidth = 3, //表格剩余空间的显示比例
                tableCss = emotion.imageCss[ contentBoxId ],
                cssOffset = emotion.imageCssOffset[ contentBoxId ],
                textHTML = ['<table border="1" class="edui-emotion-smileytable">'],
                i = 0, imgNum = emotion.SmileyBox[ contentBoxId ].length, imgColNum = 11, faceImage,
                sUrl, realUrl, posflag, offset, infor;

            for ( ; i < imgNum; ) {
                textHTML.push( '<tr>' );
                for ( var j = 0; j < imgColNum; j++, i++ ) {
                    faceImage = emotion.SmileyBox[ contentBoxId ][i];
                    if ( faceImage ) {
                        sUrl = imagePath + faceImage + faceVersion;
                        realUrl = imagePath + faceImage;
                        posflag = j < positionLine ? 0 : 1;
                        offset = cssOffset * i * (-1) - 1;
                        infor = emotion.SmileyInfor[ contentBoxId ][i];

                        textHTML.push( '<td  class="edui-emotion-' + tableCss + '" data-surl="'+ sUrl +'" data-realurl="'+ realUrl +'" data-posflag="'+ posflag +'" align="center">' );
                        textHTML.push( '<span>' );
                        textHTML.push( '<img  style="background-position:left ' + offset + 'px;" title="' + infor + '" src="' + emotion.SmileyPath + (editor.options.emotionLocalization ? '0.gif" width="' : 'default/0.gif" width="') + iWidth + '" height="' + iHeight + '"></img>' );
                        textHTML.push( '</span>' );
                    } else {
                        textHTML.push( '<td bgcolor="#FFFFFF">' );
                    }
                    textHTML.push( '</td>' );
                }
                textHTML.push( '</tr>' );
            }
            textHTML.push( '</table>' );
            textHTML = textHTML.join( "" );
            $contentBox.html( textHTML );
        },
        createArr: function( tabNum ) {
            var arr = [];
            for ( var i = 0; i < tabNum; i++ ) {
                arr[i] = 0;
            }
            return arr;
        },
        width:603,
        height:400
    });

})();

