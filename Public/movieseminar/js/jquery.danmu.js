(function( $ ) {
	$.jQueryPlugin = function( name ) {
		$.fn[name] = function( options ) {
			var args = Array.prototype.slice.call( arguments , 1 );
			if( this.length ) {
				return this.each( function() {
					var instance = $.data( this , name ) || $.data( this , name , new cyntax.plugins[name]( this , options )._init() );
					if( typeof options === "string" ){
						options = options.replace( /^_/ , "" );
						if( instance[options] ) {
							instance[options].apply( instance , args );
						}
					}
				});
			}
		};
	};
})( jQuery );
var cyntax = {
	plugins : {}
};

(function( $ ){
	cyntax.plugins.timer = function( ele , options ){
		this.$this = $( ele );
		this.options = $.extend( {} , this.defaults , options );
		this.timer_info = {id:null, index:null, state:0};
	};
	cyntax.plugins.timer.prototype = {
		defaults : { 
			delay: 1000,      
			repeat: false,    
			autostart: true,	
			callback: null,   
			url: '',          
			post: ''          
		},
		_init : function(){
			if (this.options.autostart) {
				this.timer_info.state = 1;
				this.timer_info.id = setTimeout( $.proxy( this._timer_fn, this ) , this.options.delay);
			}
			return this;
		},
		_timer_fn : function() {
				if (typeof this.options.callback == "function")
					$.proxy( this.options.callback, this.$this ).call(this, ++this.timer_info.index);
				else if (typeof this.options.url == "string") {
					ajax_options = {
						url: this.options.url,
						context: this,
						type: (typeof this.options.post == "string" && typeof this.options.post != "" == "" ? "POST": "GET"),
						success: function(data, textStatus, jqXHR) {
							this.$this.html(data);
						}
					};
					if (typeof this.options.post == "string" && typeof this.options.post != "")
						ajax_options.data = this.options.post;
					$.ajax(ajax_options);
				}
				if ( this.options.repeat && this.timer_info.state == 1 &&
					(typeof this.options.repeat == "boolean" || parseInt(this.options.repeat) > this.timer_info.index) )
					this.timer_info.id = setTimeout( $.proxy( this._timer_fn, this ) , this.options.delay );
				else
					this.timer_id = null;
		},
		start : function() {
			if (this.timer_info.state == 0) {
				this.timer_info.index = 0;
				this.timer_info.state = 1;
				this.timer_id = setTimeout( $.proxy( this._timer_fn, this ) , this.options.delay);
			}
		},
		
		stop : function(){
			if ( this.timer_info.state == 1 && this.timer_info.id ) {
				clearTimeout(this.timer_info.id);
				this.timer_id = null;
			}
			this.timer_info.state = 0;
		},
		
		pause : function() {
			if ( this.timer_info.state == 1 && this.timer_info.id )
				clearTimeout(this.timer_info.id);
			this.timer_info.state = 0;
		},
		
		resume : function() {
			this.timer_info.state = 1;
			this.timer_id = setTimeout( $.proxy( this._timer_fn, this ) , this.options.delay);
		}
	};

	$.jQueryPlugin( "timer" );
	
})( jQuery );

(function() {
	var $ = jQuery,
		pauseId = 'jQuery.pause',
		uuid = 1,
		oldAnimate = $.fn.animate,
		anims = {};

	function now() { return new Date().getTime(); }

	$.fn.animate = function(prop, speed, easing, callback) {
		var optall = $.speed(speed, easing, callback);
		optall.complete = optall.old; // unwrap callback
		return this.each(function() {
			// check pauseId
			if (! this[pauseId])
				this[pauseId] = uuid++;
			// start animation
			var opt = $.extend({}, optall);
			oldAnimate.apply($(this), [prop, $.extend({}, opt)]);
			// store data
			anims[this[pauseId]] = {
				run: true,
				prop: prop,
				opt: opt,
				start: now(),
				done: 0
			};
		});
	};

	$.fn.pause = function() {
		return this.each(function() {
			// check pauseId
			if (! this[pauseId])
				this[pauseId] = uuid++;
			// fetch data
			var data = anims[this[pauseId]];
			if (data && data.run) {
				data.done += now() - data.start;
				if (data.done > data.opt.duration) {
					// remove stale entry
					delete anims[this[pauseId]];
				} else {
					// pause animation
					$(this).stop();
					$(this).stop();
					$(this).stop();
					data.run = false;
				}
			}
		});
	};

	$.fn.resume = function() {
		return this.each(function() {
			// check pauseId
			if (! this[pauseId])
				this[pauseId] = uuid++;
			// fetch data
			var data = anims[this[pauseId]];
			if (data && ! data.run) {
				// resume animation
				data.opt.duration -= data.done;
				data.done = 0;
				data.run = true;
				data.start = now();
				oldAnimate.apply($(this), [data.prop, $.extend({}, data.opt)]);
			}
		});
	};
})();

