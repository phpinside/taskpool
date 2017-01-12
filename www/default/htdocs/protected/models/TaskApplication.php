<?php

/**
 * This is the model class for table "task_application".
 *
 * The followings are the available columns in table 'task_application':
 * @property integer $task_id
 * @property integer $user_id
 * @property integer $create_time
 * @property integer $status
 */
class TaskApplication extends CActiveRecord
{
	const STATUS_WAITING = 1; //已申请，等候通过
 
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return TaskApplication the static model class
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
		return 'task_application';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id', 'required'),
			array('task_id, user_id, create_time, status', 'numerical', 'integerOnly'=>true),
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
			'task_id' => 'Task',
			'user_id' => 'User',
			'create_time' => 'Create Time',
			'status' => 'Status',
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
	

	
}