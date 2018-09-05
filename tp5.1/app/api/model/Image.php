<?php 
namespace app\api\model;


class Image extends Base
{
	protected $hidden = array('id','delete_time','update_time');
	
	public function BannerItems()
	{
		return $this->hasOne(BannerItem::class,'banner_id','id');
	}
	public function getUrlAttr($value,$data)
	{
		return $this->prifixImage($value,$data);
	}
}