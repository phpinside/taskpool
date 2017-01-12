<?php
$this->pageTitle=Yii::app()->name . ' - '. $task->subject;
$this->breadcrumbs=array(
	'任务'=>array('task/index'),
	$task->subject,
);
?>

<h1><?php echo $task->subject;?></h1>

<?php if(Yii::app()->user->hasFlash('task')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('task'); ?>
</div>

<?php endif; ?>

<div class="form">
<p>
<?php 
$receivers = '';
foreach($task->receivers as $user){
 	 $params=array('id'=>$user->id);
 	 $url=Yii::app()->createUrl('user/space', $params);
 	 $receivers .= "<a href='{$url}'>{$user->realname}</a>&nbsp;&nbsp;";
}

Yii::app()->clientScript->registerCss('task_detail', '#task_detail {font-size:1.2em} #task_detail td{text-align:left;word-break:break-all} #task_detail th {width: 5em;}');
$this->widget('zii.widgets.CDetailView', array(
	'id'=>'task_detail',
	'data'=>$task,
	'attributes'=>array(
		'man_hour',
		array('label'=>'打分人数', 'type'=>'raw', 'value'=>TaskGrade::getTaskCreditsUsers($task->id)),
		array('label'=>$task->getAttributeLabel('difficulty'), 'type'=>'raw', 'value'=>Task::getDifficulties($task->difficulty)),
		array('label'=>$task->getAttributeLabel('status'), 'type'=>'raw', 'value'=>'<span class="task-s-'.$task->status.'">'.Task::getStatus($task->status).'</span>'),
		array('label'=>$task->getAttributeLabel('description'), 'type'=>'html', 'value'=>nl2br($task->description)),
		array('label'=>$task->getAttributeLabel('gain'), 'type'=>'ntext', 'value'=>$task->gain),
		array('label'=>$task->getAttributeLabel('dateline'), 'type'=>'ntext', 'value'=>date("m-d", $task->dateline).", ".Util::chineseWeekday($task->dateline)),
		array('label'=>$task->getAttributeLabel('remark'), 'type'=>'ntext', 'value'=>$task->remark, 'visible'=>$this->showRemark($task) ),
		array('label'=>$task->getAttributeLabel('audit_message'), 'type'=>'ntext', 'value'=>$task->audit_message, 'visible'=>$this->showAuditMessage($task)),
		array('label'=>$task->getAttributeLabel('credit'), 'type'=>'ntext', 'value'=>$task->credit, 'visible'=>($task->status==TASK::STATUS_AUDITED)),
		array('label'=>'接收者', 'type'=>'html', 'value'=>$receivers)
	)
		
));

if($task->status == Task::STATUS_ADDED) {
	echo CHtml::form(array('task/credit'), 'GET');
	echo CHtml::hiddenField('id', $task->id);
	echo CHtml::submitButton('我要评分!', array('style'=>'font-size: 32px;'));
	echo CHtml::endForm();
}
//1、任务是否可以申请，如果任务已经开放申请，并且我在任务接受者里面，就可以申请。
$applicable=$task->checkApplicable(Yii::app()->user->id, false);

if($applicable) :
	echo CHtml::form(array('task/apply'));
	echo CHtml::hiddenField('taskId', $task->id);
	echo CHtml::submitButton('这活我干!', array('style'=>'font-size: 32px;'));
	echo CHtml::endForm();
endif;
//2、任务已经申请过，并且任务状态不是完成状态，就显示这个
if( $task->status<Task::STATUS_FINISHED  && Yii::app()->user->id == $task->winner_id ) :
	echo ' '.$task->getError('user_id');
	echo CHtml::form(array('task/todone'),'get' );
	echo CHtml::hiddenField('taskId', $task->id);
	echo CHtml::submitButton('干完了!', array('style'=>'font-size: 32px;'));
	echo CHtml::endForm();
endif;
//3、提交过任务完成的申请，但是还没有经过任务发起者的审核。当管理员查看就出现这个按钮
if(Task::STATUS_FINISHED==$task->status && in_array(Yii::app()->user->name, Yii::app()->params['admins'])) :
	$form=$this->beginWidget('CActiveForm', array(
		'id'=>'task-audit',
		'action'=>array('task/audit'),
		'enableClientValidation'=>TRUE,
		'clientOptions'=>array(
			'validateOnSubmit'=>true,
		),
	));
	echo '<div class="row">';
	echo $form->hiddenField($task, 'id', array('name'=>'taskId'));
	echo $form->labelEx($task, 'credit');
	echo $form->textField($task, 'credit', array('size'=>5, 'maxlength'=>3));
	echo $form->error($task, 'credit');
	echo '</div>';
	
	echo '<div class="row">';
	echo $form->labelEx($task, 'audit_message');
	echo $form->textArea($task, 'audit_message',array('rows'=>3, 'cols'=>'60'));
	echo $form->error($task, 'audit_message');
	echo '</div>';
	echo '<div class="row buttons">';
	echo CHtml::submitButton('通过审核!');
	echo '</div>';
	$this->endWidget();
endif;
//4、任务状态为已经被发起人审核通过，则为终结状态。
if(Task::STATUS_AUDITED==$task->status) :
	echo ' '.$task->getError('user_id');
	echo CHtml::submitButton('本任务已结束!', array('disabled'=>'disabled'));
endif;
//6、只要任务不是处于完成状态并且是管理员身份，就可以点击【关闭任务】按钮
if($task->status < Task::STATUS_FINISHED && in_array(Yii::app()->user->name, Yii::app()->params['admins'])) :
	echo CHtml::form(array('task/closed'), 'POST', array('onsubmit'=>'return confirm("确定？")'));
	echo CHtml::hiddenField('taskId', $task->id);
	echo CHtml::submitButton('关闭任务');
	echo CHtml::endForm();
endif;
?>
</p>
<?php
if(in_array(Yii::app()->user->name, Yii::app()->params['admins'])):
?>
<hr />

<h3>任务打分记录：</h3>
<p>

<?php

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$gradeData,
    'columns'=>array('credit', 'description'),
));

