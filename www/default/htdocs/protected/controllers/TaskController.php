<?php

class TaskController extends Controller {
	public $layout='//layouts/task';

	public function filters() {
		return array(
			'accessControl',
		);
	}

    /**
     * 权限控制。
     * ?： 未登录用户；*：所有用户；@:已登录用户
     * @see CController::accessRules()
     */
	public function accessRules() {
        return array(
            array('deny',
                'actions'=>array('Manage', 'My', 'NoApplications', 'Add', 'Open', 'Update', 'Apply', 'Done', 'Audit', 'Credit'),
                'users'=>array('?'),
            ),
            array('allow',
                'actions'=>array('Manage', 'NoApplications', 'Add', 'Open', 'Update', 'Audit', 'MyPublic'),
                'users'=>Yii::app()->params['admins'],
            ),
			array('allow',
                'actions'=>array('Add', 'Update', 'MyPublic'),
                'users'=>Yii::app()->params['team_leader'],
            ),
            array('deny',
                'actions'=>array('Manage', 'MyPublic', 'NoApplications', 'Add', 'Open', 'Update', 'Audit',),
                'users'=>array('@'),
            ),
        );
    }

	/**
	 * 首页，待完善
	 */
	public function actionIndex() {
		if(Yii::app()->user->isGuest) {
			$this->actionAll();
		} elseif(in_array(Yii::app()->user->name, Yii::app()->params['admins'])) {
			$this->actionManage();
		} else {
			$this->actionMy();
		}
	}

	/**
	 * 发布的任务，待完善
	 */
	public function actionManage() {
		$criteria = new CDbCriteria();
		if(isset($_GET['Target'])) {
			$criteria->addCondition('status='.$_GET['Target']['status']);
		}
		
		$dataProvider =  new CActiveDataProvider('Task', array(
			'pagination' =>array(
				'pageSize' => 20,
				'pageVar'=>'page',
			),
			'criteria' => $criteria,
			'sort'=>array(
				'defaultOrder'=>'id desc',
			)
		));
		
		$this->render('manage', array('dataProvider'=>$dataProvider, 'subTitle'=>'任务管理'));
	}

	/**
	 * 我发布的任务
	 */
	public function actionMyPublic() {
		$criteria = new CDbCriteria();
		if(isset($_GET['Target'])) {
			$status = $_GET['Target']['status'];
			$criteria->addCondition('t.status='.$_GET['Target']['status']);
		}
		$criteria->addCondition('user_id='.Yii::app()->user->id);
		$criteria->with='winner';
		
		$dataProvider = new CActiveDataProvider('Task', array(
			'pagination'=> array(
				'pageSize' => 20,
				'pageVar'=>'page',
			),
			'sort' => array('defaultOrder' => 't.id DESC'),
			'criteria' => $criteria
		));

		$this->render('all', array(
			'dataProvider'=>$dataProvider,
			'subTitle'=>'所有任务'
		));
	}

	/**
	 * 可申请的任务
	 */
	public function actionMy() {	
		//定义取消显示状态
		$disableStatus = array(Task::STATUS_ADDED, Task::STATUS_OPEN);
		$status = isset($_GET['Target']['status']) ? $_GET['Target']['status'] : '';	
		
		$historyTasks = new CActiveDataProvider('Task', array(
			'criteria' => Task::getHistoryTasks(Yii::app()->user->id, $status),
			'pagination' => array(
				'pageSize' => 20,
				'pageVar'=>'page',
			),
			'sort'=>array(
				'defaultOrder'=>'t.create_time desc',
			)
		));
		$this->render('my', array('historyTasks'=>$historyTasks, 'disableStatus' => $disableStatus, 'subTitle'=>'我抢到的任务'));
	}
	/**
	 * 任务详情页
	 */
	public function actionView() {
		$id = intval($_GET['id']);
		$task = $this->loadModel($id);
		$neighborId = $task->getNeighborId($id);
		$taskLog = TaskLog::getTaskLogs($id);
        $gradeData = TaskGrade::getGradeList($id);
		$this->render('show', array('task'=>$task, 'taskLog'=>$taskLog, 'gradeData'=>$gradeData, 'neighborId' => $neighborId));
	}

