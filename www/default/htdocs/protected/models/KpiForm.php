<?php

class KpiForm extends CFormModel
{
	public $year;
	public $month;
	public $recvs;
	public $startdate='';
	public $enddate='';

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('year, month, recvs', 'required'),
		);
	}
	
	public function attributeLabels()
	{
		return array(
			'year'=>'年',
			'month'=>'月',
			'recvs'=>'任务接收者',
			'startdate'=>'开始日期',
			'enddate'=>'结束日期',
		);
	}
}