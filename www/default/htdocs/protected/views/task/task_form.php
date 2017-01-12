<?php
$this->pageTitle=Yii::app()->name . ' - '.$title;
$this->breadcrumbs=array(
	'任务'=>array('task/index'), $title
);

?>

<h1><?php echo $title;?></h1>

<?php if(Yii::app()->user->hasFlash('task')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('task'); ?>
</div>

<?php endif; ?>


<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'task-form',
	'enableAjaxValidation'=>true,

)); ?>

	<p class="note">带有 <span class="required">*</span> 标记的为必填项</p>

	<?php echo $form->errorSummary($model); ?>

	 <div class="row">	
		<?php echo $form->labelEx($model,'subject'); ?>
		<?php 
			if(!empty($model->attributes['subject'])) {
				 echo $form->textField($model,'subject',array('size'=>90)); 
			}else {
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'id'=>'Task_subject',
					'name'=>'Task[subject]',
					'source'=>$this->createUrl('task/suggests'),
					'options'=>array(
					 'select'=>"js:function(event, ui) {
						var receivers = ui.item.receivers;
						$('#Task_description').val(ui.item.description);
						$('#Task_gain').val(ui.item.gain);
						$('#Task_man_hour').val(ui.item.man_hour);
						$('#Task_dateline').val(ui.item.dateline);
						$('#Task_difficulty').val(ui.item.difficulty);
						for(var i=0;i<receivers.length;i++) {
							var str = 'Task_recvs_'+receivers[i];
							$('input[name=\'Task[recvs][]\']').each(function(){
								if($(this).val() == receivers[i]){
									$(this).attr('checked', true);
								}
							}); 
						}	
					}"),
				'htmlOptions'=>array('size'=>'90'),
				));				
			}
		?>		
		<?php echo $form->error($model,'subject'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'description'); ?>
		<?php echo $form->textArea($model,'description',array('rows'=>10, 'cols'=>70)); ?>
		<?php echo $form->error($model,'description'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'gain'); ?>
		<?php echo $form->textArea($model,'gain',array('rows'=>10, 'cols'=>70)); ?>
		<?php echo $form->error($model,'gain'); ?>
	</div>
		
	<div class="row">
		<?php echo $form->labelEx($model,'man_hour'); ?>
		<?php echo $form->textField($model,'man_hour',array('size'=>60,'maxlength'=>3)); ?>
		<?php echo $form->error($model,'man_hour'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'dateline'); ?>
		<?php 
		$this->widget('zii.widgets.jui.CJuiDatePicker',
			array(  
			'language'=>'zh_cn',  
			'model'=>$model,  
			'attribute'=>'dateline', 
			'options'=>array(  
			'showAnim'=>'fold',  
			'dateFormat'=>'yy-mm-dd',  
			) 
		)); 
		 ?>
		
	</div>
		
	<div class="row">
		<?php echo $form->labelEx($model,'difficulty'); ?>
		<?php echo $form->dropDownList($model,'difficulty',Task::getDifficulties()); ?>
		<?php echo $form->error($model,'difficulty'); ?>
	</div>
	
	<div class="row">
		<?php echo $form->labelEx($model,'recvs'); ?>
		<label><input type="checkbox" onclick="var hd=this;$('input[name=\'Task[recvs][]\']').each(function(){this.checked=hd.checked;}); " /> [-==全选==-]</label>
		<?php echo  CHtml::activeCheckBoxList($model,'recvs',$receivers); ?>
		<?php echo $form->error($model,'recvs'); ?>
	</div>


	<div class="row buttons">
		<?php echo CHtml::submitButton('提交'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->

<?php if($model->isNewRecord) { ?>
<script>
$(document).ready(function(){
	$('#Task_subject').val(' ').click(function(){
		$(this).trigger("keydown");
	});	
})();
</script>
<?php } ?>