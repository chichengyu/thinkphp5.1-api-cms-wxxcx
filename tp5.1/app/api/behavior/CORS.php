<?php 
namespace app\api\behavior;

class CORS
{
	// CORS 跨域请求（简单请求与复杂请求）
	public function run($params)
	{
	    /*   指定多个域名跨域
		$all_origin = [
		     'http://456.com',
		     'http://94.191.42.70:8004'
		];
		// 跨域访问的时候才会存在此字段 $_SERVER['HTTP_ORIGIN']
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
		if (!in_array($origin,$all_origin)){
		      return;
		}
		header('Access-Control-Allow-Origin: '.$origin);
	    */
		// 允许所有的域访问
		header('Access-Control-Allow-Origin: http://123.com');
		// 允许访问的时候header允许携带的那些键值对
        	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, token");
        	// 允许访问的方式
        	header('Access-Control-Allow-Methods: POST,GET');
        	// 一般不允许复杂请求(即options请求)
        	if (request()->isOptions()) {
        		exit();
        	}
	}
}
