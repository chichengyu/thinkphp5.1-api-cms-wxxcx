<?php 
namespace app\api\validate;
use think\Validate;
use app\lib\exception\ParamsException;
use think\facade\Request;

class BaseValidate extends Validate
{

	public function goCheck()
	{
		// 获取http传入的所有参数
		$request = Request::instance();
		$params = $request->param();

		// 进行验证参数
		$result = $this->batch()->check($params);
		if ($result) {
			return true;
		} else {
			throw new ParamsException(array(
				// $this->error有一个问题，并不是一定返回数组，需要判断
				'msg' => is_array($this->error)?implode(',',$this->error):$this->error
			));
		}
	}
	// 验证是否正整数
	protected function isIdInteger($value,$rule='',$data='',$field='')
	{
		if (is_int($value+0) && ($value+0) > 0) {
			return true;
		} else {
			return false;
		}
	}
	// 验证是否为空
	protected function isNotEmpty($value,$rule='',$data='',$field='')
	{
		if (empty($value)) {
			return false;
		}
		return true;
	}
	// 验证手机号
	public function isMobile($value)
	{
		// $reg = '/^1(3|4|5|7|8)[0-9]\d{8}$/';
		$reg = '^1(3|4|5|7|8)[0-9]\d{8}$^';
		$result = preg_match($reg,$value);
		if ($result) {
			return true;
		}
		return false;
	}

	// 对提交的数据进行安全验证并过滤
	public function getDataByRule($data)
	{
		// 我们利用token获取的uid
		// 不允许提交数据包含user_id 或 uid ,阻止恶意覆盖user_id外键
		if (array_key_exists('user_id',$data) || array_key_exists('uid', $data)) {
			throw new ParamsException(array(
				'msg' => '参数中存在非法的参数名user_id或uid'
			));
		}
		$newArray = array();
		// 遍历所有验证规定进行筛选数据
		foreach ($this->rule as $k => $v) {
			$newArray[$k] = $data[$k];
		}
		return $newArray;
	}
}