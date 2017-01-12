<style type="text/css">
	div.form label {display: inline}
</style>
<?php
$this->pageTitle=Yii::app()->name . ' - '.$title;
$this->breadcrumbs=array(
	$title
);
?>
<?php if(Yii::app()->user->hasFlash('PermitForm')): ?>
<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('PermitForm'); ?>
</div>
<?php endif; ?>
<div class="form">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'permit-form',
	'enableAjaxValidation'=>true,

)); ?>
<div class="row">
	<label style="font-size: 16pt">观察者:</label><br/>
	<?php echo  CHtml::activeCheckBoxList($model,'viewer',User::getSimpleUsersList(), array('checkAll'=>'[-==全选==-]','separator'=>' ','class'=>'display:none')); ?>
	<?php echo $form->error($model,'viewer'); ?>
</div>
<div class="row">
	<label style="font-size: 16pt">team_leader:</label><br/>
	<?php echo  CHtml::activeCheckBoxList($model,'team_leader',User::getSimpleUsersList(), array('checkAll'=>'[-==全选==-]','separator'=>' ')); ?>
	<?php echo $form->error($model,'team_leader'); ?>
</div>
<div class="row buttons">
	<?php echo CHtml::submitButton('提交'); ?>
</div>
<?php $this->endWidget(); ?>
</div><!-- form -->
