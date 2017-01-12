<li>
<?php echo $data['credit']?>&nbsp;&nbsp;
<?php echo CHtml::link($data->subject, $data->url);?>&nbsp;&nbsp;
<?php echo CHtml::link("抢", array('scramble/do', 'task_id'=>$data->id), array('class'=>'j_scramble'));?>
</li>
 