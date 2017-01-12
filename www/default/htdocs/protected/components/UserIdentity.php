<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	public $_id;
	
	/**
	 * 用户验证
	 * 
	 * 使用Ldap验证，如果通过，且数据库中无此用户信息，则将该用户的基本信息插入数据库。
	 * 
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
/*
		if(!function_exists("ldap_connect")) {
			$local_user = EDMSQuery::instance("user")->findOne(array("username"=>$this->username));
			if(!$local_user) {

				$realname = $this->username;
				$email = 'no_ladp@localhost.cc';
				$user_id = new MongoId();
				EDMSQuery::instance("user")->insert(array(
					'_id' => $user_id,
					'username' => $this->username, 
					'realname' => $realname, 
					'passwd' => md5($this->password), 
					'email' => $email
				));
				$this->_id = $user_id;
				$this->setState('realname', $realname);
			} 
			else 
			{
				$this->_id = $local_user['_id'];	
				$this->setState('realname', $local_user['realname']);			
			}
			
			return !($this->errorCode = self::ERROR_NONE);
		}

*/


		$options = Yii::app()->params['ldap'];
		$ou_string = "ou=" . implode(",ou=",$options['ou']);
		$dc_string = "dc=" . implode(",dc=",$options['dc']);
		 
		$connection = ldap_connect($options['host']);
		ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
		 
		if($connection)
		{
			$bind = @ldap_bind($connection, "cn={$this->username},{$ou_string},{$dc_string}", $this->password);
		 
			if(!$bind) 
			{
				$this->errorCode = self::ERROR_PASSWORD_INVALID;
			} 
			else 
			{
				$this->errorCode = self::ERROR_NONE;
				$localUser = User::model()->findByAttributes(array("username"=>$this->username));
				
				if(!$localUser) {
					$user = new User();
					$ls = ldap_search($connection, "{$ou_string},{$dc_string}", "(|(cn={$this->username}))");
					$info = ldap_get_entries($connection, $ls);
					$fname = isset($info[0]['sn'][0]) ? $info[0]['sn'][0] : '';
					$gname = isset($info[0]['givenname'][0]) ? $info[0]['givenname'][0] : '';
					
					$user->realname = $fname.$gname;
					$user->email = isset($info[0]['mail'][0]) ? $info[0]['mail'][0] : '';
					$user->passwd = '';
					$user->username = $this->username;
					
					if($user->insert()) {
						$this->_id = $user->id;
						$this->setState('realname', $user->realname);
					} else {
						$this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
					}
				} 
				else 
				{
					$this->_id = $localUser->id;
					$this->setState('realname', $localUser->realname);
				}
			}
		}
		return !$this->errorCode;

	}
	
	public function getId() 
	{
		return $this->_id;
	}
}