<?php

/**
 * This is the model class for table "task_log".
 *
 * The followings are the available columns in table 'task_log':
 * @property integer $id
 * @property integer $task_id
 * @property string $info
 * @property integer $create_time
 */
class TaskLog extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return TaskLog the static model class
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
		return 'task_log';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, task_id, info, create_time', 'required'),
			array('id, task_id, create_time', 'numerical', 'integerOnly'=>true),
			array('info', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, task_id, info, create_time', 'safe', 'on'=>'search'),
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
			'task_id' => 'Task',
			'info' => 'Info',
			'create_time' => 'Create Time',
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
			)
		);
	}	

	/**
	 * 添加任务操作记录
	 */
	public static function addLog($task_id, $info) {
		$log = new TaskLog();
		$log->task_id = $task_id;
		$log->info = $info;
		$result = $log->insert();
		unset($log);
		return $result;
	} 
	
	/**
	 * 获取任务操作记录
	 */
	public static function getTaskLogs($task_id) {
		return new CActiveDataProvider(__CLASS__, array(
			'criteria' => array(
				'condition'=>'task_id = '.$task_id,
				'order' => 'create_time desc, id desc'
			),
		));
	}
}