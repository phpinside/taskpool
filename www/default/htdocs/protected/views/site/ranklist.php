
<h1><?php echo $subTitle;?></h1>
<?php

$columns = array(
	array('class'=>'CLinkColumn', 'header'=>'姓名', 'labelExpression'=>'$data["realname"]', 'urlExpression'=>'Yii::app()->createUrl(\'user/space\', array(\'id\'=>$data[\'winner_id\']))'),
	array('name' => 'score', 'header' => '积分'),
	array('name' => 'email', 'header' => '用户邮箱')
);

$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
	'columns'=>$columns,
	'enableSorting'=>true,
	'summaryText'=> $subTitle .'，共{count}条',
	'summaryCssClass'=>'',
	'pager'=>'application.components.widgets.Pager',
)); ?>