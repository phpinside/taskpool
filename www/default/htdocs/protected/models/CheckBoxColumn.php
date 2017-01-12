<?php
class CheckBoxColumn extends CCheckBoxColumn
{
        public $disabled;
        protected function renderDataCellContent($row,$data)
        {
               if($this->value!==null)
					$value=$this->evaluateExpression($this->value,array('data'=>$data,'row'=>$row));
				else if($this->name!==null)
					$value=CHtml::value($data,$this->name);
				else
					$value=$this->grid->dataProvider->keys[$row];

				$checked = false;
				if($this->checked!==null)
					$checked=$this->evaluateExpression($this->checked,array('data'=>$data,'row'=>$row));
					
				//else
				//	$this->checkBoxHtmlOptions['disabled'] = ($data->status!=Task::STATUS_ADDED) ? true : false;
				
				$options=$this->checkBoxHtmlOptions;
				if($this->disabled!==null) {
					$readOnly = $this->evaluateExpression($this->disabled,array('data'=>$data,'row'=>$row));
					$options['disabled']=$readOnly;
					if ($readOnly && isset($options['class'])) {
						$options['class'] = trim(str_replace('select-on-check', '', $options['class']));
					}
				}

				$name=$options['name'];
				unset($options['name']);
				$options['value']=$value;
				$options['id']=$this->id.'_'.$row;

				echo CHtml::checkBox($name,$checked,$options);
        }
}
?>