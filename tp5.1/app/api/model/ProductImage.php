<?php 
namespace app\api\model;


class ProductImage extends Base
{
	protected $hidden = array('img_id','delete_time','product_id');

	public function images()
	{
		return $this->belongsTo('Image','img_id','id');
	}
}