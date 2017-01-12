<?php
/**
 * Pager class file.
 *
 * @author Riverlet <mengfanbin@hudong.com>
 */

/**
 * Pager 用于输出符和百科网样式的分页HTML
 *
 * @author Riverlet <mengfanbin@hudong.com>
 * @package application.components.widgets
 * @since 1.0
 */
class Pager extends CBasePager {

	/**
	 * @var integer 最大分页按钮数量（中间部分），默认 8
	 */
	public $maxButtonCount = 8;
	/**
	 * @var string 下一页连接文字，默认为“下一页”
	 */
	public $nextPageLabel;
	/**
	 * @var string 上一页连接文字，默认为“上一页”
	 */
	public $prevPageLabel;

	/**
	 * @var mixed the CSS file used for the widget. Defaults to null, meaning
	 * using the default CSS file included together with the widget.
	 * If false, no CSS file will be used. Otherwise, the specified CSS file
	 * will be included when using this widget.
	 */
	public $cssFile;
	/**
	 * @var array HTML attributes for the pager container tag.
	 */
	public $htmlOptions=array();
	
	/**
	 * @var boolean 是否显示汇总信息
	 */
	public $showSummary = false;
	
	/**
	 * @var int 当前页按钮距按钮序列左侧偏移数
	 */
	public $currentButtonOffset = 3;
	
	/**
	 * @var boolean 是否显示上一页下一页
	 */
	public $showPrevNext = true;
	
	

	/**
	 * 设置默认参数，初始化相关属性。
	 */
	public function init() {
		if($this->showPrevNext) {
			if($this->nextPageLabel===null) {
				$this->nextPageLabel='下一页';
			}
			if($this->prevPageLabel===null) {
				$this->prevPageLabel='上一页';
			}
		}
		

		if(!isset($this->htmlOptions['id'])) {
			$this->htmlOptions['id']=$this->getId();
		}
		if(!isset($this->htmlOptions['class'])) {
			$this->htmlOptions['class']='pagenum'; //默认CSS样式名为 pagenum
		}
	}

	/**
	 * Executes the widget.
	 * This overrides the parent implementation by displaying the generated page buttons.
	 */
	public function run() {
		if($this->getItemCount() <= $this->getPageSize()) {
			return;
		}
		
		$this->registerClientScript();
		$buttons = $this->createPageButtons();
		echo CHtml::tag('div', $this->htmlOptions, implode('',$buttons));
		
		if ($this->showSummary){
			echo "<div style=\"text-align:left;\">每页<b>{$this->pageSize}</b>条,共<b>{$this->pageCount}</b>页,总共<b>{$this->itemCount}</b>条记录</div>";
		}
	}
	
	
	protected function createPageButtons() {
		$pageCount = $this->getPageCount();
		$currentPage = $this->getCurrentPage(false);
		list($beginPage, $endPage) = $this->getPageRange();
		$buttons = array();
		
		if($currentPage > 0 && $this->showPrevNext) {
			$buttons[] = $this->createPageButton($this->prevPageLabel, $currentPage-1, false, array('class'=>'btnnum btnnum_no'));
		}
		
		if($currentPage - $this->currentButtonOffset > 0 && $pageCount > $this->maxButtonCount) {
			$buttons[] = $this->createPageButton('1', 0, false);
		}
		
		if($beginPage > 2) {
			$buttons[] = '...';
		}
		
		for($i=$beginPage-1; $i<$endPage; $i++) {
			$buttons[] = $this->createPageButton($i+1, $i, $i == $currentPage);
		}

		if($endPage < $pageCount - 1) {
			$buttons[] = '...';
		}
		
		if($endPage < $pageCount) {
			$buttons[] = $this->createPageButton($pageCount, $pageCount - 1, false);
		}
		
		if($currentPage < $pageCount - 1 && $this->showPrevNext) {
			$buttons[] = $this->createPageButton($this->nextPageLabel, $currentPage+1, false, array('class'=>'btnnum btnnum_no'));
		}
		
		return $buttons;
	}
	
	

	protected function createPageButton($label, $page, $selected, $htmlOptions=array())	{
		if($selected) {
			return CHtml::tag('em', $htmlOptions, $label);
		} else {
			return CHtml::link($label, $this->createPageUrl($page), $htmlOptions);
		}
	}	
	
	/**
	 * @return array the begin and end pages that need to be displayed.
	 */
	protected function getPageRange() {
		$currentPage=$this->getCurrentPage();
		$pageCount=$this->getPageCount();

		if($this->maxButtonCount > $pageCount) {
			$beginPage = 1;
			$endPage = $pageCount;
		} else {
			$beginPage = $currentPage + 1 - $this->currentButtonOffset;
			$endPage = $beginPage + $this->maxButtonCount - 1;
			if($beginPage < 1) {
				$endPage = $currentPage + 2 - $beginPage;
				$beginPage = 1;
				if($endPage - $beginPage < $this->maxButtonCount) {
					$endPage = $this->maxButtonCount;
				}
			} elseif($endPage > $pageCount) {
				$beginPage = $pageCount - $this->maxButtonCount + 1;
				$endPage = $pageCount;
			}
		}
		
		return array($beginPage,$endPage);
	}
	

	/**
	 * Registers the needed client scripts (mainly CSS file).
	 */
	public function registerClientScript()	{
		if($this->cssFile!==false)
			self::registerCssFile($this->cssFile);
	}

	/**
	 * Registers the needed CSS file.
	 * @param string $url the CSS URL. If null, a default CSS URL will be used.
	 */
	public static function registerCssFile($url=null)
	{
		if($url===null)
			$url=CHtml::asset(dirname(__FILE__).DIRECTORY_SEPARATOR.'pager.css');
		Yii::app()->getClientScript()->registerCssFile($url);
	}
}
