<?php 
namespace app\api\model;


class Banner extends Base
{
	public function BannerItems()
	{
		return $this->hasMany(BannerItem::class,'banner_id','id');
	}
}