<?php 
class MedalTable extends CWidget {	
	public $members;
	public $colors = array('gold', 'silver', 'silver', 'coral', 'coral', 'slategray', 'slategray');
	/**
	 * 执行 widget，显示标签云
	 */
	public function run() {
		if(empty($this->members)) {
			echo '<span>暂无数据</span>';
			return;
		}
		
		$dataCount = count($this->members); //5
		$maxHeight=100;
		$levelHeight = floor($maxHeight/$dataCount); //20
		
		$displayOrder = array();
		$topIndex = max(0, (ceil($dataCount/2)-1)); //2
		
		$members = $this->members;
		
		foreach ($members as $key=>$m) {
			$members[$key]['level']=1+$key;
			$members[$key]['color']=$this->colors[$key];
			$members[$key]['height']=$maxHeight - ($key*$levelHeight);
		}
		
//		print_r($members);
		for($i=0; $i<=$topIndex+1; $i++) {
			if($topIndex-$i >=0) {
				$leftMember = array_shift($members);
				
				$displayOrder[$topIndex-$i] = $leftMember;
			}
			if($i != 0) {
				$rightMember = array_shift($members);
				if(!$rightMember) {
					break;
				}
				$displayOrder[$topIndex+$i] = $rightMember;
			}	
		}
		
		echo '<table style="text-align:center; width:auto; border-bottom: 1px solid #CCC "><tr>';
		for($i=0; $i<$dataCount; $i++) {
			echo '<td style=" vertical-align:bottom; text-align: center;">
				<span style="font-size: 20px; line-height: 30px; font-weight:bold;">'
				.CHtml::link($displayOrder[$i]['realname'], array('user/space', 'id'=>$displayOrder[$i]['winner_id']), array('target'=>'_blank'))
				.'</span> 
				<div style="background: '.$displayOrder[$i]['color'].'; width: 130px; text-align: center; padding-top: 10px; height:'.(25+$displayOrder[$i]['height']).'px; font-size:'.(20+$displayOrder[$i]['height']).'px;">'.$displayOrder[$i]['level'].'</div>
				</td>';
			
		}
		echo '</tr></table>';
	}
}


///<em>('.$displayOrder[$i]['score'].')</em> 