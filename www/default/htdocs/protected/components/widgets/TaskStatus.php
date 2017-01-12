<?php 
class TaskStatus extends CWidget {
	
	public $dataProvider;
	public $crrentStatus = 'all';
	//弃用显示的状态
	public $disableStatus;
	
	public function init() {
		if($this->dataProvider===null)
			throw new CException(Yii::t('TaskStatus','The "dataProvider" property cannot be empty.'));
		$this->dataProvider->getData();
	}
	/**
	 * 执行 widget，显示标签云
	 */
	public function run() {
		echo '任务状态：| ';
		
		foreach(Task::getStatus() as $status=>$text) {
			if (!empty($this->disableStatus) && in_array($status, $this->disableStatus)) {
				continue;
			}
			if($this->crrentStatus == $status) {
				echo "<span class='task-s-{$status}' style='background: none repeat scroll 0 0 #E5F1F4;'>".CHtml::link($text, array('task/manage', "Target[status]"=>$status))."</span> | ";
			}else {
				echo "<span class='task-s-{$status}'>".CHtml::link($text, array('', "Target[status]"=>$status))."</span> | ";
			}
		}
		
		$data=$this->dataProvider->getData();
	}
}