<?php
$this->pageTitle=Yii::app()->name . ' - 打分';
$this->breadcrumbs=array(
	'任务'=>array('task/index'), 
	$task->subject=>$task->url,
	'打分'
);

?>

<h1><?php echo '请打分：'.$task->subject;?></h1>

<?php if(Yii::app()->user->hasFlash('task')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('task'); ?>
</div>

<?php endif; ?>


<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'task-credit',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note">带有 <span class="required">*</span> 标记的为必填项</p>
	<div class="row">
		<?php echo $form->labelEx($model,'credit'); ?>
		<?php echo $form->textField($model,'credit',array('size'=>60,'maxlength'=>4)); ?>
		<?php echo $form->error($model,'credit'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textArea($model,'description',array('rows'=>10, 'cols'=>70)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>
	
	<div class="row buttons">
		<?php echo $form->hiddenField($model,'task_id'); ?>
		<?php echo $form->error($model,'task_id'); ?>
		<?php echo CHtml::submitButton('提交'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
