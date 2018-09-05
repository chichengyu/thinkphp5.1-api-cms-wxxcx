<?php 
namespace app\api\model;

class Theme extends Base
{
	protected $hidden = ['delete_time','update_time','topic_img_id','head_img_id'];

	
	public function topicImage()
	{
		return $this->belongsTo('Image','topic_img_id','id');
	}
	public function headerpicImage()
	{
		return $this->belongsTo('Image','head_img_id','id');
	}
	public function products()
	{
		return $this->belongsToMany('Product','theme_product','product_id','theme_id');
	}
}