	/*
	*
	* 任务确认页
	*/
	public function actionTodone () {
		$id = intval($_GET['taskId']);
		$task = $this->loadModel($id);
		$task->setScenario('done');
		//$taskLog = TaskLog::getTaskLogs($id);
		$this->render('todone', array('task'=>$task));
	}

	/**
	 * 没有任何人申请的任务
	 */
	public function actionNoApplications() {
		$dataProvider = new CActiveDataProvider('Task', array(
			'criteria'=> Task::getNoApplications(),
			'pagination' => array(
				'pageSize' => 20,
				'pageVar'=>'page',
			),
			'sort' => array('defaultOrder' => 'id DESC')
		));
		$this->render('all', array('dataProvider'=>$dataProvider, 'subTitle'=>'没有任何人申请的任务'));

	}

	/**
	 * 所有任务列表
	 */
	public function actionAll() {
		$criteria = new CDbCriteria();
		if(isset($_GET['Target'])) {
			$status = $_GET['Target']['status'];
			$criteria->addCondition('t.status='.$_GET['Target']['status']);
		}
		$criteria->with='winner';
		
		$dataProvider = new CActiveDataProvider('Task', array(
			'pagination'=> array(
				'pageSize' => 20,
				'pageVar'=>'page',
			),
			'sort' => array('defaultOrder' => 't.id DESC'),
			'criteria' => $criteria

		));

		$this->render('all', array(
			'dataProvider'=>$dataProvider,
			'subTitle'=>'所有任务'
		));
	}


	/**
	 * 任务添加
	 */
	public function actionAdd() {
		$model=new Task;
		if(isset($_POST['ajax']) && $_POST['ajax']==='task-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		if(isset($_POST['Task'])) {
			$model->attributes=$_POST['Task'];
			if($model->save()) {
				Yii::app()->user->setFlash('task','添加成功！');
				$this->refresh();
			}
		}else if(!empty(Yii::app()->session['taskFromBugTracker'])) {
			$model->attributes = unserialize(Yii::app()->session['taskFromBugTracker']);
			unset(Yii::app()->session['taskFromBugTracker']);	
		}
		$receivers = in_array(Yii::app()->user->name, Yii::app()->params['admins']) ? User::getUserWithScore() : User::getReceiversSimpleUsersList();
		$this->render('task_form',array('model'=>$model, 'title'=>'添加任务', 'receivers'=>$receivers, 'dateline'=>''));
	}
	
	/**
	 * 从bug系统添加任务
	 */
	public function actionAddFromBugTracker() {
		Yii::app()->session['taskFromBugTracker'] =  serialize($_POST['taskFromBugTracker']);
		$this->redirect(array('task/add'));
	}

	/**
	 * 开放任务申请
	 */
	public function actionOpen() {
		$openIds = (array)$_POST['taskIds'];
		if(!empty($openIds)) {
			$errors = array();
			foreach($openIds as $taskId) {
				$task = Task::model()->findByPk($taskId);
				if(!is_null($task)) {
					$task->open();
					$error = $task->getError('credit');
					if(!empty($error)) {
						$errors[] = $error;
					}
				}
			}
			if(!empty($errors)) {
				Yii::app()->user->setFlash('task', implode('<br/>', $errors));
			} else {
				Yii::app()->user->setFlash('task', '成功开放所选任务！');
			}
		} else {
			Yii::app()->user->setFlash('task', '未选中任何任务。');
		}
		$this->redirect(Yii::app()->request->urlReferrer);
	}