?>
</p>
<?php
endif;
?>
<hr />

<h3>任务状态记录：</h3>
<p>

<?php
//$this->widget('zii.widgets.CListView', array(
//	'dataProvider'=>$taskLog,
//	'itemView'=>'_tasklog_item',
//));

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider'=>$taskLog,
    'columns'=>array(
        array(            // display 'create_time' using an expression
            'name'=>'时间',
            'value'=>'date("Y-m-d H:i:s", $data->create_time)',
        ),
        array(
        	'name'=>'事件',
        	'value'=>'$data->info',
        )
    )
));

?>
</p>

<?php
// 判断并赋值给另一个变量是为了避免后面再次使用$task->previousId,因为每次直接使用$task->previousId实际上都是调用了 $task->getPreviousId()方法，会执行多余的SQL查询。
if(($prevId = $task->previousId) != false) { echo CHtml::link('上一篇', array('/task/view', 'id'=>$prevId)); }
if(($nextId = $task->nextId) != false) { echo CHtml::link('下一篇', array('/task/view', 'id'=>$nextId), array('style'=>'float:right')); }
/*
$url = preg_replace("/(id(?:=|\/))\d+(.*?)/", '$1[id]$2', Yii::app()->request->getQueryString());
if (!empty($neighborId['beforeId'])) {
	$beforeUrl = str_replace('[id]', $neighborId['beforeId'], $url);
	echo '<a href="index.php?'.$beforeUrl.'">上一篇</a>';
}

if (!empty($neighborId['nextId'])) {
	$nextUrl = str_replace('[id]', $neighborId['nextId'], $url);
	echo '<a href="index.php?'.$nextUrl.'" style="float:right">下一篇</a>';
}
*/
?>
</div><!-- form -->
