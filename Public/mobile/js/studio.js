var btnElem=document.getElementById("wrap");//获取ID
var posStart = 0;//初始化起点坐标
var posEnd = 0;//初始化终点坐标
function initEvent() {
    btnElem.addEventListener("touchstart", function(event) {
        event.preventDefault();//阻止浏览器默认行为
        posStart = 0;
        posStart = event.touches[0].pageY;//获取起点坐标
    });
    btnElem.addEventListener("touchend", function(event) {
        event.preventDefault();
        posEnd = 0;
        posEnd = event.changedTouches[0].pageY;//获取终点坐标
        if(posStart - posEnd > 20 ){
            window.location.href="";
        };
    });
};
initEvent();