	/**
	 * 修改任务
	 */
	public function actionUpdate() {
		$id = intval($_GET['id']);
		$task = $this->loadModel($id);
		if(in_array(Yii::app()->user->name, Yii::app()->params['team_leader'])) {
			if($task->user_id != Yii::app()->user->id && !in_array(Yii::app()->user->name, Yii::app()->params['admins'])) {
				throw new CHttpException(403, '您未被授权执行这个动作!');
			}
		}
		$task->setScenario('edit');
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='task-form') {
			echo CActiveForm::validate($task);
			Yii::app()->end();
		}
		
		if(isset($_POST['Task'])){
			$task->attributes=$_POST['Task'];
			//计算初始分值
			$task->setAttribute('credit', $task->getDefaultCredit($task->attributes['man_hour'], $task->attributes['difficulty']));
			$task->dateline=strtotime($task->dateline);
			
			if($task->save()) {
				$this->redirect(array('view','id'=>$task->id));
			}
		}
		$task->recvs = CHtml::listData($task->receivers, 'id', 'id');
		$task->dateline=date('Y-m-d',$task->dateline);
		$this->render('task_form',array(
			'model'=>$task,
			'title'=>'修改任务',
			'receivers' =>(in_array(Yii::app()->user->name, Yii::app()->params['admins']) ? User::getUserWithScore() : User::getSimpleUsersList())
		));
	}

	/*申请任务*/
	public function actionApply() {
		if(Yii::app()->request->isPostRequest) {
			$id = intval($_POST['taskId']);
			$task = Task::model()->findByPk($id);

			if($task->apply()) {
				$msg = '申请已提交，请等候确认。';
			} else {
				$msg = $task->getError('user_id');
			}

			Yii::app()->user->setFlash('task', $msg);
			if(!isset($_GET['ajax'])) {
				$this->redirect($task->url);
			}
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}


	/*确认完成任务*/
	public function actionDone() {
		if(isset($_POST['Task'])) {
			$id = $_POST['Task']['id'];
			$task = Task::model()->findByPk($id);
			$task->setScenario('done');
		} else {
			$task = Task::model();
		}
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='task-form') {
			echo CActiveForm::validate($task);
			Yii::app()->end();
		}
		
		if(Yii::app()->request->isPostRequest) {
			
			$remark = ($_POST['Task']['remark']);
			$task->remark = $remark;
			if($task->done()) {
				$msg = '确认完毕，请等候审查。';
			} else {
				$msg = $task->getError('remark');
			}

			Yii::app()->user->setFlash('task', $msg);
			if(!isset($_GET['ajax'])) {
				$this->redirect($task->url);
			}
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}

	/*审查完成情况*/
	public function actionAudit() {
		if(Yii::app()->request->isPostRequest) {
			$id = intval($_POST['taskId']);
			$task = Task::model()->findByPk($id);
			$task->attributes=$_POST['Task'];
			if($task->audit()) {
				Yii::app()->user->setFlash('task', '审核完毕~');
			} 
			
			if(!isset($_GET['ajax'])) {
				$this->redirect($task->url);
			}
		}
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	}


	/**
	 * 删除任务
	 */
	public function actionDelete() {
		if(Yii::app()->request->isPostRequest)
	    {
	        // we only allow deletion via POST request
	        $task = Task::model()->findByPk($_GET['id']);
	        if($task->status != Task::STATUS_ADDED) {
	        	throw new CHttpException(403, '任务已开放，不能删除.');
	        } else {
	        	$task->delete();
	        }

	        if(!isset($_GET['ajax'])) {
	            $this->redirect(array('task/manage'));
	        }
	    }
	    else {
	        throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	    }

	}

	/**
	 * 关闭任务
	 */
	public function actionClosed(){
		if(Yii::app()->request->isPostRequest)
	    {
			$id = intval($_POST['taskId']);
	        $task = Task::model()->findByPk($id);
			if($task->status >= Task::STATUS_FINISHED){
	        	throw new CHttpException(403, '任务已完成，不能关闭！');
			}else{
	        	$task->close();
			}
	        if(!isset($_GET['ajax'])) {
	            $this->redirect(array('task/manage'));
	        }
		}
	    else {
	        throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
	    }
	}

	/**
	 * 任务导出--toExcel
	 */
	public function actionExport() {
		//得到在时间段内开放的所有任务,默认为上周一到周末
		$begTime = Util::getLastMonday(1);
		$endTime = Util::getLastMonday(7);
		
		if(Yii::app()->request->isPostRequest){
			$lastStartDate = strtotime($_POST['startdate']);
			$lastEndDate = strtotime($_POST['enddate']) + 86399;//结束时间应该是 23::59:59 => 86399
			
			// 文件下载可参考： http://www.yiiframework.com/doc/api/1.1/CHttpRequest#sendFile-detail
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="技术部执行力表格'.date('Ymd', $lastEndDate).'.xls"');
			header('Cache-Control: max-age=0');
			
			$objPHPExcel = Task::model()->exportTaskExcel($lastStartDate, $lastEndDate);
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
			exit;
		}
		
		$this->render('makeexport',array(
				'starttime' => $begTime,
				'endtime' => $endTime,
		));
	}
	
	/**
	 * 按月导出并提供下载某人的任务列表，计算KPI使用
	 * 
	 * 潘雪鹏 2012-10-15
	 * 
	 * 逻辑：
	 * 1) 接受浏览器提交参数$_POST['recvs', 'year', 'month']。
	 * 2) 遍历name列表，逐个获取某人某月的任务信息数据。
	 * 3) 根据第2步的任务信息数据，在runtime目录生成临时xls文件。
	 * 4) 生成下载文件，
	 *    单人：直接下载xls文件，
	 *    多人：将多个xls文件打包成zip文件下载。
	 */
	public function actionExportKpi(){
		$model=new KpiForm;
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='KpiForm') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		$allUsersList = User::getSimpleUsersList();
		
		if(isset($_POST['KpiForm'])) {
			$model->attributes = $_POST['KpiForm'];
			if( $model->validate() ){
				// 根据年月计算开始和结束时间点
				$year = $_POST['KpiForm']['year'];
				$month = $_POST['KpiForm']['month'];
				$recvs = $_POST['KpiForm']['recvs'];
				
				$begTime = strtotime("$year-$month-1");
				// 结束时间 +1 month 后将成为下个月1号（2012-10-01）00:00:00
				$endTime = strtotime('+1 month', $begTime);
				
				if(!empty($_POST['KpiForm']['startdate'])) {
					$begTime = strtotime($_POST['KpiForm']['startdate']);
				}
				if(!empty($_POST['KpiForm']['enddate'])) {
					$endTime = strtotime($_POST['KpiForm']['enddate']);
				}
				
				$taskModel = new Task;
				
				$excelFileList = array();
				foreach( $recvs as $uid ){
					$taskList = $taskModel->getCompletedList($begTime, $endTime, $uid);
					
					$department = str_replace('任务池-', '', Yii::app()->name);
					$userInfo = array(
						'uid'=>$uid,
						'year'=>$year,
						'month'=>$month,
						
						'realname'=>$allUsersList[$uid], 
						'profession'=>'',
						'department'=>$department,
						'superior'=>'',
					);
					
					// 创建临时xls文件
					$downloadName = "{$department}_{$allUsersList[$uid]}_{$year}年{$month}月_KPI考核表.xls";
					$downloadName = iconv('utf-8', 'gbk', $downloadName);
					$excelFileList[] = array(
						'tempFile' => $taskModel->createExcelFile($userInfo, $taskList),
						'downloadName' => $downloadName,
					);
				}
				
				// 浏览器下载
				$this->download("{$department}_{$year}年{$month}月_KPI考核表.zip", $excelFileList);
				
				//Yii::app()->user->setFlash('task','导出成功！');
				//$this->refresh(); ?????
			}
		}
		
		$this->render('exportKpi', array(
			'model'=>$model, 'title'=>'导出KPI表', 'allUsersList'=>$allUsersList
		));
	}
	
	/**
	 * 下载任务表格
	 * 
	 * 潘雪鹏 2012-10-15
	 * @param string $zipFileName 打包下载的文件名称
	 * @param array $excelFileList excel文件列表
	 * 
	 */
	private function download($zipFileName, $excelFileList){
		if( count($excelFileList) > 1 ){
			$zip = new ZipFile();
			foreach( $excelFileList as $item ){
				$zip->addFile($item['tempFile'], $item['downloadName']);
				// 删除临时文件
				unlink($item['tempFile']);
			}
			
			$zip->download($zipFileName);
		}else{
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$excelFileList[0]['downloadName'].'"');
			header('Cache-Control: max-age=0');
			
			$tempname = $excelFileList[0]['tempFile'];
			echo file_get_contents($tempname);
			unlink($tempname);
		}
		Yii::app()->end();
	}

	/**
	 * 生成排行榜单
	 */
	public function actionRank() {
		
//		$begTime = Util::getLastMonday(1);
//		$endTime = Util::getLastMonday(7);
		$rank = new Rank();
		if(Yii::app()->request->isPostRequest) {
			
			$rank->attributes = $_POST['Rank'];
			
			if($rank->makeRank()) {
				Yii::app()->user->setFlash('task', '成功生成排行榜单！');
			} else {
				Yii::app()->user->setFlash('task', 'D\'oh!');
			}
		}
		$this->render('makerank',array(
			'rank'=>$rank,
//			'starttime'=>$begTime,
//			'endtime'=>$endTime,
		));
	}
	
	/**
	 * 检查是否显示审核信息
	 * @param Task $task
	 * @return boolean
	 */
	public function showAuditMessage($task) {
		return $task->status == Task::STATUS_AUDITED
			&& (Yii::app()->user->id==$task->user_id || Yii::app()->user->id==$task->winner_id || in_array(Yii::app()->user->name, Yii::app()->params['admins']));
	}
	/**
	 * 检查是否显示任务完成情况描述
	 * @param Task $task
	 * @return boolean
	 */
	public function showRemark($task) {
		return (Task::STATUS_FINISHED==$task->status || $task->status==Task::STATUS_AUDITED) 
			&& (Yii::app()->user->id==$task->user_id || Yii::app()->user->id==$task->winner_id || in_array(Yii::app()->user->name, Yii::app()->params['admins'])); 
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	private function loadModel($id)	{
		$model=Task::model()->with('user')->with('receivers')->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'页面找不到。。');
		return $model;
	}

	/**
	 * 给任务打分
	 */
	public function actionCredit() {
		$id = intval($_GET['id']);
		$model = new TaskGrade();
		$model->task_id = $id;
		$task = $this->loadModel($id);
		if($task->status == Task::STATUS_CLOSE){
			Yii::app()->user->setFlash('task','此任务已关闭不能评分！');
			$this->redirect($task->url);
		}
		if($task->status == Task::STATUS_ADDED && isset($_POST['TaskGrade']) && $id>0 ) {
			$model->attributes=$_POST['TaskGrade'];
			if($model->saveCredit()) {
				Yii::app()->user->setFlash('task','评分成功！');
				$this->redirect(array('task/index'));
			}
		}
		if($model->credit == '0') {
			$model->credit = '';
		}
		$this->render('credit',array('model'=>$model, 'task'=>$task));
	}
	/**
	 *任务标题类似百度suggest提示框
	 */
	public function actionSuggests(){
		$keyword = trim($_GET['term']);
		if(empty($keyword)) {
			$tasks=Task::model()->suggest(NULL,Yii::app()->user->id);
			echo CJSON::encode($tasks);	 
		}elseif(!empty($keyword)) {
			$tasks=Task::model()->suggest($keyword);
			echo CJSON::encode($tasks);
		}
    } 		  
}