<?php
$this->pageTitle=Yii::app()->name . ' - '.$subTitle;
$this->breadcrumbs=array(
	'任务'=>array('task/index'), $subTitle
);

?>

<h1><?php echo $subTitle;?></h1>

<?php if(Yii::app()->user->hasFlash('task')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('task'); ?>
</div>

<?php endif; ?>


<div class="form">

<form action="<?php echo Yii::app()->createUrl('task/open');?>" method="POST">
<?php 
	$this->widget(
		'application.components.widgets.TaskStatus',  array(
				'dataProvider'=>$dataProvider,
		)); 
?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
	'columns'=>array(
		array('class'=>'CheckBoxColumn', 'id'=>'taskIds', 'disabled'=>'$data->status!=Task::STATUS_ADDED'), //多选
		'id', 
		array('class'=>'CLinkColumn', 'header'=>'任务标题', 'labelExpression'=>'$data->subject', 'urlExpression'=>'$data->getUrl()', 'cssClassExpression'=>'"task-s-{$data->status}"'),
//		array('name'=>'subject', 'cssClassExpression'=>'"task-s-{$data->status}"'),
		'man_hour','credit', 'difficulty',
		array('name'=>'user_id','value'=>'$data->user->realname'), 
		array('name'=>'dateline', 'value'=>'date("Y-m-d", $data->dateline)'),
		array('class'=>'CButtonColumn',)
	),
	'selectableRows'=>2,
	'enableSorting'=>true,
	'ajaxUpdate'=>false,
	'pager'=>'application.components.widgets.Pager',
)); ?>
<button type="submit">选中任务开放申请</button>
</form>
</div><!-- form -->
