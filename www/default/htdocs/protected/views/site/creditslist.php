
<h1><?php echo $subTitle;?></h1>
<?php

$columns = array(
	array('class'=>'CLinkColumn', 'header'=>'姓名', 'labelExpression'=>'$data["realname"]', 'urlExpression'=>'Yii::app()->createUrl(\'user/space\', array(\'id\'=>$data[\'user_id\']))'),
	array('name' => 'week_recredit', 'header' => '本周预计总得分', 'value'=>'intval($data["week_recredit"])'),
	array('name' => 'man_hour', 'header' => '承担的工时', 'value'=>'intval($data["man_hour"])'),
	array('name' => 'week_ready', 'header' => '本周已得分', 'value'=>'intval($data["week_ready"])'),
	array('name' => 'one', 'header' => '上周得分', 'value'=>'intval($data["one"])'),
	array('name' => 'six', 'header' => '本月已得分', 'value'=>'intval($data["six"])'),
	array('name' => 'two', 'header' => '上月得分', 'value'=>'intval($data["two"])'),
	array('name' => 'five', 'header' => '当前季度得分', 'value'=>'intval($data["five"])'),
	array('name' => 'three', 'header' => '当前半年得分', 'value'=>'intval($data["three"])'),
	array('name' => 'four', 'header' => '当前年度得分', 'value'=>'intval($data["four"])'),
	array('name' => 'total', 'header' => '累计得分'),
);

$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider' =>$dataProvider,
	'columns' => $columns,
	'enableSorting' => true,
	'summaryText' => $subTitle .'，共{count}条',
	'summaryCssClass' => '',
	'pager' => 'application.components.widgets.Pager',
)); ?>