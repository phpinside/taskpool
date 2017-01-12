<?php

/**
 * This is the model class for table "task".
 *
 * The followings are the available columns in table 'task':
 * @property integer $id
 * @property integer $user_id
 * @property string $subject
 * @property string $description
 * @property string $gain
 * @property integer $man_hour
 * @property integer $credit
 * @property integer $difficulty
 * @property integer $create_time
 * @property integer $update_time
 * @property integer $status_update_time
 * @property integer $status_counter
 * @property integer $winner_id
 * @property integer $status
 * @property string $remark
 * @property integer $audit_time
 * @property string $audit_message
 */
class Task extends CActiveRecord
{
	public $recvs;

	// Difficulty levels
    const DFCT_A = 1;
    const DFCT_B = 2;
    const DFCT_C = 3;
    const DFCT_D = 4;

    const STATUS_ADDED = 1;
    const STATUS_OPEN = 2; //开放申请
    const STATUS_WORKING = 3; //分配完毕，开始工作，已经确认人选
    const STATUS_FINISHED = 4; //任务确认完成
	const STATUS_AUDITED = 5; //任务审查完毕
	const STATUS_CLOSE = 6; //任务已关闭
	//难度级别基准分值
	public $difficultyDefaultCredit = array(
		self::DFCT_A => 1,
		self::DFCT_B => 1.5,
		self::DFCT_C => 2.5,
		self::DFCT_D => 3.5,
	);

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Task the static model class
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
		return 'task';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('remark', 'required', 'on'=>'done'),
			array('subject, description,gain, man_hour, difficulty,dateline, recvs', 'required', 'on'=>'insert, edit'),
			array('user_id, man_hour, difficulty, create_time, update_time, winner_id, status', 'numerical', 'integerOnly'=>true),
			array('subject', 'application.components.validators.StringWidthValidator', 'max'=>200, 'min'=>4, 'on'=>'insert, edit'),
			array('audit_message', 'length', 'max'=>300),
			array('credit', 'numerical'),
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
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
			'winner' => array(self::BELONGS_TO, 'User', 'winner_id'),
			'receivers' => array(self::MANY_MANY, 'User', 'task_receiver(task_id,user_id)'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'user_id' => '添加人',
			'subject' => '任务标题',
			'description' => '任务描述',
			'gain' => '交付成果',
			'man_hour' => '所需工时',
			'credit' => '任务积分',
			'difficulty' => '难度级别',
			'dateline' => '交付日期',
			'create_time' => '创建时间',
			'update_time' => '更新时间',
			'winner_id' => 'Winner',
			'recvs' => '任务接收者',
			'status' => '任务状态',
			'remark' => '完成情况',
			'audit_message' => '审核意见',
		);
	}

	/**
	 * behaviors
	 * 自动填充创建及更新时间
	 */
	public function behaviors(){
		return array(
			'CTimestampBehavior' => array(
				'class' => 'zii.behaviors.CTimestampBehavior',
				'setUpdateOnCreate'=>true,
			),
			'task' => 'application.components.behaviors.TaskMessageBehavior'
		);
	}

	public function getUrl($absolutePath = false,$urlmsg='task/view') {
		return $absolutePath ? Yii::app()->createAbsoluteUrl($urlmsg, array(
			'id'	=> $this->id
		)) : Yii::app()->createUrl($urlmsg, array(
			'id'	=> $this->id
		));
	}

	public static function url($absolutePath = false, $params = array()) {
		return $absolutePath ? Yii::app()->createAbsoluteUrl('task/view', $params) : Yii::app()->createUrl('task/view', $params);
	}

    /**
     * 获取难度列表数组
     * @param mixed $key
     */
    public static function getDifficulties($key=null) {
    	$dfcts = array(
    		'' => '- 请选择 -',
    		self::DFCT_A => 'A - 最简单',
    		self::DFCT_B => 'B -一般难度',
    		self::DFCT_C => 'C -有点难度',
    		self::DFCT_D => 'D - 最困难',
    	);
    	if(!is_null($key)) {
    		return isset($dfcts[$key]) ? $dfcts[$key] : '';
    	} else {
    		return $dfcts;
    	}
    }

   /**
     * 获取任务状态列表数组
     * @param mixed $key
     */
    public static function getStatus($key=null) {
    	$status = array(
    		self::STATUS_ADDED => '已添加，尚未开抢',
    		self::STATUS_OPEN => '开抢中',
    		self::STATUS_WORKING => '已被抢，开始工作',
    		self::STATUS_FINISHED => '已完成',
    		self::STATUS_AUDITED => '已审查',
    		self::STATUS_CLOSE => '已关闭',
    	);
    	if(!is_null($key)) {
    		return isset($status[$key]) ? $status[$key] : '';
    	} else {
    		return $status;
    	}
    }

	public function beforeSave() {
		if(parent::beforeSave()) {
			if($this->isNewRecord) {
				$this->dateline=strtotime($this->dateline);
				$this->user_id = Yii::app()->user->id;
				$this->winner_id = 0;
				$this->status = self::STATUS_ADDED;
				//计算初始分值
				$this->credit = $this->getDefaultCredit($this->man_hour, $this->difficulty);
			
			}
	        return true;
	    } else {
	    	return false;
	    }
	}

	public function afterSave() {
		if($this->isNewRecord) {
			$this->saveTaskReceiver();
// 			$message = new Message(User::getAllUserId(), "[新任务待评分]{$this->subject}", "还等什么？快去评分吧!&nbsp;&nbsp;&nbsp;".CHtml::link('现在就去!', $this->getUrl(true)));
// 			$message->send();
			TaskLog::addLog($this->id, Yii::app()->user->realname.'添加了此任务.');
			//增加一条评分记录
			$taskGradeModel = new TaskGrade();
			$taskGradeModel->attributes = array(
				'task_id' => $this->id,
				'user_id' => Yii::app()->user->id,
				'credit' => $this->credit,
				'description' => '系统标准: a级任务(1+0)*工时 b级任务(1+0.5)*工时 c级任务(1+1.5)*工时 d级任务(1+2.5)*工时',
			);
			$taskGradeModel->saveCredit();
			
		} elseif ($this->scenario == 'edit') {
			//更新一条评分记录
			$taskGradeModel = new TaskGrade();
			$taskGradeModel->user_id = Yii::app()->user->id;
			$taskGradeModel->attributes = array(
				'task_id' => $this->id,
				'credit' => $this->credit,
			);
			$taskGradeModel->updateCredit();
			$this->removeAllTaskReceiver();
			$this->saveTaskReceiver();
		}
		parent::afterSave();
	}

	/**
	 * 开放任务申请
	 */
	public function open() {
		$this->setScenario('open');
		if(!$this->checkTaskCloseStatus()) {
			$this->addError('credit', '任务:【'.$this->subject.'】已关闭，任务暂时不能开放！');
			return false;
		}
		$this->status = self::STATUS_OPEN;
		$this->status_update_time = time();
		if(!$this->status_counter || $this->status_counter >= Yii::app()->params['maxDispatchCount']) {
			$this->status_counter = 0;
		}
		$this->status_counter += 1;
		
		if($this->save(false)) {
			$countMsg = $this->status_counter == 1 ? '' : '第'.$this->status_counter.'次';
			TaskLog::addLog($this->id, '任务'.$countMsg.'开放申请');

			$receivers = TaskReceiver::getTaskReceivers($this->id);
			
			if(count($receivers) == 1) { //仅有一个接收者，则直接分配，不走三轮分配流程。
				$directUser = array_shift($receivers);
				$taskApplication = new TaskApplication();
				$taskApplication->task_id = $this->id;
				$taskApplication->user_id = $directUser;
				$taskApplication->status = TaskApplication::STATUS_WAITING;
				$taskApplication->save(false);
				$this->directDispatch($directUser, false);
			} else {
				$this->afterOpen();
			}
		} else {
			print_r($this->errors);
		}

	}


	/**
	 * 任务完成
	 */
	public function done() {
		$this->setScenario('done');
		$this->status = self::STATUS_FINISHED;
		$this->done_time=time();
		$this->status_update_time=time();
		$this->remark = strip_tags($this->remark);
		if($this->save()) {
			//检查是否申请了此任务，先忽略这个步骤
			$username = Yii::app()->user->realname;
			TaskLog::addLog($this->id, $username.'确认完成了此任务;');
			
			$this->afterDone();
		} else {
			return false;
		}
		return true;
	}

	/**
	 * 任务审查通过
	 */
	public function audit() {
		$this->setScenario('audit');
		$this->status = self::STATUS_AUDITED;
		$this->audit_time = $this->status_update_time = time();
		if($this->save()) {
			$user = User::model()->findByPk($this->winner_id);
			$user->total_score = intval($user->total_score) + intval($this->credit);
			$user->save();
//			if($user->save()) { //更新用户历史总得分
//				TaskLog::addLog($user->id, $user->realname."的任务:{$this->id}:{$this->subject},增加积分：{$this->credit},成功！");
//			}
			$username = Yii::app()->user->realname;
			TaskLog::addLog($this->id, $username.'审查通过了此任务;');
			$this->afterAudit();
			return true;
		}
		return true;
	}

	/**
	 * 关闭任务
	 */
	public function close(){
		$this->setScenario('close');
		$this->status = self::STATUS_CLOSE;
		$this->status_update_time=time();
		if($this->save()) {
			$username = Yii::app()->user->realname;
			TaskLog::addLog($this->id, $username.'关闭了此任务;');
			$this->afterClose();
			return true;
		}
		return true;
	}

	/**
	 * 获取所有开放申请状态的任务
	 */
	public static function getOpeningTasks() {
		return Yii::app()->db->createCommand(
			'SELECT * FROM task WHERE status = :status'
		)->queryAll(true, array(':status'=>self::STATUS_OPEN));
	}

	/**
	 * 获取没有任何人选择的任务
	 */
	public static function getNoApplications() {
		$criteria = new CDbCriteria();
		$criteria->join='LEFT JOIN task_application t_a ON t_a.task_id = id';
//		$criteria->compare('t_a.user_id', $user_id);
		$criteria->compare('t.status', self::STATUS_OPEN);
		$criteria->addCondition('task_id IS NULL');
//		$criteria->order = 'id desc';
		return $criteria;
	}

	/**
	 * 直接分配给某人
	 * @param int $winner
	 */
	public function directDispatch($winner, $console=true) {
		$winnerInfo = Yii::app()->db->createCommand(
			'SELECT * FROM user WHERE id = :id'
		)->queryRow(true, array(':id'=>$winner));
//		print_r($winnerInfo);

		if($console) {
			$this->dispatchLog("最终分配给[{$winner}]".$winnerInfo['realname']);
		}

		//更新任务的分配时间
		$this->dispatch_time = time();
		$this->winner_id = $winnerInfo['id'];
		$this->status = self::STATUS_WORKING;
		if($this->save(false)) {
			TaskLog::addLog($this->id, '本任务被最终分配给 '.$winnerInfo['realname']);

			$this->afterDispatch();
			if($console) {
				$this->dispatchLog("已发邮件");
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 任务分发
	 */
	public function dispatch() {
		$applicant = Yii::app()->db->createCommand(
			'SELECT user_id FROM task_application WHERE task_id = :task_id'
		)->queryColumn(array(':task_id'=>$this->id));

		$this->dispatchLog("开始分发（第{$this->status_counter}次）");

		if(empty($applicant)) { // 没有任何人申请

			$this->dispatchLog("没有任何人申请此任务");

			if($this->status_counter >= Yii::app()->params['maxDispatchCount']) { //已三次无人选

				$this->status = self::STATUS_ADDED;
				$this->save(false);
				$this->nobodyChoosed();
				$this->dispatchLog("已三次无人选，已向任务添加者发提醒邮件");

				/*
				$this->dispatchLog(Yii::app()->params['maxDispatchCount']."次无人选，直接随机分配给任务接收者");
				$applicant = Yii::app()->db->createCommand(
					'SELECT user_id FROM task_receiver WHERE task_id = :task_id'
				)->queryColumn(array(':task_id'=>$this->id));

				$this->finalAssign($applicant);
				*/

			} else { //否则任务分数+ 10%，重新开放申请

				$this->credit += ceil($this->credit * 0.1);
				$this->open();

				$this->dispatchLog("任务分数 +10% = {$this->credit}");
			}
		} else {
			$this->finalAssign($applicant);
		}
	}

	/**
	 * 任务最终分配
	 */
	private function finalAssign($applicant = array()) {

		$this->dispatchLog("开始分配，user_id in（".implode(', ', $applicant)."）");

		//初始化每人当前已申请积分为0：
		$applicantCredits = array();
		foreach($applicant as $user_id) {
			$applicantCredits[intval($user_id)] = 0;
		}

		//查询已申请到的积分：
		$applicantIds = implode(', ', $applicant);
		$creditData = Yii::app()->db->createCommand(
			'SELECT winner_id, SUM(credit) AS sum_credit FROM task WHERE winner_id IN ('.$applicantIds.') AND status = :status GROUP BY winner_id '
		)->queryAll(true, array(':status'=>self::STATUS_WORKING));

		foreach($creditData as $row) {
			$applicantCredits[intval($row['winner_id'])] = intval($row['sum_credit']);
		}

		//找出当前最小积分的人（们）
		$minCreditApplicants = array();
		foreach($applicantCredits as $user_id=>$credit) {
			$currentCreditData = array('user_id'=>$user_id, 'credit'=>$credit);
			if(empty($minCreditApplicants)) {
				$minCreditApplicants[0] = $currentCreditData;
			} else {
				if($credit < $minCreditApplicants[0]['credit']) {
					$minCreditApplicants = array(); //有更小的就清空
					$minCreditApplicants[0] = $currentCreditData; //然后设为最小的
				} elseif ($credit == $minCreditApplicants[0]['credit']) {
					$minCreditApplicants[] = $currentCreditData; //有并列最小的就加入数组
				}
			}
			$this->dispatchLog("申请者：{$user_id}, 当前申请到任务的积分：{$credit}");
		}

		if(empty($minCreditApplicants)) { //不太可能发生。。
			return true;
		}

		$this->dispatchLog("申请者中积分最少者：".print_r($minCreditApplicants, true));

		$winnerIndex = array_rand($minCreditApplicants); //并列最小则随机
		$winner = $minCreditApplicants[$winnerIndex]['user_id'];

//		$this->dispatchLog("最终胜出者：{$winner}");

		return $this->directDispatch($winner, true);
	}

	private function dispatchLog($msg) {
		echo "[{$this->id}] {$this->subject} ............ {$msg}\n";
	}

	/**
	 * 检查 $user_id 是否可以申请此任务
	 */
	public function checkApplicable($user_id, $addError = true) {
		if(!$this->checkTaskCloseStatus()) {
			if($addError) {
				$this->addError('user_id', '任务已关闭，不能申请！');
			}
			return false;
		}
		if($this->status != self::STATUS_OPEN) {
			if($addError) {
				$this->addError('user_id', '此任务尚未开放，不能申请');
			}
			return false;
		}
		$taskReceivers = Yii::app()->db->createCommand(
			'SELECT user_id FROM task_receiver WHERE task_id = :task_id'
		)->queryColumn(array(':task_id'=>$this->id));

		if(!in_array($user_id, $taskReceivers)) {
			if($addError) {
				$this->addError('user_id', '此任务仅限其他火星人申请');
			}
			return false;
		}

		$applicationTime = Yii::app()->db->createCommand(
			'SELECT create_time FROM task_application WHERE task_id = :task_id AND user_id = :user_id'
		)->queryScalar(array(':task_id'=>$this->id, ':user_id'=>$user_id));

		if($applicationTime) {
			if($addError) {
				$this->addError('user_id', '您在 [ '.date('Y-m-d H:i:s', $applicationTime).' ] 申请了此任务');
			}
			return false;
		}

		return true;
	}

	/*
	 * 检查任务状态
	 */
	public function checkTaskCloseStatus(){
		if($this->status == Task::STATUS_CLOSE){
			return false;
		}
		return true;
	}

	/**
	 * 任务申请
	 */
	public function apply($user_id = null) {
		if(is_null($user_id)) {
			$user_id = Yii::app()->user->id ;
		}

		if(!$this->checkApplicable($user_id)) {
			return false;
		}
		$taskApplication = new TaskApplication();
		$taskApplication->task_id = $this->id;
		$taskApplication->user_id = $user_id;
		$taskApplication->status = TaskApplication::STATUS_WAITING;
		if($taskApplication->save()) {
			if($taskApplication->user_id == Yii::app()->user->id) {
				$username = Yii::app()->user->realname;
			} else {
				$username = Yii::app()->db->createCommand(
					'SELECT realname FROM user WHERE id = :id'
				)->queryScalar(array(':id'=>$user_id));
			}
			TaskLog::addLog($this->id, $username.'申请了此任务;');
			return true;
		} else {
			return false;
		}
	}



		/**
	 * 删除任务的可申请人
	 */
	private function removeAllTaskReceiver() {
		$taskReceiver = new TaskReceiver();
		$taskReceiver->deleteAll(" task_id={$this->id} ");
	}

	/**
	 * 保存任务的可申请人
	 */
	private function saveTaskReceiver() {
		$taskReceiver = new TaskReceiver();
		$taskReceiver->task_id = $this->id;
		$taskReceiver->saveFromArray($this->recvs);
		unset($taskReceiver);
	}

	/**
	 * 获取符合指定条件的任务列表
	 */
	public static function getTasks($criteria, $pagination) {

		return new CActiveDataProvider(__CLASS__, array(
			'pagination' =>$pagination,
			'criteria' => $criteria,
		));
	}

	/**
	 * 获取某人可申请的任务
	 */
	public static function getCandidateTasks($user_id) {

		$tasks = Yii::app()->db->createCommand(
			 'SELECT  `t` . *
				FROM  `task`  `t`
				WHERE t.status = :t_status
				AND t.id
				IN (
				
				SELECT task_id
				FROM task_receiver
				WHERE user_id = :tr_user_id
				)
				AND t.id NOT
				IN (
				
				SELECT task_id
				FROM task_application
				WHERE user_id = :tr_user_id
				)
				ORDER BY t.create_time DESC '
		)->queryAll(true, array(':t_status'=>self::STATUS_OPEN, ':tr_user_id'=>$user_id));


		return new CArrayDataProvider($tasks, array('pagination'=>false));

	}

	/**
	 * 获取某人已申请任务
	 */
	public static function getHistoryTasks($user_id, $status='') {
		$criteria = new CDbCriteria();
		$criteria->compare('t.winner_id', $user_id);
		if('' != $status) {
			$criteria->compare('t.status', $status);
		}
		$criteria->with='winner';

		return $criteria;
	}


	/**
	 * 获取某人正在做的任务
	 */
	public static function getWorkingTasks($user_id, $pagination) {
		$criteria = new CDbCriteria();
		$criteria->compare('winner_id', $user_id);
		$criteria->addInCondition('status', array(self::STATUS_WORKING,self::STATUS_FINISHED));
		$criteria->order = 'create_time asc';
		return new CActiveDataProvider(__CLASS__, array(
			'pagination' =>$pagination,
			'criteria' => $criteria,
		));
	}

	/**
	 * 获取某人做的任务
	 */
	public static function getUserTasks($user_id) {
		$criteria = new CDbCriteria();
		$criteria->compare('winner_id', $user_id);
		return $criteria;
	}

	/**
	 * 获取当前任务的上一篇或下一篇id
	 * @param $id current task id
	 * @param $type next or before
	 * @deprecated
	 */
	public function getNeighborId($id) {
		$neighbors = array();
		$sql = "SELECT id FROM `task` WHERE id>$id ORDER BY id ASC LIMIT 1";
		$result = Yii::app()->db->createCommand($sql)->queryScalar();
		$neighbors['nextId'] = !empty($result) ? $result : '';
		$sql = "SELECT id FROM `task` WHERE id<$id ORDER BY id DESC LIMIT 1";
		$result = Yii::app()->db->createCommand($sql)->queryScalar();
		$neighbors['beforeId'] = !empty($result) ? $result : '';
		return $neighbors;
	}
	
	
	/**
	 * 获取上一任务
	 * @return mixed 成功则返回任务ID，失败返回false
	 */
	public function getPreviousId() {
		$sql = "SELECT id FROM `task` WHERE id < :id ORDER BY id DESC LIMIT 1";
		return Yii::app()->db->createCommand($sql)->queryScalar(array(':id'=>$this->id));
	}
	
	/**
	 * 获取下一任务
	 * @return mixed 成功则返回任务ID，失败返回false
	 */
	public function getNextId() {
		$sql = "SELECT id FROM `task` WHERE id > :id ORDER BY id ASC LIMIT 1";
		return Yii::app()->db->createCommand($sql)->queryScalar(array(':id'=>$this->id));
	}
	
	/**
	 * 获取用户某时间段的任务完成情况，默认为所有用户
	 * 
	 * @param int $begTime
	 * @param int $endTime
	 * @param int $winnerId
	 * @return array
	 */
	public function getUserTaskCompleteInfo($begTime, $endTime, $winnerId=null) {
		$where = array();
		if($begTime > 0) {
			$where[] = "T.dateline >= $begTime";
		}
		if($endTime > 0) {
			$where[] = "T.dateline < $endTime";
		}
		
		if($winnerId) {
			$winnerId = intval($winnerId);
			$where[] = "T.winner_id = $winnerId";
		}
		
		$sql = 'SELECT *, (SELECT realname FROM user U WHERE U.id=T.winner_id) as realname FROM '.$this->tableName(). ' as T ';
		
		if( !empty($where) ){
			$sql .= ' WHERE '.implode(' and ', $where);
		}
		
		$taskList = Yii::app()->db->createCommand($sql)->queryAll();
		
		$taskComList = array();
		foreach($taskList as $task) {
			$taskComList[$task['winner_id']][] = $task;
		}
		return $taskComList;
	}

	/**
	 * 获取用户某时间段已完成的任务列表，默认为所有用户
	 * 
	 * @param int $begTime
	 * @param int $endTime
	 * @param int $winnerId
	 * @return array
	 */
	public function getCompletedList($begTime, $endTime, $winnerId=null) {
		$where = array();
		$begTime = intval($begTime);
		$endTime = intval($endTime);
		$winnerId = intval($winnerId);
		
		$sql = 'SELECT * FROM '.$this->tableName()." WHERE done_time >= $begTime and done_time < $endTime and status = ".self::STATUS_AUDITED;
		
		if( $winnerId ){
			$sql .= " and winner_id = $winnerId";
		}
		
		$taskList = Yii::app()->db->createCommand($sql)->queryAll();
		return $taskList;
	}
	
	/**
	 * 根据已审查的任务列表，生成临时的excel文件。
	 * 潘雪鹏 2012-10-17 18:32
	 * 
	 * 实现思路：
	 * 1) 准备一个excel模板文件，将格式设置好，使用PHPExce设置格式较累。
	 * 2) 模板当中默认有8个任务数据的位置，当任务多于8条时，添加新行。
	 * 3) 将任务信息自动填充到excel对应的列中。
	 * 
	 * @param int $uid
	 * @param array $taskList 已审查的任务列表数据
	 * 
	 * @return string 临时文件绝对路径
	 */
	public function createExcelFile($info, $taskList){
		// 读取excel模板
		$kaohebiao = Yii::app()->basePath.'/data/kpi-tpl.xls';
		
		// 目前（2012-10-18），创建PHPExcel实例之前，
		// 必须执行new PHPExcel，否则不能自动加载PHPExcel_IOFactory等类文件
		$objPHPExcel = new PHPExcel();
		$objPHPExcel = PHPExcel_IOFactory::load($kaohebiao);
		
		$objPHPExcel->setActiveSheetIndex(0);
		$objActSheet = $objPHPExcel->getActiveSheet();
		
		
		$total = count($taskList); // 任务总数
		$taskStartLineNo = 6; // 任务列表开始行号
		$defaultTaskLineNum = 8; // 默认存在的任务行数
		$totalLineNo = $taskStartLineNo; // 总计得分行号
		
		// 是否需要新增行
		if( $total > $defaultTaskLineNum ){
			$objActSheet->insertNewRowBefore($taskStartLineNo+1, $total-$defaultTaskLineNum);
			$totalLineNo += $total;
		}else{
			$totalLineNo += $defaultTaskLineNum;
		}
		
		// 设置个人信息
		$objActSheet->setCellValue('A1', "员工月度绩效考核表 （{$info['year']}年{$info['month']}月）"); 
		$objActSheet->setCellValue('B2', "姓名: {$info['realname']}");
		$objActSheet->setCellValue('C2', "岗位: {$info['profession']}");
		$objActSheet->setCellValue('D2', "部门: {$info['department']}");
		$objActSheet->setCellValue('G2', "直属主管: {$info['superior']}");
		
		// 循环填充各列数据
		// A任务序号 B任务标题 C任务描述 D难度级别 E交付成果 F交付日期 G完成情况说明 H审核意见 I积分
		$i = 6;
		foreach($taskList as $taskId => $task){
			$objActSheet->setCellValue("A$i", $task['id']);
			$objActSheet->setCellValue("B$i", $task['subject']);
			$objActSheet->setCellValue("C$i", $task['description']);
			$objActSheet->setCellValue("D$i", $task['difficulty']);
			$objActSheet->setCellValue("E$i", $task['gain']);
			$objActSheet->setCellValue("F$i", date('Y-m-d', $task['dateline']));
			$objActSheet->setCellValue("G$i", $task['remark']);
			$objActSheet->setCellValue("H$i", $task['audit_message']);
			$objActSheet->setCellValue("I$i", $task['credit']);
			
			$i += 1;
		}
		$taskEndLineNo = $totalLineNo-1;
		// 总计得分公式
		$objActSheet->setCellValue("I$totalLineNo", "=SUM(I$taskStartLineNo:I$taskEndLineNo)");
		
		// 生成临时文件，文件在下载后会被删除
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$fileName = Yii::app()->runtimePath . "/{$info['uid']}.xls";
		$objWriter->save($fileName);
		
		return $fileName;
	}
	
	public function exportTaskExcel($startDate, $endDate) {
		$objPHPExcel = new PHPExcel();
		$objActSheet = $objPHPExcel->getActiveSheet();
		
		$this->setSheetFormat($objActSheet, $startDate, $endDate, '0');
			
		$taskList = Task::model()->getUserTaskCompleteInfo($startDate, $endDate);
		$this->setSheetData($objActSheet, $taskList, 1);
		
		// 下周计划表格
		$thisBegTime = strtotime("+1 week", $startDate);
		$thisEndTime = strtotime("+1 week", $endDate);
			
		$objActSheet1 = $objPHPExcel->createSheet(1);
		$this->setSheetFormat($objActSheet1, $thisBegTime, $thisEndTime, '0→');
		$taskList = $this->getUserTaskCompleteInfo($thisBegTime, $thisEndTime);
		$this->setSheetData($objActSheet1, $taskList, 2);	
		return $objPHPExcel;
	}
	
	private function setSheetFormat($objActSheet, $startDate, $endDate, $sheetTitle) {
		$objActSheet->mergeCells('A1:C1');
		$objActSheet->setCellValue('A1', '互动在线（北京）科技有限公司执行力指数表v5.0')->getStyle('A1')->getFont()->setBold(true);
		$objActSheet->setCellValue('D1', '日期：'.date('Y-m-d', $startDate).'～'.date('Y-m-d', $endDate-2*3600*24))->getStyle('D1')->getFont()->setBold(true);
			
		// 周执行力表格
		$titleConfig = array(
				'A2'=>array('title'=>'成员','width'=>'9'),
				'B2'=>array('title'=>'完成率','width'=>'9'),
				'C2'=>array('title'=>'状态','width'=>'9'),
				'D2'=>array('title'=>'任务目标','width'=>'50'),
				'E2'=>array('title'=>'交付成果','width'=>'50'),
				'F2'=>array('title'=>'完成情况','width'=>'50')
		);
		$this->setTDFormat($objActSheet, array('A2', 'B2', 'C2', 'D2', 'E2', 'F2'));
		$objActSheet->setTitle($sheetTitle);
			
		foreach($titleConfig as $key => $title) {
			//设置第2行的格式
			$objActSheet->setCellValue($key, $title['title'])
			->getStyle($key)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()->setARGB('FF999999');
		
			//设置列的宽度
			$objActSheet->getColumnDimension(substr($key, 0, 1))
			->setWidth($title['width']);
		
			//设置列对齐方式
			$objActSheet->getStyle(substr($key, 0, 1))->getAlignment()
			->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);//自动换行
		}
	}
	
	private function setSheetData ($objActSheet, $taskList, $type) {
		$i = 3;
		foreach($taskList as $key=>$tasks) { //每次循环出一个人的所有任务
			$count = 0;				//任务个数
			$compeleCount = 0;		//完成个数
			$countList = array(); 	//用户的任务列表
			$gainList = array();	//用户的交付列表
			$remarkList = array();	//用户的备注（完成情况）
			$realname = '';
		
			foreach($tasks as $task) {
				$count ++;
				if($task['status'] == Task::STATUS_AUDITED) { //如果任务已完成
					$compeleCount ++;
				}
				$realname = $task['realname'];//任务对应的人
				$countList[]  = $task['subject']!= '' ? $count.'.'. $task['subject']:'';
				$gainList []   = $task['gain'] 	!= '' ? $count.'.'. $task['gain']:'';
				$remarkList[] = $task['remark'] != '' ? $count.'.'. $task['remark']:'';
			}
		
			if(1 == $type) {
				$complete = intval($compeleCount/$count*100).'%';
				$complete_str = $compeleCount/$count == 1 ? '完成':'未完成';
				$remark_str = implode("\r\n", $remarkList);
			} else {
				$complete = '';
				$complete_str = '';
				$remark_str = '';
			}
			
			$objActSheet->setCellValue('A'.$i, $realname);
			$objActSheet->setCellValue('B'.$i, $complete);
			$objActSheet->setCellValue('C'.$i, $complete_str);
			$objActSheet->setCellValue('D'.$i, implode("\r\n",$countList));
			$objActSheet->setCellValue('E'.$i, implode("\r\n",$gainList));
			$objActSheet->setCellValue('F'.$i, $remark_str);
			$this->setTDFormat($objActSheet, array('A'.$i, 'B'.$i, 'C'.$i, 'D'.$i, 'E'.$i, 'F'.$i));
			
			$i++;
		} 
	}
	
	private function setTDFormat($sheet, $td_list) {
		if(is_string($td_list)) {
			$td_list = array($td_list);
		}
		foreach($td_list as $td) {
			$borders = $sheet->getStyle($td)->getBorders();
			$borders->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$borders->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$borders->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$borders->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		}
	}
	
	
	
	public function afterOpen() {
		if($this->hasEventHandler('onAfterOpen')) {
			$event = new CModelEvent($this);
			$this->onAfterOpen($event);
			return $event->isValid;
		}
		
		return true;
	}
	public function onAfterOpen($event) {
		$this->raiseEvent('onAfterOpen', $event);
	}
	
	public function afterDone() {
		if($this->hasEventHandler('onAfterDone')) {
			$event = new CModelEvent($this);
			$this->onAfterDone($event);
			return $event->isValid;
		}
		return true;
	}
	public function onAfterDone($event) {
		$this->raiseEvent('onAfterDone', $event);
	}
	
	public function afterDispatch() {
		if($this->hasEventHandler('onAfterDispatch')) {
			$event = new CModelEvent($this);
			$this->onAfterDispatch($event);
			return $event->isValid;
		}
		return true;
	}
	public function onAfterDispatch($event) {
		$this->raiseEvent('onAfterDispatch', $event);
	}
	
	public function nobodyChoosed() {
		if($this->hasEventHandler('onNobodyChoosed')) {
			$event = new CModelEvent($this);
			$this->onNobodyChoosed($event);
			return $event->isValid;
		}
		return true;
	}
	
	public function onNobodyChoosed($event) {
		$this->raiseEvent('onNobodyChoosed', $event);
	}
	
	public function afterAudit() {
		if($this->hasEventHandler('onAfterAudit')) {
			$event = new CModelEvent($this);
			$this->onAfterAudit($event);
		}
	}
	
	public function onAfterAudit($event) {
		$this->raiseEvent('onAfterAudit', $event);
	}

	public function afterClose(){
		if($this->hasEventHandler('onAfterClose')) {
			$event = new CModelEvent($this);
			$this->onAfterClose($event);
		}
	}
	public function onAfterClose($event){
		$this->raiseEvent('onAfterClose', $event);
	}
	
	/**
	 * 得到某个用户在一段时间内的某状态的总积分（$states 只能为4,5）
	 * @param int 			$winner_id 		用户id
	 * @param string 		$bTime 	    	开始时间
	 * @param string 		$eTime	    	结束时间
	 * @param string|array 	$states    		状态,如果存在多种状态，是或的关系 默认为5已确认完成
	 * @param boolean		$withViewer		是否取观察者结果
	 */
	public function getUserCredit($winner_id = 0, $begTime, $endTime, $states=5, $withViewer = true) {
		return $this->getCredit($winner_id, $begTime, $endTime, $states, $withViewer, 'done_time');
	}
	
	
	/**
	 * 得到某个用户在一段时间内的进行中的任务总分(状态为 Task::STATUS_WORKING)
	 * @param int 			$winner_id 		用户id
	 * @param string 		$bTime 	    	开始时间
	 * @param string 		$eTime	    	结束时间
	 * @param boolean		$withViewer		是否取观察者结果
	 */
	public function getUserCreditWillbe($winner_id = 0, $begTime, $endTime, $withViewer = true) {
		return $this->getCredit($winner_id, $begTime, $endTime, self::STATUS_WORKING, $withViewer, 'dateline');
	}
	
	/**
	 * 获取某一用户全部分数（包含确认完成，进行中，已审查） 注意返回值为 int 分数值 
	 * @param $winner_id
	 * @param $begTime
	 * @param $endTime
	 * @return $return 
	 */
	public function getUserCreditTotal($winner_id = 0, $begTime, $endTime) {
		if($winner_id <= 0){
			return 0;
		}
		$return = 0;	
		$willbe = $this->getUserCreditWillbe($winner_id, $begTime, $endTime);
		$done = $this->getUserCredit($winner_id, $begTime, $endTime, array(self::STATUS_FINISHED, self::STATUS_AUDITED));
		foreach($willbe as $item){
			if($item['id'] == $winner_id) {
				$return += intval($item['sumCredit']);
			}
		}
		foreach($done as $item){
			if($item['id'] == $winner_id) {
				$return += intval($item['sumCredit']);
			}
		}
		return $return;
	}
	/**
	 * 得到某个用户在一段时间内的某状态的总积分
	 * @param int 			$winner_id 		用户id
	 * @param string 		$bTime 	    	开始时间
	 * @param string 		$eTime	    	结束时间
	 * @param string|array 	$states    		状态,如果存在多种状态，是或的关系 默认为5已确认完成
	 * @param boolean		$withViewer		是否取观察者结果
	 */
	private function getCredit($winner_id = 0, $begTime, $endTime, $states=5, $withViewer = true, $onTime = 'done_time') {
		$where = array();
		if($winner_id > 0) {
			$where[] = 'T.winner_id = '. $winner_id;
		}
		if($begTime > 0) {
			$where[] = "T.$onTime >= $begTime";
		}
		if($endTime > 0) {
			$where[] = "T.$onTime < $endTime";
		}
		if(!is_array($states)) {
			$states = array(intval($states));
		}
		
		$where[] = "T.status in (".implode(',', $states).")";
		
		$whereStr = '1';
		if( !empty($where) ){
			$whereStr = implode(' and ', $where);
		}
		
		$sql = 'SELECT U.realname,U.id,U.total_score ,(
						SELECT  SUM(T.credit)  
						FROM '. $this->tableName() .' T 
						WHERE U.id = T.winner_id AND '. $whereStr .'
					) as sumCredit
				FROM `'. User::model()->tableName() .'` U WHERE 1';
		if(!$withViewer) {
			$viewerIds = User::getUsernamesToId(Yii::app()->params['viewer']);
			$sql .= !empty($viewerIds) ? " AND U.id NOT IN (".implode(',', $viewerIds).")" : '';
			
		}
		if($winner_id > 0) {
			$sql .= ' AND U.id = '. $winner_id;
		}
		$sql .= ' ORDER BY sumCredit DESC';
		return Yii::app()->db->createCommand($sql)->queryAll();
	}
	
	/**
	 *根据指定的关键字或者用户id得到相对应的任务列表
	 * @param		string     用来匹配的关键字
	 * @param		int     	用户id
	 * @return		array		匹配到的记录数据
	 */
	public function suggest($keyword=NULL,$user_id=0)
	{
		if(!empty($keyword)) {
			$models=$this->findAll(array(
				'condition'=>'subject LIKE :keyword',
				'order'=>'create_time desc',
				'params'=>array(':keyword'=>"%$keyword%")
			));			
		}

		if(!empty($user_id)) {
			$models=$this->findAll(array(
				'condition'=>'user_id = :user_id',
				'order'=>'create_time desc',
				'params'=>array(':user_id'=>"$user_id")
			));			
		}	
		
		$suggest=array();
		$receivers = array();
		$levenshtein_arr = array();
		foreach($models as $model) {
			if(isset($model->id)) {
				$sql = "SELECT * FROM task_receiver WHERE task_id=".$model->id;
				$taskReceiverList = Yii::app()->db->createCommand($sql)->queryAll();	
				if(!empty($taskReceiverList)) {
					foreach($taskReceiverList as $user) {
						$receivers[] = $user['user_id'];
					}
				}
			}
			//levenshtein算法排序之后取出前10条
			$dateline = date('Y-m-d',strtotime("Friday"));
			$suggest[] = array(
				'levenshtein'=>levenshtein($model->subject,$keyword),
				'value'=>$model->subject,  
				'description'=>$model->description,
				'gain'=>$model->gain,
				'man_hour'=>$model->man_hour,
				'dateline'=>$dateline,
				'difficulty'=>$model->difficulty,
				'receivers'=>$receivers,
				'subject'=>$model->subject
			);
			$levenshtein_arr[] = levenshtein($model->subject,$keyword);
		}
		array_multisort($levenshtein_arr, SORT_ASC, $suggest);
		$suggest = array_slice($suggest, 0, 10, true);
		return $suggest;
	}	
	/**
	 *
	 * 获取任务默认的积分
	 * @param int $man_hour
	 * @param int $difficulty
	 * @return int
	 * 
	 * @author RenChao
	 */
	public function getDefaultCredit($man_hour, $difficulty) {
		return ceil($this->difficultyDefaultCredit[$difficulty] * $man_hour);
	}
}
