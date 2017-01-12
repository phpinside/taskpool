<?php

class UserController extends Controller
{	
	public function actionScore()
	{
		$this->render('score');
	}

	public function actionSpace()
	{
		$winner_id = isset($_GET['id']) ? intval($_GET['id']) : intval(Yii::app()->user->id);
		$model=User::model()->findByPk($winner_id);
		if($model === null) {
			throw new CHttpException(404,'页面找不到。。');
		}
		$detail = User::model()->getUserScoreDetail($winner_id);
		$userTasks = new CActiveDataProvider('Task', array(
			'pagination' =>array(
				'pageSize' => 20,
				'pageVar'=>'page',
			),
			'criteria' => Task::getUserTasks($winner_id),
			'sort'=>array(
				'defaultOrder'=>'id desc',
			),
		));
		$this->render('space', array('userTasks'=>$userTasks, 'model'=>$model,'detail'=>$detail, 'title'=>'个人信息页'));
	}

	public function actionTask()
	{
		$this->render('task');
	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}