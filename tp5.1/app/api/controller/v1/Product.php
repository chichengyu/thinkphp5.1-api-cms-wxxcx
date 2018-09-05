<?php 
namespace app\api\controller\v1;
use app\api\validate\ProductValidate;
use app\api\model\Product as ProductModel;
use app\lib\exception\ProductException;

class Product
{
	// 最新商品列表
	public function getRecent($count=10)
	{
		(new ProductValidate)->goCheck();
		$res = ProductModel::getMostRecent($count);
		if ($res->isEmpty()) {
			throw new ProductException(array(
				'msg' => 'Not Recent Product'
			));
		}
		return $res;
	}
	// 该分类下的所有商品
	public function getAllInCategory($cateId)
	{
		(new ProductValidate)->goCheck();
		$res = ProductModel::getProductCategory($cateId);
		if ($res->isEmpty()) {
			throw new ProductException;
		}
		return $res;
	}
	// 获取一件商品详情
	public function getProductOne($id)
	{
		(new ProductValidate)->goCheck();
		$res = ProductModel::with(['productImages'=>function($query){
					// 注意：此处对关联查询出的数据中的order字段进行排序
					$query->with('images')->order('order ASC');
				},'productPropertys'])
				->find($id);
		if (!$res) {
			throw new ProductException;
		}
		return $res;
	}
}