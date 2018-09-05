<?php 
namespace app\api\controller\v1;
use app\api\model\Category as CategoryModel;
use app\lib\CategoryException;

class Category
{
	public function getAllCategorys()
	{
		/*
			all([],'Images') 
				空数组[]表示查询所有
				Images 是关联方法
			上面等同于
				with('Images')->select()
		*/
		$res = CategoryModel::all([],'Images');
		if ($res->isEmpty()) {
			throw new CategoryException;
		}
		return $res;
	}
}