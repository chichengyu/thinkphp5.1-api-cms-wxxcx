<?php 
namespace app\api\controller\v1;
use app\api\model\Theme as ThemeModel;
use app\api\validate\ThemeValidate;
use app\lib\exception\ThemeException;
use app\api\validate\IDMustValidate;
use think\Collection;

class Theme 
{
	/*
		http://test.com/theme/v1/theme?ids=1,2,3
		@url /theme?ids=1,2,3,4....
		return 一组theme模型
	*/
	public function getSimpelist($ids=null)
	{
		// 验证参数是否为正整数
		(new ThemeValidate)->goCheck();
		$res = ThemeModel::with('topicImage,headerpicImage')->select($ids);
		if ($res->isEmpty()) {
			throw new ThemeException;
		}
		return $res;
	}

	/*
		http://test.com/theme/v1/theme/1
		@url /theme/1
	*/
	public function getComplexOne(ThemeModel $theme,$id)
	{
    	// 使用自定义验证器验证参数
        (new IDMustValidate)->goCheck();
		$res = $theme::with('products,topicImage,headerpicImage')->find($id);
		if (Collection::make($res)->isEmpty()) {
			throw new ThemeException;
		}
		return $res;
	}
}