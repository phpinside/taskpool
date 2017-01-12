<?php
$this->pageTitle=Yii::app()->name . ' - 生成排行榜';
$this->breadcrumbs=array(
	'任务'=>array('task/index'),
//	$task->subject,
);
?>

<h1> 生成周执行力表格</h1>

<?php if(Yii::app()->user->hasFlash('task')): ?>

<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('task'); ?>
</div>

<?php endif; ?>

<div class="form">
<hr />
<h4>选择时间段</h4>
<p>
<div class="Input" >
 <label>开始日期:</label>
 <?php

echo CHtml::form(array('task/export'));

 $this->widget('zii.widgets.jui.CJuiDatePicker',array(  
    'language'=>'zh_cn',  
    'name'=>'startdate',  
    'options'=>array(  
        'showAnim'=>'fold',  
        'dateFormat'=>'yy-mm-dd',  
    ),  
    'value'=>$starttime,
 
));  
 
 
 ?>
 
  <label>截止日期:</label>
 <?php

 $this->widget('zii.widgets.jui.CJuiDatePicker',array(  
    'language'=>'zh_cn',  
    'name'=>'enddate',  
    'options'=>array(  
        'showAnim'=>'fold',  
        'dateFormat'=>'yy-mm-dd',  
    ),  
	'value'=>$endtime,
 
));  
 


echo '<br />'.CHtml::submitButton('开始生成', array('style'=>'font-size: 32px;'));
echo CHtml::endForm();

 ?>
</div>
</p>
</div><!-- form -->
