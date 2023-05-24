<?php
namespace Home\Controller;

class BuildHtmlController extends CommonController
{
	public function build(){
		$this->buildHtml('index',HTML_PATH,'Pc:index','utf8');//注意：这里的utf8不能写成utf-8
		
	
		$this->buildHtml('topic',HTML_PATH,'Pc:topic','utf8');//注意：这里的utf8不能写成utf-8
	}
}