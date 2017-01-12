<?php
$this->pageTitle=Yii::app()->name . ' - '. $task->subject;
$this->breadcrumbs=array(
	'任务'=>array('task/index'),
	$task->subject,
);
?>

<h1>确认完成 [<?php echo $task->subject;?>]</h1>

<?php if(Yii::app()->user->hasFlash('task')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('task');?>
</div>

<?php endif; ?>


<div class="form">

	<?php $form=$this->beginWidget('CActiveForm', array(
			'action'=>CHtml::normalizeUrl(array('task/done')),
			'id'=>'task-form',
			'enableAjaxValidation'=>true,
			'focus'=>array($task,'remark'),
	)); ?>
		<?php	
			echo '对这个任务作为评价吧<b/>';
			echo $form->labelEx($task,'remark');
			echo $form->textArea($task, 'remark', array('rows'=>10, 'cols'=>70));
			echo $form->error($task,'remark');
			
			echo $form->hiddenField($task, 'id');
			echo "<p />";
			echo CHtml::submitButton('确定干完了!', array('style'=>'font-size: 32px;'));
		?>
	<?php $this->endWidget(); ?>
</div><!-- form -->
