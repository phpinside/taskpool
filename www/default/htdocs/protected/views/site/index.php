<h2>上周积分排行
<?php
	if(in_array(Yii::app()->user->name, Yii::app()->params['admins'])){
		echo CHtml::link('更多', array('site/creditList'));
	}
?>
</h2>
 
<?php 
	$this->widget('application.components.widgets.MedalTable', array('members'=>$preWeekTops));
?>

<br /><br /><br />


<h2>上月积分排行
<?php 
	if(in_array(Yii::app()->user->name, Yii::app()->params['admins'])){
		echo CHtml::link('更多', array('site/creditList'));
	}
?>
</h2>

<?php 
	$this->widget('application.components.widgets.MedalTable', array('members'=>$preMonthTops));
?>
<br /><br /><br />


<h2>前半年积分排行
<?php 
	if(in_array(Yii::app()->user->name, Yii::app()->params['admins'])){
		echo CHtml::link('更多', array('site/creditList'));
	}
?>

</h2>
<?php 
	$this->widget('application.components.widgets.MedalTable', array('members'=>$preHalfyearTops));
?>


<br /><br /><br />
<h2>年度积分排行
<?php 
	if(in_array(Yii::app()->user->name, Yii::app()->params['admins'])){
		echo CHtml::link('更多', array('site/creditList'));
	}
?>
</h2>
<?php 
	$this->widget('application.components.widgets.MedalTable', array('members'=>$preYearTops));
?>