;(function( $ ){
 var Danmu= function (element, options) {
    this.$element	= $(element);  
    this.options	= options;
    $(element).data("nowtime",options.nowtime);
    $(element).data("danmu_array",options.danmuss);
    $(element).data("opacity",options.opacity);
    $(element).data("paused",1);
    $(element).data("topspace",0);
    $(element).data("bottomspace",0);
    this.$element .css({
		"position":"absolute",
		"left":this.options.left,
		"top":this.options.top,
		"width":this.options.width,
		"height":this.options.height,
		"z-index":this.options.zindex,
		"color":options.default_font_color,
		"font-family":options.font_family ,
		"font-size":options.font_size_big,
		"overflow":"hidden"
	});
    var heig=this.$element.width();
	var row_conut=parseInt(heig/options.font_size_big);
	var rows_used=new Array();

	$("<div class='timer71452'></div>").appendTo(this.$element );
	this.$timer=$(".timer71452");
	this.$timer.timer({
		delay: 100,
		repeat: options.sumtime,
		autostart: false,
		callback: function( index ) {
			heig=$(element).width();
			//row_conut=parseInt(heig/options.font_size_big);
			if($(element).data("danmu_array")[$(element).data("nowtime")]){
					var danmus=$(element).data("danmu_array")[$(element).data("nowtime")];
					for(var i=0;i<danmus.length;i++){
						var a_danmu="<div class='flying flying2' id='linshi'></div>";
						$(element).append(a_danmu);
						$("#linshi").text(danmus[i].text);
						$("#linshi").css({
							"color":danmus[i].color
							,"text-shadow":" 0px 0px 0px "
							,"-moz-opacity":$(element).data("opacity")
							,"opacity": $(element).data("opacity")
							,"white-space":"nowrap"
							,"font-weight":"bold"
						});
						if (danmus[i].color<"#777777")
							$("#linshi").css({
								"text-shadow":" 0px 0px 0px "
							});
						if (danmus[i].hasOwnProperty('isnew')){
							$("#linshi").css({"border":"1px solid "+danmus[i].color});
						}
						if( danmus[i].size == 0)  $("#linshi").css("font-size",options.font_size_small);
						if  ( danmus[i].position == 0){
							//var top_local=parseInt(30+(options.height-60)*Math.random());//随机高度
							var row = parseInt(row_conut*Math.random());
							while (rows_used.indexOf(row)>=0 ){
								var row = parseInt(row_conut*Math.random());
							}
							rows_used.push(row);
							//console.log(rows_used.length);
							if (rows_used.length==row_conut){
								rows_used =new Array();
								row_conut=parseInt(heig/options.font_size_big);
							}
							var top_local=(row)*options.font_size_big;

							$("#linshi").css({"position":"absolute"
								,"top": options.height
								,"left":top_local
							});
							var fly_tmp_name="fly"+parseInt(heig*Math.random()).toString();
							$("#linshi").attr("id",fly_tmp_name);
							$('#'+fly_tmp_name).animate({top:-$(this).height()*3,},options.speed
								,function(){$(this).remove();}
							);
						}
						else if ( danmus[i].position == 1){
							var top_tmp_name="top"+parseInt(10000*Math.random()).toString();
							$("#linshi").attr("id",top_tmp_name)
							$('#'+top_tmp_name).css({
								"width":options.width
								,"text-align":"center"
								,"position":"absolute"
								,"top":(5+$(element).data("topspace"))
							});
							$(element).data("topspace",$(element).data("topspace")+options.font_size_big);
							$('#'+top_tmp_name).fadeTo(options.top_botton_danmu_time,$(element).data("opacity"),function(){
									$(this).remove();
									$(element).data("topspace",$(element).data("topspace")-options.font_size_big);
								}
							);
						}
						else if ( danmus[i].position == 2){
							var bottom_tmp_name="top"+parseInt(10000*Math.random()).toString();
							$("#linshi").attr("id",bottom_tmp_name)
							$('#'+bottom_tmp_name).css({
								"width":options.width
								,"text-align":"center"
								,"position":"absolute"
								,"bottom":0+$(element).data("bottomspace")
							});
							$(element).data("bottomspace",$(element).data("bottomspace")+options.font_size_big);
							$('#'+bottom_tmp_name).fadeTo(options.top_botton_danmu_time,$(element).data("opacity"),function(){
									$(this).remove();
									$(element).data("bottomspace",$(element).data("bottomspace")-options.font_size_big)
								}
							);

						} //else if
					}   // for in danmus
				}  //if (danmus)
			$(element).data("nowtime",$(element).data("nowtime")+1);

		}
	});		  
};


Danmu.DEFAULTS = {
		left: 0,    
		top: 0 , 
		height: 520,
		width: 324,
		zindex :100,
		speed:15000,
		sumtime:65535	,
		nowtime:0,
		font_family:"old_school_toons",
		danmuss:{},
	    default_font_color:"#FFFFFF",
		font_size_small:16,
		font_size_big:24,
		opacity:"0.9",
		top_botton_danmu_time:6000
	}



Danmu.prototype.danmu_start = function(){	
	this.$timer.timer('start');
	this.$element.data("paused",0);
};

Danmu.prototype.danmu_stop = function(){
	this.$timer.timer('stop');
	$('.flying').remove();
	nowtime=0;
	this.$element.data("paused",1);
	this.$element.data("nowtime",0);
};


Danmu.prototype.danmu_pause = function(){
	this.$timer.timer('pause');
	$('.flying').pause();
	this.$element.data("paused",1);
};


Danmu.prototype.danmu_resume = function(){
	this.$timer.timer('resume');
	$('.flying').resume();
	this.$element.data("paused",1);
};

Danmu.prototype.danmu_hideall= function(){
	$('.flying').remove();

};

Danmu.prototype.add_danmu = function(arg){
	if(this.$element.data("danmu_array")[arg.time]){
		this.$element.data("danmu_array")[arg.time].push(arg);
	}
	else{
		this.$element.data("danmu_array")[arg.time]=new Array();
		this.$element.data("danmu_array")[arg.time].push(arg);
	}

};

	
function Plugin(option,arg) {
    return this.each(function () {
      var $this   = $(this);
      var options = $.extend({}, Danmu.DEFAULTS, typeof option == 'object' && option);
      var data    = $this.data('danmu');
      var action  = typeof option == 'string' ? option : NaN;
      if (!data) $this.data('danmu', (data = new Danmu(this, options)))
      if (action)	data[action](arg);  
    })
};


$.fn.danmu             = Plugin;
$.fn.danmu.Constructor = Danmu;


})(jQuery);