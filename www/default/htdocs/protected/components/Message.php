<?php
class Message extends CComponent {
	private $_to;
	private $_subject;
	private $_message;
	
	/**
	 * Construct 
	 * @param array $toUsers 可以是用户ID的数组，也可以是接收地址的数组。
	 * @param string $subject 消息主题
	 * @param string $message 消息内容
	 */
	public function __construct($toUsers, $subject, $message) {
		if(isset($toUsers[0]) && is_numeric($toUsers[0])) {
			$userIds = implode(',', $toUsers);
			$cmd = Yii::app()->db->createCommand("SELECT email FROM user WHERE id IN($userIds)");
			$this->_to = $cmd->queryColumn();
		} else {
			$this->_to = $toUsers;
		}
		
		$this->_subject = $subject;
		$this->_message = $message;
	}
	
	/**
	 * 发送消息
	 */
	public function send() {
		$this->sendEmail();
	}
	
	private function sendEmail() {
		
		$receivers = implode(', ', $this->_to);
		
		$subject = "=?UTF-8?B?".base64_encode($this->_subject)."?=";
		$headers = 'MIME-Version: 1.0' . "\r\n"
			.'Content-type: text/html; charset=utf-8' . "\r\n"
			.'From: '.Yii::app()->params['adminEmail']. "\r\n" 
			.'Reply-To: mengfanbin@hudong.com' . "\r\n" 
			.'X-Mailer: taskpool PHP/' . phpversion();
//		die;
		mail($receivers, $subject, $this->_message, $headers);
	}
	
	
}