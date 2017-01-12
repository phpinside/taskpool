<h3><?php  echo $model->realname ?> 的基本信息</h3>

<?php 
if( $model->username != Yii::app()->user->name && !in_array(Yii::app()->user->name, Yii::app()->params['admins']) ) {  

 $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$userTasks,
	'columns'=>array(
		'id', 
		array('class'=>'CLinkColumn','header'=>'任务标题', 'labelExpression'=>'$data->subject', 'urlExpression'=>'$data->url',  'cssClassExpression'=>'"task-s-{$data->status}"'),
		'man_hour', 
		array('name'=>'create_time', 'value'=>'date("Y-m-d H:i", $data->create_time)'),
		array('name'=>'dateline', 'value'=>'date("m-d", $data->dateline).", ".Util::chineseWeekday($data->dateline)'),
	),
	'enableSorting'=>true,
	'summaryText'=>'抢到的任务，共{count}条，当前占用工时：'.$detail['currentManhour'].'小时',
	'summaryCssClass'=>'',
)); 

} else {
	?>
<p>
<?php $this->widget('zii.widgets.CDetailView', array(
'data'=>$model,
 'attributes'=>array(
   array(
        'label' => '上周积分',
    	'value'=>$detail['preWeekScore'],
    ),
   array(
        'label' => '上月积分',
      	'value'=>$detail['preMonthScore'],
    ),
   array(
        'label' => '总积分',
      	'value'=>$detail['allScore'],
    ),
   array(
        'label' => '总完成任务数',
    	'value'=>$detail['taskNum'],
    ), 
    array(
        'label' => '本周任务预计得分',
      	'value'=>$detail['weekScore'],
    ), 
    

),
)); ?>
</p>



<?php //$this->widget('application.components.widgets.TaskStatus'); ?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$userTasks,
	'columns'=>array(
		'id', 
		array('class'=>'CLinkColumn','header'=>'任务标题', 'labelExpression'=>'$data->subject', 'urlExpression'=>'$data->url',  'cssClassExpression'=>'"task-s-{$data->status}"'),
		'man_hour', 'credit', 
		array('name'=>'create_time', 'value'=>'date("Y-m-d H:i", $data->create_time)'),
		array('name'=>'dateline', 'value'=>'date("m-d", $data->dateline).", ".Util::chineseWeekday($data->dateline)'),
	),
	'enableSorting'=>true,
	'summaryText'=>'抢到的任务，共{count}条，当前占用工时: '.$detail['currentManhour'].'小时',
	'summaryCssClass'=>'',
)); ?>



<?php
}
?>