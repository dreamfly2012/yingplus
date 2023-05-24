<?php


namespace Home\Model;

class ForumReportModel extends CommonModel{
	protected $_validate = array(     
		array('reason','require','理由必须填写！'), //默认情况下用正则进行验证     
		array('content','require','举报证据必须填写'), // 在新增的时候验证name字段是否唯一  
	);
} 