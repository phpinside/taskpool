<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property string $id
 * @property string $username
 * @property string $realname
 * @property string $passwd
 * @property string $email
 */
class User extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('username', 'required'),
			array('username', 'length', 'max'=>50),
			array('realname', 'length', 'max'=>10),
			array('passwd', 'length', 'max'=>32),
			array('email', 'length', 'max'=>100),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'username' => 'Username',
			'realname' => 'Realname',
			'passwd' => 'Passwd',
			'email' => 'Email',
		);
	}

    public static function getSimpleUsersList() 
    {
    	$cmd = Yii::app()->db->createCommand("SELECT id, username, realname FROM user ORDER BY username;");
    	$users = (array)$cmd->queryAll();
    	
    	foreach($users as $user) {
    		$usersReturn[$user['id']] =$user['realname'];
    	}
    	return $usersReturn;
    }
 
	/**
	 * 取得接受者用户信息
	 * @return array 接收邮件的用户信息
	 */
	public static function getReceiversSimpleUsersList()
	{
		$simple_users_list = User::getSimpleUsersList();
		$viewer_ids_array = User::getUsernamesToId(Yii::app()->params['viewer']);
		if(!empty($viewer_ids_array) && !empty($simple_users_list)) {
			foreach ($simple_users_list as $simple_key=>$simple_users) {
				if(in_array($simple_key, $viewer_ids_array)) {
					unset($simple_users_list[$simple_key]);
				}
			}
		}
		return $simple_users_list;
	}
	
	/**
	 * 用户名字转换为用户ID.
	 * @param array $usernames
	 * @return array 用户ID组成的数组
	 */
	public static function getUsernamesToId($usernames)
	{
		$username_id_array = array();
		if(!empty($usernames)) {
			$usernames_string = "'".implode('\',\'', $usernames)."'";
			$usernames_ids = User::model()->findAllBySql("SELECT id FROM user WHERE username IN ($usernames_string)");
			foreach ($usernames_ids as $usernames_id) {
				$username_id_array[] = $usernames_id->id;
			}
		}
		return $username_id_array;
	}


	public static function getAllUserId() {
    	return Yii::app()->db->createCommand('SELECT id FROM `user`')->queryColumn();
    }

	/**
	 * 取得接受者用户ID
	 * @return array 接收邮件的用户信息
	 */
	public static function getReceiversAllUserId()
	{
		$username_id_array = array();
		$simple_users_list = Yii::app()->db->createCommand('SELECT id FROM `user`')->queryColumn();
		$viewer_ids_array = User::getUsernamesToId(Yii::app()->params['viewer']);
		
		$username_id_array = array_diff($simple_users_list, $viewer_ids_array);
		
		return $username_id_array;
	}
	
	/**
	 *  取总分
		"SELECT sum(credit) score FROM `task`  
		WHERE status in ($status) and status_update_time between $starttime  and  $endtime and winner_id=$winner_id"
		*/
	public static function getScore($winner_id,$query=array()) {
		$sql='SELECT sum(credit) score FROM `task` WHERE winner_id= '.$winner_id;
		foreach($query as $k => $v){
			$sql.=' and '.$k.' '.$v.' ';
		}
		$userscore= Yii::app()->db->createCommand($sql)->queryScalar();
		$userscore=empty($userscore)? 0: $userscore;
		return $userscore;
	}

	/**
	 * 当前占用的工时。（某人正在工作状态的任务工时总和）
	 */
	public static function getCurrentManhour($winner_id) {
		$sql='SELECT sum(man_hour) FROM `task` WHERE winner_id= :winner_id AND status=:status';
		$manhour = Yii::app()->db->createCommand($sql)->queryScalar(array(':winner_id'=>$winner_id, ':status'=>Task::STATUS_WORKING));
		$manhour = empty($manhour)? 0: $manhour;
		return $manhour;
	}	
	
	
	/**
	 * 指定时间段某一用户任务占用的工时和。（忽略任务状态）
	 */
	public static function getUserManhour($winnerId, $startTime, $endTime) {
		$sql='SELECT sum(man_hour) FROM `task` WHERE winner_id= :winner_id AND dispatch_time BETWEEN :startTime AND :endTime';
		$manhour = Yii::app()->db->createCommand($sql)->queryScalar(array(':winner_id'=>$winnerId, ':startTime'=>$startTime, ':endTime'=>$endTime));
		$manhour = empty($manhour)? 0: $manhour;
		return $manhour;
	}	
	
	/**
  		取任务总数量��
 	*/
	public static function getTaskNum($winner_id,$status=5) {
		$tasknum= Yii::app()->db->createCommand("SELECT count(*)  FROM `task` WHERE winner_id=$winner_id and status =$status " )->queryScalar();
		$tasknum=empty($tasknum)? 0: $tasknum;
		return $tasknum;
	}
	
	/**
	 * 统计所有用户当前正在做的任务得分
	 */
	public static function countUserScore($status = null){
		$data = array();
		if(empty($status)){
			$status = Task::STATUS_WORKING;
		}
		$sql = 'SELECT winner_id, sum(credit) AS score FROM `task` WHERE status = :status GROUP BY winner_id';		
		$resutl = Yii::app()->db->createCommand($sql)->queryAll(true, array(':status'=>$status));
		foreach ($resutl as $value){
			$data[$value['winner_id']] = $value['score'];
		}
		return $data;
	}
	
	/**
	* 显示用户列表及对应的正在做的任务的总分数
	 */
	public static function getUserWithScore(){
		$user = User::getReceiversSimpleUsersList();
		$score = User::countUserScore();
		foreach ($user as $key => $value){
			if(key_exists($key, $score)){
				$user[$key] = $value . '(' .$score[$key] .')';
			}else{
				$user[$key] = $value . '(0)';
			}
		}
		return $user;
	}
	
	/**
	 * 获取某一用户积分详情
	 * @param int $userId
	 */
	public function getUserScoreDetail($userId) {
		$userId = intval($userId);
		if ($userId <= 0) {
			return array();
		}
		//上周积分
		$result = Task::model()->getUserCredit($userId, strtotime(Util::getLastMonday(1)), strtotime(Util::getThisMonDay(1)) - 1, Task::STATUS_AUDITED);
		$detail['preWeekScore'] = intval($result[0]['sumCredit']);
		//上月积分
		$result = Task::model()->getUserCredit($userId, strtotime(Util::getUpMonthFirstDay()), strtotime(Util::getUpMonthLastDay()), Task::STATUS_AUDITED);
		$detail['preMonthScore'] = intval($result[0]['sumCredit']);
		//本周预计得分
		$detail['weekScore']= Task::model()->getUserCreditTotal($userId, strtotime(Util::getThisMonDay(1)), strtotime(Util::getThisMonDay(8)) - 1);
		//总积分
		$detail['allScore'] = intval($this->findByPk($userId)->total_score);
		//任务数
		$detail['taskNum'] = $this->getTaskNum($userId);
		//当前占用工作时间
		$detail['currentManhour'] = $this->getCurrentManhour($userId);
		return $detail;
		
	}
}