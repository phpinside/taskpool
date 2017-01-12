<?php

/**
 * This is the model class for table "rank".
 *
 * The followings are the available columns in table 'rank':
 * @property integer $id
 * @property string $starttime
 * @property string $endtime
 * @property integer $rankype
 * @property string $rankcode
 */
class Rank extends CActiveRecord
{
	public $starttime = 0; //已废弃，为数据兼容性保留
	public $endtime = 0; //已废弃
	
	const RANKTYPE_WEEK 	= 1;  	//按周排行榜
	const RANKTYPE_MONTH 	= 2; 	//月
	const RANKTYPE_HALFYEAR = 3; 	//半年
	const RANKTYPE_YEAR 	= 4;    //年
	const RANKTYPE_QUARTER 	= 5;  	//季度
	const RANKTYPE_THIS_MONTH = 6;  //当月
	
	public function getRankType() {
		return array(
					self::RANKTYPE_WEEK,
					self::RANKTYPE_MONTH,
					self::RANKTYPE_HALFYEAR,
					self::RANKTYPE_YEAR,
					self::RANKTYPE_QUARTER,
					self::RANKTYPE_THIS_MONTH
				);
	}
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Rank the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'rank';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			//array('rankcode', 'required', 'on'=>'save'),
			array('rankype', 'numerical', 'integerOnly'=>true),
			//array('starttime, endtime', 'match', 'pattern'=>'/^[\d]{4}-[\d]{1,2}-[\d]{1,2}$/'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, starttime, endtime, rankype, rankcode', 'safe', 'on'=>'search'),
		);
	}

	
	
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'starttime' => '开始时间',
			'endtime' => '结束时间',
			'rankype' => 'Rankype',
			'rankcode' => 'Rankcode',
		);
	}

	/**
	 * 获取上周、上月、上季度、上半年等的排行版
	 */
	public function getPreTop($ranktype=1 ,$limit=5)
	{
		$rankitem = Yii::app()->db->createCommand(
			'SELECT *  FROM `rank` 
			WHERE rankype=:ranktype
			ORDER BY id DESC LIMIT 0,1'
		)->queryRow(true, array(':ranktype'=>$ranktype));
	 //  $scores=unserialize( base64_decode($rankitem['rankcode']) );
	 	$ranks = $rankitem['rankcode'];
	 	$scores=array();
	 	@eval("\$scores = $ranks;");
		$scores= array_slice($scores,0,$limit);
	  	 return $scores;
	}
	
	/**
	 * 根据时间段生成排行榜单
	 */
	public function makeRank() {
	
		if(!in_array($this->rankype, 
			array(self::RANKTYPE_WEEK, self::RANKTYPE_MONTH, self::RANKTYPE_HALFYEAR, self::RANKTYPE_YEAR))
		) {
			return false;
		}
		
		$rank = $this->getStatisticsData($this->rankype, Task::STATUS_AUDITED);
		$result = array();
		foreach($rank as $key=>$user) {
			if(!empty($user['sumCredit'])) {
				$result[$key]=$user;
				$result[$key]['winner_id'] = $result[$key]['id'];
				unset($result[$key]['id']);
			}
		}
		
		
		$this->rankcode = var_export($result,true);
//		print_r($this->rankcode);
		$this->rankype;
		$this->starttime = 0;
		$this->endtime = 0;
				
		return $this->save(FALSE);
	
	}

	/*
	 * get rank name
	 */
	public static function getRankName($type = 1){
		$names = array( 
					self::RANKTYPE_WEEK => '上周', 
					self::RANKTYPE_MONTH => '上月', 
					self::RANKTYPE_HALFYEAR => '前半年', 
					self::RANKTYPE_YEAR => '年度',
					self::RANKTYPE_QUARTER => '季度'
				);
		return $names[$type];
	}
	
	/**
	 *  实时取得用户统计积分数据，上周,上月，上季度，上半年，全年
	 * 	默认为为上周完成任务积分
	 * 
	 * @param int $ranktype
	 * @param int $states
	 */
	public function getStatisticsData($ranktype = 1, $states = Task::STATUS_AUDITED) {
		$ranktype = intval($ranktype) > 0 ? intval($ranktype) : 1;
		$begTime = Util::getLastMonday(1);
		$endTime = date('Y-m-d H:i:s', strtotime(Util::getThisMonday(1))-1); //本周一0点-1s即上周末
		switch($ranktype) {
			case self::RANKTYPE_THIS_MONTH:
				$begTime = Util::getThisMonthFirstDay();
				$endTime = Util::getThisMonthLastDay();
				break;
			case self::RANKTYPE_MONTH :					//上月
				$begTime = Util::getUpMonthFirstDay();
				$endTime = Util::getUpMonthLastDay();
				break;
			case self::RANKTYPE_HALFYEAR :				//半年
				$begTime = Util::getUpHalfYearFirstDay();
				$endTime = Util::getUpHalfYearLastDay();
				break;
			case self::RANKTYPE_YEAR :					//全年
				$begTime = Util::getUpYearFirstDay();
				$endTime = Util::getUpYearLastDay();
				break;
			case self::RANKTYPE_QUARTER: 				//季度
				$begTime = Util::getUpQuarterFirstDay();
				$endTime = Util::getUpQuarterLastDay();
				break;
		}
		
		return Task::model()->getUserCredit(0, strtotime($begTime), strtotime($endTime), $states);
	}
}