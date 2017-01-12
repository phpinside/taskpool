<?php
class Scramble extends MongoModel
{
    public $_id;
    public $user_id;
    public $task_id;
    public $create_time;
    public $status = self::STATUS_OPEN;
    
    const STATUS_CLOSED = 1;
    const STATUS_OPEN = 2;
    const STATUS_REOPEN = 3;
 
 
    // We can define rules for fields, just like in normal CModel/CActiveRecord classes
    public function rules()
    {
        return array(
            array('task_id', 'required'),
            array('task_id', 'validTask', 'on'=>'user_add')
        );
    }
    
    /**
     * 验证规则
     * @param unknown_type $attribute
     * @param unknown_type $params
     */
	public function validTask($attribute, $params) {
		if(!$this->hasErrors($attribute)) {
			$task_id = is_string($this->$attribute) ? new MongoId($this->$attribute) : $this->$attribute;
			$task = EDMSQuery::instance('task')->findOne(array('_id'=>$task_id));
			if(!$task) {
				$this->addError($attribute, '所抢任务不存在~');
			} else {
				$user_id = Yii::app()->user->id;
				if(!in_array($user_id, $task['receivers'])) { //String\MongoId 类型的，in_array都返回true
					$this->addError($attribute, '不能抢这个任务啊~');
				} else {
					$log = EDMSQuery::instance('scramble')->findOne(array('user_id'=>$user_id, 'task_id'=>$task_id));
					if($log) {
						$this->addError($attribute, '这个任务已抢过~');
					} 
				}
			}
		}
	}    
 

    /**
     * 添加抢任务记录
     */
    public function add() {
    	if(is_string($this->task_id)) {
    		$this->task_id = new MongoId($this->task_id);
    	}
    	$scramble = $this->attributes;
    	    	
		$scramble['_id'] = new MongoId();
    	$scramble['user_id'] = Yii::app()->user->id;
    	$scramble['create_time'] = new MongoDate();
    	$result = false;
    	try 
    	{
    		$result = EDMSQuery::instance("scramble")->insert($scramble, array('safe'=>true));	
    	}
    	catch (Exception $e) { echo $e->getMessage(); }
    	return $result;
    }
    

 
}

