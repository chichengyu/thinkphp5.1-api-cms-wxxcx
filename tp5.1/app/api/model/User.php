<?php 
namespace app\api\model;

class User extends Base
{
	public function address()
	{
		return $this->hasOne('UserAddress','user_id','id');
	}

	public static function getByopenID($openid)
	{
		$user = self::where('openid','=',$openid)->find();
		return $user;
	}
}