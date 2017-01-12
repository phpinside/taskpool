<?php $this->beginContent('//layouts/main'); ?>
<div class="span-19">
	<div id="content">
		<?php echo $content; ?>
	</div><!-- content -->
</div>
<div class="span-5 last">
	<div id="sidebar">
	<?php
		$this->beginWidget('zii.widgets.CPortlet', array(
			'title'=>'操作',
		));
		$this->widget('zii.widgets.CMenu', array(
			'items'=>array(
				array('label'=>'添加任务', 'url'=>array('task/add'), 'visible'=>in_array(Yii::app()->user->name, array_merge(Yii::app()->params['admins'], Yii::app()->params['team_leader']))),
				array('label'=>'管理任务', 'url'=>array('task/manage'), 'visible'=>in_array(Yii::app()->user->name, Yii::app()->params['admins'])),
				array('label'=>'我的任务', 'url'=>array('task/my')),
				array('label'=>'我发布的任务', 'url'=>array('task/myPublic'), 'visible'=>in_array(Yii::app()->user->name, array_merge(Yii::app()->params['admins'], Yii::app()->params['team_leader']))),
				array('label'=>'所有任务', 'url'=>array('task/all')),
				array('label'=>'无人选的任务', 'url'=>array('task/noApplications'), 'visible'=>in_array(Yii::app()->user->name, Yii::app()->params['admins'])),
				array('label'=>'生成榜单', 'url'=>array('task/rank'), 'visible'=>in_array(Yii::app()->user->name, Yii::app()->params['admins'])),
				array('label'=>'导出任务', 'url'=>array('task/export'), 'visible'=>in_array(Yii::app()->user->name, Yii::app()->params['admins'])),
				array('label'=>'权限', 'url'=>array('site/permit'), 'visible'=>in_array(Yii::app()->user->name, Yii::app()->params['admins'])),
				array('label'=>'导出KPI表', 'url'=>array('task/exportKpi'), 'visible'=>in_array(Yii::app()->user->name, Yii::app()->params['admins'])),
			),

			'htmlOptions'=>array('class'=>'operations'),
		));
		$this->endWidget();
	?>
	</div><!-- sidebar -->
</div>
<?php $this->endContent(); ?>