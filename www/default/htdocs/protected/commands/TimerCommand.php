<?php
class TimerCommand extends CConsoleCommand {
	
	public function actionStart() {
		
		while (1) {
			$openingTasks = (array)Task::getOpeningTasks();
			
			foreach ($openingTasks as $task) {
				echo $task['id']."\r\n";
				if(time() - $task['status_update_time'] > Yii::app()->params['dispatchTime']) {
					Task::model()->findByPk($task['id'])->dispatch();
				}
			}
			
			sleep(2);
		}
	}
 
}