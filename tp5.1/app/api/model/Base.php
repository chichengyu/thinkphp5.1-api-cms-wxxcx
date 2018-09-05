<?php 
namespace app\api\model;
use think\Model;
use think\Db;
use think\facade\Log;

class Base extends Model
{
	protected function initialize(){
		if (config('app_debug')) {
			Db::listen(function ($sql, $time, $explain) {
			    // 记录SQL
			    Log::write($sql . ' [' . $time . 's]','sql');
			});
		}
	}
	/*
		处理图片前缀与来源
	*/
	protected function prifixImage($value,$data)
	{
		$url = $value;
		// $data['from'] == 1来自本地图片，不等1来自网络
		if ($data['from'] == 1) {
			$url = config('img_prifix').$url;
		}
		return $url;
	}
}