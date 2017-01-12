<?php
class Util {
	public static function chineseWeekday($timestamp) {
		$chineseWords = array('日','一','二','三','四','五','六');
		return '周'.$chineseWords[date('w', $timestamp)];
	}
	
	/**
	 * @desc
	 * 		得到上个星期的第几天0点时间戳,星期一为第一天
	 */
	public static function getLastMonday($n) {
		return date('Y-m-d', strtotime(self::getThisMonDay($n)) - 7 * 86400);
	}
	
	/**
	 * @desc
	 * 		得到这个星期的第几天0点时间戳,星期一为第一天 
	 */
	public static function getThisMonDay($n) {
		$time = time();
		$timeStamp = $time - $time % 86400 - (date('w', $time) - $n ) * 86400;
		return date('Y-m-d', $timeStamp);
	}
	
	/**
	 * 得到上月第一天时间
	 */
	public static function getUpMonthFirstDay() {
		$lastMonth = self::getLastMonth();
		$year = $lastMonth == 12 ? date('Y') - 1 : date('Y');
		return date('Y-m-d H:i:s', mktime(0, 0, 0, $lastMonth, 1, $year));
	}
	
	/**
	 * 得到上月最后一天时间
	 */
	public static function getUpMonthLastDay() {
		return date('Y-m-d H:i:s',strtotime(self::getThisMonthFirstDay()) - 1);//上月最后一天 即本月第一天0点 -1s
	} 
	
	/**
	 * 得到当月第一天时间
	 */
	public static function getThisMonthFirstDay() {
		return date('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), 1, date('Y')));
	}
	
	/**
	 * 得到当月最后一天时间
	 */
	public static function getThisMonthLastDay() {
		$nextMonth = self::getNextMonth();
		$year = $nextMonth == 1 ? date('Y') + 1 : date('Y');
		return date('Y-m-d H:i:s', mktime(0, 0, 0, $nextMonth, 1, $year)-1); //下个月的第一天0点-1s  即本月最后一天
	}
	
	
	
	/**
	 * 得到季度第一天时间
	 */
	public static function getUpQuarterFirstDay() {
		$season = ceil((date('n'))/3);//季度是第几季度
		return date('Y-m-d H:i:s', mktime(0, 0, 0, $season*3-3+1,1,date('Y')));
	}
	
	/**
	 * 得到季度最后一天时间
	 */
	public static function getUpQuarterLastDay() {
		$season = ceil((date('n'))/3);//季度是第几季度
		return date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 ,0,$season*3,1,date("Y"))),date('Y')));
	}
	
	/**
	 * 得到当前（前后）半年第一天时间
	 */
	public static function getUpHalfYearFirstDay() {
		$m = date('m')>6 ? 7 : 1;
		return date('Y-m-d H:i:s', mktime(0, 0, 0, $m , 1, date('Y')));
	}
	
	/**
	 * 得到当前半年最后一天时间
	 */
	public static function getUpHalfYearLastDay() {
		$m = date('m')>6 ? 12 : 6;
		return date('Y-m-d H:i:s', mktime(23, 59, 59, $m , 30, date('Y')));
	}
	
	/**
	 * 得到当年第一天时间
	 */
	public static function getUpYearFirstDay() {
		return date('Y-m-d H:i:s', mktime(0, 0, 0, 1 , 1, date('Y')));
	}
	
	/**
	 * 得到当年最后一天时间
	 */
	public static function getUpYearLastDay() {
		return date('Y-m-d H:i:s', mktime(23, 59, 59, 12 , 31, date('Y')));
	}
	
	/**
	 * 得到当日时间
	 */
	public static function getToDay() {
		return date('Y-m-d H:i:s', time());
	}
	
	private static function getLastMonth(){
		return date('m')-1 < 1 ? 12 : date('m') - 1;
	}
	
	private static function getNextMonth() {
		return date('m')+1 > 12 ? 1 : date('m') + 1;
	}
}