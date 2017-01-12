<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}
	public function filters() 
	{
		return array(
			'accessControl + Ranklist, CreditList',
		);
	}
	public function accessRules()
	{
		return array(
			array('allow',
            	 'actions'=>array('Ranklist', 'CreditList'),
            	 'users'=>Yii::app()->params['admins'],
            ),
            
			array('deny',
            	 'actions'=>array('Ranklist', 'CreditList'),
            	 'users'=>array('?'),
            ),
           
            array('deny',
            	 'actions'=>array('Ranklist', 'CreditList'),
            	 'users'=>array('@'),
            ),
            
            
		);
	}
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$preWeekTops=Rank::model()->getPreTop(1);
		$preMonthTops=Rank::model()->getPreTop(2);
		$preHalfyearTops=Rank::model()->getPreTop(3);
		$preYearTops=Rank::model()->getPreTop(4);
		
		$this->render('index',array(
			'preWeekTops'=>$preWeekTops,
			'preMonthTops'=>$preMonthTops,
			'preHalfyearTops'=>$preHalfyearTops,
			'preYearTops'=>$preYearTops,
			'title'=>'积分排行榜'
		));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$headers="From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'],$model->subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
	
	/**
	 * 权限设置
	 */
	public function actionPermit() {
		$this->layout = '//layouts/task';
		$model=new PermitForm();
		$model->pushDefaultAttribute();
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='task-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		if(isset($_POST['PermitForm'])) {
			$model->attributes=$_POST['PermitForm'];
			if($model->save()) {
				Yii::app()->user->setFlash('PermitForm','设置成功！');
				$this->refresh();
			}
		}
		$this->render('permit',array('model'=>$model, 'title'=>'权限设置'));
	}
	
	public function actionTest(){
		$this->display();
	}
	
	/**
	 * get fullly detail rank information
	 */
	public function actionRanklist()
	{
		$type = isset($_GET['type']) ? intval($_GET['type']) : 1;
		$scoreDetail=Rank::model()->getPreTop($type, null);
		$dataProvider=new CArrayDataProvider($scoreDetail, array(
			'keyField' => false,
			'sort' => array(
          		'attributes'=>array(
               		'score'
         		),
          	),
          	'pagination'=>array(
          		'pageSize'=>20,
          	)
      	));
      	$subtitle = Rank::getRankName($type) . '积分排行';
      	$this->render('ranklist', array(
			'dataProvider'=>$dataProvider,
			'subTitle'=>$subtitle
		));
	}
	
	public function actionCreditList() {
		//0. 排序条件
		//1. 所有用户本周已得分和本周预计得分（动态获取）
		$begTime = Util::getThisMonDay(1);
		$endTime = date('Y-m-d H:i:s', strtotime(Util::getThisMonDay(8))-1);//下个星期一0点-1s
		//本周已得分
		$creditsReady  = Task::model()->getUserCredit(0, strtotime($begTime), strtotime($endTime), Task::STATUS_AUDITED, false);
		$finalData = array();
		foreach($creditsReady as $userReady) {	
			$finalData[$userReady['id']]['user_id'] = $userReady['id'];
			$finalData[$userReady['id']]['realname'] = $userReady['realname'];
			$finalData[$userReady['id']]['week_ready'] = $userReady['sumCredit'];
			//获取本周预计得分
			$finalData[$userReady['id']]['week_recredit'] = Task::model()->getUserCreditTotal($userReady['id'], strtotime($begTime), strtotime($endTime));
			$finalData[$userReady['id']]['total'] = $userReady['total_score'];
		}
		//2. 取得积分排行榜，上周，上月，上季度，半度，全年的分，分别赋值到对应的用户上面
		$rankType = Rank::model()->getRankType();
		$scoreDetail = array();
		foreach($rankType as $type) {
			$scoreDetail[$type] = Rank::model()->getStatisticsData($type, 5);
		}
		
		
		//拼接数据
		$return = array(1 => 'one', 2 => 'two',	3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six');
		foreach($finalData as $key => $creadits) {
		//	$finalData[$key]['man_hour'] = User::getUserManhour($key, strtotime(Util::getThisMonDay(1)), strtotime(Util::getThisMonDay(8)));
		$finalData[$key]['man_hour'] = User::getCurrentManhour($key);//上周任务工时改为累计没完成的工时
			foreach($scoreDetail as $type => $users) {
				$isHave = false;
				foreach($users as $user) {
					if($key == $user['id']) {
						$finalData[$key][$return[$type]] = $user['sumCredit'];
						$isHave = true;
					}
				}
				if(!$isHave) {
					$finalData[$key][$return[$type]] = 0;
				}
			}
		}
		$subtitle = '积分排行榜单';
		$dataProvider = new CArrayDataProvider($finalData, array(
				'keyField' => false,
				'sort' => array(
						'attributes'=>array(
								'one',
								'two',
								'three',
								'four',
								'five',
								'six',
								'man_hour',
								'week_recredit',
								'week_ready',
								'total',
						),
				),
				'pagination'=>array(
						'pageSize'=>200,
				)
		));
		
		$this->render('creditslist', array(
				'dataProvider'=>$dataProvider,
				'subTitle'=>$subtitle
		));
	}
}