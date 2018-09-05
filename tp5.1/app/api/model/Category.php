<?php 
namespace app\api\model;

class Category extends Base
{
	protected $hidden = array('delete_time','update_time');

	public function Images()
	{
		return $this->belongsTo('Image','topic_img_id','id');
	}
}