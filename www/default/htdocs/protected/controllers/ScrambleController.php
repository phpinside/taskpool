<?php

class ScrambleController extends Controller {
	
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
            array('allow',
                //'actions'=>array('do'),
                'users'=>array('@'),
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

	/**
	 * 首页，待完善
	 */
	public function actionIndex() {
	
	}
	
	/**
	 * 点击“抢”
	 */
	public function actionDo() {
		if(Yii::app()->request->isPostRequest) {
			$model=new Scramble('user_add');
			$model->task_id = $_GET['task_id'];
			if($model->validate()) {
				echo ($model->add()) ? 'ok' : print_r($model->errors, 1);
			} else {
				echo $model->getError('task_id');
			}
		} else {
			throw new CHttpException(400,'打开方式不对啊。。');
		}
	}

}