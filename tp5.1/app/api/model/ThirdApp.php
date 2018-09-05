<?php 
namespace app\api\model;

class ThirdApp extends Base
{
	public static function check($ac,$se)
	{
		$res = self::where(array(
					'app_id' => $ac,
					'app_secret' => $se
				))
				->find();
		return $res;
	}
}