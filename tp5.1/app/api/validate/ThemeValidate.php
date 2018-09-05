<?php 
namespace app\api\validate;

class ThemeValidate extends BaseValidate
{
	protected $rule = array(
		'ids' => 'checkIds'
	);
	protected $message = array(
		'ids' => 'ids必须是以逗号隔开的多个正整数'
	);

	protected function checkIds($value)
	{
		$values = explode(',',$value);
		if (empty($values)) {
			return false;
		}
		foreach ($values as $k => $v) {
			if ($this->isIdInteger($v)) {
				return true;
			} else {
				return false;
			}
		}
		return true;
	}
}