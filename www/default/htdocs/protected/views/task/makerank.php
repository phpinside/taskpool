<?php
$this->pageTitle=Yii::app()->name . ' - 生成排行榜';
$this->breadcrumbs=array(
	'任务'=>array('task/index'),
//	$task->subject,
);
?>

<h1> 生成排行榜</h1>

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
<?php $form=$this->beginWidget('CActiveForm'); ?>
 <label><input value="1" name="Rank[rankype]" id="Rank_rankype" checked="checked" type="radio"> 上周</label>
 <label><input value="2" name="Rank[rankype]" id="Rank_rankype"  type="radio"> 上个月</label>
 <label><input value="3" name="Rank[rankype]" id="Rank_rankype"  type="radio"> 前半年</label>
 <label><input value="4" name="Rank[rankype]" id="Rank_rankype"  type="radio"> 年度</label>


<?php echo '<br />'.CHtml::submitButton('开始生成', array('style'=>'font-size: 32px;'));
$this->endWidget();

 ?>
</div>
</p>
</div><!-- form -->
