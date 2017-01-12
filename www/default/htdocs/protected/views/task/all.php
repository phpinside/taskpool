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
<?php
$columns = array(
		'id',
		array('class'=>'CLinkColumn', 'header'=>'任务标题', 'labelExpression'=>'$data->subject', 'urlExpression'=>'$data->getUrl()', 'cssClassExpression'=>'"task-s-{$data->status}"'),
		array('name'=>'winner', 'header'=>'获胜者', 'value'=>'isset($data->winner->realname)? $data->winner->realname : "--"', 'cssClassExpression'=>'$data->winner_id == Yii::app()->user->id ? "task_winner" : ""'),
		'man_hour', 'credit', 'difficulty',
		array('name'=>'user_id', 'value'=>'$data->user->realname'),
		array('name'=>'dateline', 'value'=>'date("m-d", $data->dateline).", ".Util::chineseWeekday($data->dateline)'),
		
	);
if(in_array(Yii::app()->user->name, array_merge(Yii::app()->params['admins'], Yii::app()->params['team_leader']))) {
		$show_update = array('class'=>'CLinkColumn', 'header'=>'操作', 'labelExpression'=>'"更新"','urlExpression'=>'$data->getUrl(false,"task/update")');
		//$show_update = array('class'=>'CButtonColumn');
		array_push($columns, $show_update);
}
$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
	'columns'=>$columns,
	'enableSorting'=>true,
	'summaryText'=>'所有任务，共{count}条',
	'summaryCssClass'=>'',
	'pager'=>'application.components.widgets.Pager',
)); ?>

</form>
</div><!-- form -->
