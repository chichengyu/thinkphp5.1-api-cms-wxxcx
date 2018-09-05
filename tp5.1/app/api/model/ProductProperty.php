<?php 
namespace app\api\model;


class ProductProperty extends Base
{
	protected $hidden = array('product_id','delete_time','update_time');

	public function product()
	{
		return $this->belongsTo('Product','product_id','id');
	}
}