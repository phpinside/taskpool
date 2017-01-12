<?php

/**
 * This is the model class for table "task_grade".
 *
 * The followings are the available columns in table 'task_grade':
 * @property string $id
 * @property string $task_id
 * @property string $user_id
 * @property integer $credit
 * @property string $description
 */
class TaskGrade extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return TaskGrade the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
//	此处不宜直接设置$this->user_id 变量，否则当前登录用户的ID容易与数据表中的ID产生混乱。
//	public function init(){
//		$this->user_id = Yii::app()->user->id;
//	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'task_grade';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('credit', 'required'),
			array('credit', 'numerical','min'=>1, 'max'=>1000),
			array('task_id', 'check_task_grade'),
			array('description', 'length', 'max'=>500),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, task_id, user_id, credit, description', 'safe', 'on'=>'search'),
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
			'id' => '评分记录自增ID',
			'task_id' => '任务ID',
			'user_id' => '用户ID',
			'credit' => '评价分数',
			'description' => '打分理由',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
		$criteria->compare('id',$this->id,true);
		$criteria->compare('task_id',$this->task_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('credit',$this->credit);
		$criteria->compare('description',$this->description,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function saveCredit() {
		$this->user_id = Yii::app()->user->id;
		return $this->save();
	}
	
	public function updateCredit() {
		$row = $this->findBySql("SELECT id FROM ".$this->tableName()." WHERE task_id=:task_id AND description LIKE '系统标准: %'", array(':task_id'=>$this->attributes['task_id']));
		if (!empty($row)) {
			$row->setAttribute('credit', $this->attributes['credit']);
			$row->save();
		} else {
			//为了兼容老版本
			$this->attributes = array(
				'task_id' => $this->attributes['task_id'],
				'user_id' => $this->attributes['user_id'],
				'credit' => $this->attributes['credit'],
				'description' => '系统标准: a级任务(1+0)*工时 b级任务(1+0.5)*工时 c级任务(1+1.5)*工时 d级任务(1+2.5)*工时',
			);
			$this->saveCredit();
		}
	}

	public function check_task_grade($attribute, $params){
		$sql = "select id from ".$this->tableName()." where task_id=:task_id and user_id=:user_id";
		$userId = Yii::app()->db->createCommand($sql)->queryScalar(array(':task_id'=>$this->$attribute,':user_id'=>Yii::app()->user->id));
		if($userId) {
			$this->addError($attribute, '您已经评过分不能重复评分！');
		}
	}
	
	/**
	 * 根据任务id得到,
	 * @param unknown_type $taskId
	 */
	public function getTaskAverageCredits($taskId) {
		$sql = 'SELECT AVG(credit) FROM '.$this->tableName().' where task_id=:task_id';
		return Yii::app()->db->createCommand($sql)->queryScalar(array(':task_id'=>$taskId));
	}
	
	/**
	 * 根据任务id得到，参与打分的总人数
	 * @param int $taskId
	 */
	public static function getTaskCreditsUsers($taskId) {
		$sql = 'SELECT count(user_id) FROM task_grade where task_id=:task_id';
		return Yii::app()->db->createCommand($sql)->queryScalar(array(':task_id'=>$taskId));
	}
    
    /**
	 * 获取任务打分记录
	 */
	public static function getGradeList($taskId) {
		return new CActiveDataProvider(__CLASS__, array(
			'criteria' => array(
				'condition'=>'task_id = '.$taskId,
				'order' => 'id desc'
			),
		));
	}
}