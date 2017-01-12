<?php

/**
 * Permit class.
 * PermitForm is the data structure for permit setting
 * permit form data. It is used by the 'site' action of 'PermitController'.
 */
class PermitForm extends CFormModel
{
	public $viewer = array();
	public $team_leader = array();
	
	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('viewer, team_leader', 'safe'),
		);
	}
	
	/**
	 * 属性设置值
	 */
	public function pushDefaultAttribute() {
		$this->push('viewer');
		$this->push('team_leader');
	}
	
	/**
	 * 具体属性设置值操作
	 * @param array $attribute
	 * @return 没有返回值
	 */	
	private function push($attribute) {
		if(!empty(Yii::app()->params[$attribute])) {
			$attribute_string = "'".implode('\',\'', Yii::app()->params[$attribute])."'";
			$attribute_ids = User::model()->findAllBySql("SELECT id FROM user WHERE username IN ($attribute_string)");
			foreach ($attribute_ids as $attribute_id) {
				$this->{$attribute}[$attribute_id->id] = $attribute_id->id;
			}
		}
	}

	/**
	 * 保存权限设置
	 */
	public function save() {
		global $config;
		$viewer = $team_leader = '';
		if(!empty($this->viewer)) {
			$viewer_ids = implode(',', $this->viewer);
			$command = Yii::app()->db->createCommand("SELECT group_concat('\'', username, '\'') AS ids FROM user WHERE id IN ($viewer_ids)");
			$viewer = $command->queryScalar();
		}
		if(!empty($this->team_leader)) {
			$team_leader_ids = implode(',', $this->team_leader);
			$command = Yii::app()->db->createCommand("SELECT group_concat('\'', username, '\'') AS ids FROM user WHERE id IN ($team_leader_ids)");
			$team_leader = $command->queryScalar();
		}
		$main_content = file_get_contents($config);
		$main_content = preg_replace("/team_leader(.*)\)/", "team_leader'=>array($team_leader)", $main_content);
		$main_content = preg_replace("/viewer(.*)\)/", "viewer'=>array($viewer)", $main_content);
		return file_put_contents($config, $main_content);
	}
}