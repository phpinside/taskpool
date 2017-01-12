<?php
class TaskMessageBehavior extends CBehavior {

	public function events() {
		return array_merge ( parent::events (), array (
				'onAfterSave' => 'afterSave',
				'onAfterOpen' => 'afterOpen',
				'onAfterDone' => 'afterDone',
				'onAfterDispatch' => 'afterDispatch',
				'onNobodyChoosed' => 'nobodyChoosed',
				'onAfterAudit' => 'afterAudit',
		));
	}

	/**
	 * @desc
	 * 		task添加完成之后触发的行为
	 * @param CEvent $event
	 */
	public function afterSave($event) {
		if($event->sender->isNewRecord) {
			$receivers = User::getReceiversAllUserId();
			$subject = "[新任务待评分]{$event->sender->subject}";
			$message = "还等什么？快去评分吧!&nbsp;&nbsp;&nbsp;".CHtml::link('现在就去!', $event->sender->getUrl(true));
			
			$message = new Message($receivers, $subject, $message);
			$message->send();
		}
	}
	
	/**
	 * 任务开放以后
	 * @param CEvent $event
	 */
	public function afterOpen($event) {
		
		$countMsg = $event->sender->status_counter == 1 ? '' : '第'.$event->sender->status_counter.'次';
		
		$receivers = TaskReceiver::getTaskReceivers($event->sender->id);
		$subject = "[新任务{$countMsg}开抢]{$event->sender->subject}";
		$taskLink = isset($_SERVER['SERVER_NAME'])
							? $event->sender->getUrl(true) : Yii::app()->params['baseUrl'].'task/view&id='.$event->sender->id;
		$message = new Message($receivers, $subject, CHtml::link('马上去看下!', $taskLink));
		$message->send();
	}
	
	/**
	 * 任务完成以后
	 * @param CEvent $event
	 */
	public function afterDone($event) {
		$username = Yii::app()->user->realname;
		
		$admins = array();
		foreach(Yii::app()->params['admins'] as $adminName) {
			if(!in_array($adminName, Yii::app()->params['viewer'])) {
				$admins[] = $adminName.'@hudong.com';
			}
		}
		
		$subject   = "[ $username 刚完成了任务 ]".$event->sender->subject;
		$link = CHtml::link('去看看!', $event->sender->getUrl(true));
		$message = new Message($admins, $subject, $link);
		$message->send();
	}
	
	/**
	 * 直接分配给某人
	 * @param CEvent $event
	 */
	public function afterDispatch($event) {
		$winnerInfo = Yii::app()->db->createCommand(
				'SELECT * FROM user WHERE id = :id'
		)->queryRow(true, array(':id'=>$event->sender->winner_id));
		$receivers = array($event->sender->winner_id);
		$subject   = '任务 "'.$event->sender->subject.'" 被最终分配给 '.$winnerInfo['realname'];
		$link 	   = CHtml::link('马上去看下!',   Yii::app()->params['baseUrl'].'task/view&id='.$event->sender->id );
		$message = new Message($receivers, $subject, $link);
		$message->send();
	}
	
	/**
	 * 任务分发
	 * @param CEvent $event
	 */
	public function nobodyChoosed($event) {
		$receivers = array($event->sender->user_id);
		$subject   = '任务 "'.$event->sender->subject.'" 无人申请 ';
		$link 	   = CHtml::link('点此修改任务，直接指定接收者',   Yii::app()->params['baseUrl'].'task/update&id='.$event->sender->id );
		$message = new Message($receivers, $subject, $link);
		$message->send();
	}
	
	/**
	 * 任务审核
	 * @param CModelEvent $event
	 */
	public function afterAudit($event) {
		$msgBody = Yii::app()->user->realname."审查通过了此任务<br />
			最终得分：{$event->sender->credit}<br />
			审核意见：{$event->sender->audit_message}<br /><br />
			".CHtml::link('去看看!', $event->sender->getUrl(true));
		
		$message = new Message(array($event->sender->winner_id), "[审查通过 ]".$event->sender->subject, $msgBody);
		$message->send();
	}
}