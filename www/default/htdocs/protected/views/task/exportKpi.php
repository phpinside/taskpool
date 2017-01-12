<?php
$this->pageTitle=Yii::app()->name . ' - '.$title;
$this->breadcrumbs=array(
	'任务'=>array('task/index'), $title
);

$monthList = array(''=>'请选择月份');
for( $i=1; $i<=12; $i++ ){
	$monthList[$i]=$i;
}
?>

<h1><?php echo $title;?></h1>

<?php if(Yii::app()->user->hasFlash('task')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('task'); ?>
</div>

<?php endif; ?>


<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'KpiForm',
	'enableAjaxValidation'=>true,

)); ?>

	<p class="note">带有 <span class="required">*</span> 标记的为必填项</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'year'); ?>
		<?php echo $form->dropDownList($model,'year',array(
			''=>'请选择年份',
			'2012'=>'2012',
			'2013'=>'2013',
			'2014'=>'2014',
			)); ?>
		<?php echo $form->error($model,'year'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'month'); ?>
		<?php echo $form->dropDownList($model,'month', $monthList); ?>
		<?php echo $form->error($model,'month'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'startdate'); ?>
		<?php
		$this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'model'=>$model,
			'attribute'=>'startdate',
			'options' => array(
				'dateFormat'=>'yy-mm-dd', //database save format
				//'altFormat'=>'mm-dd-yy' //display format
				//'showAnim'=>'fold',
				//'yearRange'=>'-3:+3' 
			),
			'htmlOptions'=>array(
            'readonly'=>'readonly',
            'style'=>'width:90px;',
			)
		));?>
		<?php echo $form->error($model,'startdate'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'enddate'); ?>
		<?php
		$this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'model'=>$model,
			'attribute'=>'enddate',
			'options' => array(
				'dateFormat'=>'yy-mm-dd', //database save format
				//'altFormat'=>'mm-dd-yy' //display format
				//'showAnim'=>'fold',
				//'yearRange'=>'-3:+3' 
			),
			'htmlOptions'=>array(
            'readonly'=>'readonly',
            'style'=>'width:90px;',
			)
		));?>
		<?php echo $form->error($model,'enddate'); ?>
	</div>

	
	<div class="row">
		<?php echo $form->labelEx($model,'recvs'); ?>
		<label><input type="checkbox" onclick="var hd=this;$('input[name=\'KpiForm[recvs][]\']').attr('checked', hd.checked); " /> [==全选==]</label>
		<?php echo  CHtml::activeCheckBoxList($model, 'recvs', $allUsersList); ?>
		<?php echo $form->error($model,'recvs'); ?>
		<label><input type="checkbox" onclick="var hd=this;$('input[name=\'KpiForm[recvs][]\']').attr('checked', hd.checked); " /> [==全选==]</label>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('导出KPI表', array('style'=>'font-size: 32px;')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
<script>
(function(){

var Y,m, date = new Date();
m = date.getMonth();
Y = date.getFullYear();

$("#KpiForm_year").val( Y );
$("#KpiForm_month").val( m );
$(':checkbox').attr('checked', true);

var loading = false;
$("#KpiForm :submit").click(function(){
	if (loading) return false;
	var o = $(this);
	o.css('color', '#999');
	loading = true;
	setTimeout(function(){
		loading = false;
		o.css('color', '#000');
	}, 7000);
});

})()
</script>
