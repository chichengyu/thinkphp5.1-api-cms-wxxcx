<?php
namespace app\api\controller\v1;
use app\api\model\Banner as BannerModel;
use app\lib\exception\IndexException;
use app\lib\exception\BannerException;
use app\api\validate\IDMustValidate;
use think\exception\HttpException;

class Banner 
{
    public function banner(BannerModel $banner,$id)
    {
    	// 使用自定义验证器验证参数
        (new IDMustValidate)->goCheck();
    	$res = $banner::with(['BannerItems.Images'])->find($id);
    	if (!$res) {
    		throw new BannerException(['msg'=>'无数据','errorCode'=>30002]);
    	}
    	return $res;
    }
}