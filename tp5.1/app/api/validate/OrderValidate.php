<?php 
namespace app\api\validate;
use app\lib\exception\ParamsException;

class OrderValidate extends BaseValidate
{
	// 验证提交订单的数据格式 
	// $products = [[商品id,商品数量],[商品id,商品数量],.....];
	// 如：$products = [['product_id'=>'商品id','count'=>商品数量],[...].....]
	protected $rule = array(
		'products' => 'require|checkProducts'
	);

	protected function checkProducts($values)
	{
		if (!is_array($values)) {
			throw new ParamsException(array('msg'=>'商品列表参数错误'));
		}
		if (empty($values)) {
			throw new ParamsException(array('msg'=>'商品列表不能为空'));
		}
		foreach ($values as $v) {
			$this->checkProduct($v);
		}
		return true;
	}

	// 验证子级单个商品
	protected function checkProduct($value){
		// new父级的验证对象是为了能调用父级的 isIdInteger 验证方法
		$validater = new BaseValidate(array(
			'product_id' => 'require|isIdInteger',
			'count' => 'require|isIdInteger'
		));
		$res = $validater->check($value);
		if (!$res) {
			throw new ParamsException(array('msg'=>'商品参数不正确'));
		}
	}
}