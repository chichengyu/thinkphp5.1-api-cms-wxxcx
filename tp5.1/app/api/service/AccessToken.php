<?php 
namespace app\api\service;

class AccessToken 
{
	/*
		AccessToken 作用：
			1.请求access_token
			2.管理access_token
	*/
	// 请求access_token的地址
	private $tokenUrl;
	// 缓存的键值名称key
	const TOKEN_CACHE_KEY = 'access';
	// access_token过期时间（隔多久请求一次）
	const TOKEN_EXPIRE_IN = 7000;

	public function __construct()
	{
		$this->tokenUrl = sprintf(config('app.wx.access_token_url'),config('app.wx.app_id'),config('app.wx.app_secret'));
	}
	// 建议用户规模小时，直接去微信服务器取最新的token
	// 微信 access_token 获取是有限制的 2000次/天
	public function get()
	{
		$token = $this->getFromCache();
		if (!$token) {
			// 向微信服务器请求 access_token
			return $this->getFromWxServer();
		}else{
			return $token['access_token'];
		}
	}
	// 从缓存读取 access_token
	private function getFromCache()
	{
		$token = Cache(self::TOKEN_CACHE_KEY);
		if ($token) {
			return json_decode($token,true);
		}
		return false;
	}
	// 向微信服务器请求 access_token
	private function getFromWxServer()
	{
		$accessToken = curl_get($this->tokenUrl);
		$accessToken = json_decode($accessToken,true);
		if (!$accessToken) {
		 	throw new Exception("获取access_token异常");
		 }
		 if (!$accessToken['errcode']) {
		 	throw new Exception($accessToken['errmsg']);
		 }
		 // 存缓存
		 $this->saveToCache($accessToken);
		 return $accessToken['access_token'];
	}
	/*
		存缓存
		@param [object] $accessToken 获取accessToken成功的数据对象
	*/
	private function saveToCache($accessToken)
	{
		Cache(self::TOKEN_CACHE_KEY,json_encode($accessToken),self::TOKEN_EXPIRE_IN);
	}
}