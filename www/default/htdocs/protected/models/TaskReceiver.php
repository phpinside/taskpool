<?php

/**
 * This is the model class for table "task_user".
 *
 * The followings are the available columns in table 'task_user':
 * @property integer $task_id
 * @property integer $user_id
 */
class TaskReceiver extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return TaskUser the static model class
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
		return 'task_receiver';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('task_id, user_id', 'required'),
			array('task_id, user_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('task_id, user_id', 'safe', 'on'=>'search'),
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
			'users'=>array(self::BELONGS_TO, 'User', 'user_id'),
			'taskusers'=>array(self::BELONGS_TO, 'TaskUser', 'task_id'),
			'tasks'=>array(self::BELONGS_TO, 'Task', 'task_id')
			
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'task_id' => 'Task',
			'user_id' => 'User',
		);
	}

	public function saveFromArray($users) {
		if(!is_array($users))return;
		foreach($users as $user_id) {
			$this->isNewRecord = true;
			$this->user_id = $user_id;
			$this->save(false);
		}
	}
	
	/**
	 * 获取任务接收者
	 * @param int $task_id
	 */
	public static function getTaskReceivers($task_id) {
		$cmd = Yii::app()->db->createCommand("SELECT user_id FROM task_receiver WHERE task_id = :task_id");
		return $cmd->queryColumn(array(':task_id'=>$task_id));
	}
	


}