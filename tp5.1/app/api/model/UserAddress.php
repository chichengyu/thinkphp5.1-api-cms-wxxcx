<?php 
namespace app\api\model;

class UserAddress extends Base
{
	protected $hidden = array('id','delete_time','update_time');

	public function users()
	{
		return $this->belongsTo('User','user_id','id');
	}
}