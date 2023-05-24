<?php    
   use Think\Model;
   include('smtpclass.php');  
   class sendMailCommon{
   	  private $smtp="smtp.163.com";
   	  private $adminEmail="m18514324763@163.com";
   	  private $adminPw="jgyuttneraoawkmz";
   	  private $mailList;
   	  private $emailTitle="影+消息发送";
   	  private $emailbody="消息信息";

	  private function __get($property_name){ 
		if(isset($this->$property_name)){ 
		     return($this->$property_name); 
		}else{ 
		     return(NULL); 
		} 
	  } 
      private function __set($property_name, $value){ 
        $this->$property_name = $value; 
      }
      
      public function sendMailCommonfun(){
      	 if(empty($this->mailList)){
            $this->mailListData();           
      	 }      	  
      	 $mail = new \smtpclass(); 
         $mail->senduserMail($this->smtp,25,$this->adminEmail,$this->adminPw,$this->adminEmail,$this->mailList,$this->emailTitle,$this->emailbody,"HTML");
      }

      public function mailListData(){
         $adminModel = M('AdminEmail');
      	 $mailData=$adminModel->select();
      	 foreach ($mailData as $key => $value) {
               $mailList =$mailList.$mailData[$key]['email'].',';
         }
         $this->mailList=$mailList;
         return $this->mailList;
      }
      
  }
?>