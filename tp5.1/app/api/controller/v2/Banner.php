<?php
namespace app\api\controller\v2;
use app\api\model\Banner as BannerModel;
use app\lib\exception\IndexException;
use app\lib\exception\BannerException;
use app\api\validate\BannerValidate;

class Banner
{
    public function banner(BannerModel $banner,$id)
    {
    	return 'This v2 version';
    }
}