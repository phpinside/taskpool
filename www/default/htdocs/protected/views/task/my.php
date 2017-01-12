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
<p>
<?php
	$this->widget(
		'application.components.widgets.TaskStatus',  array(
				'dataProvider'=>$historyTasks,
				'disableStatus' => $disableStatus,
	));
 ?>
</p>

<div class="form">
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$historyTasks,
	'columns'=>array(
		'id',
		array('class'=>'CLinkColumn', 'header'=>'任务标题', 'labelExpression'=>'$data->subject', 'urlExpression'=>'$data->getUrl()', 'cssClassExpression'=>'"task-s-{$data->status}"'),
		array('name'=>'winner', 'header'=>'获胜者', 'value'=>'isset($data->winner->realname)? $data->winner->realname : "--"', 'cssClassExpression'=>'$data->winner_id == Yii::app()->user->id ? "task_winner" : ""'),
		'man_hour',  /*'credit', */  
		array('name'=>'难度级别', 'value'=>'Task::getDifficulties(intval($data->difficulty))'), 
		array('name'=>'user_id', 'value'=>'isset($data->winner->realname)? $data->winner->realname : "--"'),
		array('name'=>'dateline', 'value'=>'date("m-d", $data->dateline).", ".Util::chineseWeekday($data->dateline)'),
	),
	'enableSorting'=>true,
	'summaryText'=>'我抢到的任务，共{count}条',
	'summaryCssClass'=>'',
	'pager'=>'application.components.widgets.Pager',
)); ?>

</div><!-- form -->
