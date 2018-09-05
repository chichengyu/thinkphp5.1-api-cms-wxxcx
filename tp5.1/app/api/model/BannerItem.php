<?php 
namespace app\api\model;

class BannerItem extends Base
{
	protected $hidden = ['img_id','delete_time','update_time'];
	public function Banners()
	{
		return $this->belongsTo('Banner','banner_id','id');
	}
	public function Images()
	{
		return $this->belongsTo('Image','img_id','id');
	}
}