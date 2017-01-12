<?php
class StringWidthValidator extends CValidator
{
	/**
	 * @var integer 最大宽度，默认为 NULL，即不限最大宽度。
	 */
	public $max;
	/**
	 * @var integer 最小宽度，默认为NULL，即不限最小宽度。
	 */
	public $min;
	/**
	 * @var integer 固定宽度，默认为NULL，即不限固定宽度。
	 */
	public $is;
	/**
	 * @var string 宽度太小时给出的提示信息。
	 */
	public $tooShort;
	/**
	 * @var string 宽度过大时给出的提示信息.
	 */
	public $tooLong;
	/**
	 * @var boolean 是否可以为 null 或空。默认为 true。即:如果验证的属性为空，则被认为合法。
	 */
	public $allowEmpty=true;
	/**
	 * @var string 被验证字符串的编码，默认为 'UTF-8'。
	 */
	public $encoding='UTF-8';

	/**
	 * 验证一个对象的属性
	 * 如果产生错误，则错误会被加入到被验证的对象中。
	 * @param CModel $object 被验证的对象
	 * @param string $attribute 被验证的属性
	 */
	protected function validateAttribute($object,$attribute) {
		$value = $object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value)) {
			return;
		}
		if($this->encoding === false) {
			$width = strlen($value);
		} else {
			$width = mb_strwidth($value, $this->encoding);
		}
		if($this->min !== null && $width < $this->min) {
			$message = $this->tooShort !== null ? $this->tooShort : Yii::t('yii','{attribute} is too short (minimum is {min} characters).');
			$this->addError($object, $attribute, $message, array('{min}' => $this->min));
		}
		if($this->max !== null && $width > $this->max) {
			$message = $this->tooLong !== null ? $this->tooLong : Yii::t('yii','{attribute} is too long (maximum is {max} characters).');
			$this->addError($object, $attribute, $message, array('{max}' => $this->max));
		}
		if($this->is !== null && $width !== $this->is) {
			$message = $this->message !== null ? $this->message : Yii::t('yii','{attribute} is of the wrong length (should be {length} characters).');
			$this->addError($object, $attribute, $message, array('{length}' => $this->is));
		}
	}
}

