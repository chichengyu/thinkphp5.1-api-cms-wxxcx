<?php 
namespace app\api\service;
use think\facade\Request;
use think\facade\Cache;
use app\lib\exception\TokenException;
use app\lib\exception\ForbiddenException;
use app\lib\enum\ScopeEnum;

class Token
{
	// 生成令牌
	public static function generateToken()
	{
		// 32哥字符组成随机字符串
		$randChars = getRandChars(32);
		// 三组随机的字符进行md5加密
		$timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
		// salt 盐
		$salt = config('salt');
		return md5($randChars.$timestamp.$salt);
	}
	// 获取缓存的数据属性
	public static function getCurrentTokenVar($key)
	{
		// 规定客户端吧 token放到 header 头里
		// 从 header 头获取用户 token
		$token = Request::instance()->header('token');
		$var = Cache::get($token);
		if (!$var) {
			throw new TokenException;
		}
		if (!is_array($var)) {
			$var = json_decode($var,true);
		}
		if (array_key_exists($key,$var)) {
			return $var[$key];
		}
		throw new Exception('Token variables do not exist');
	}

	// 利用携带的 token令牌 获取用户 uid
	public static function getCurrentUid()
	{
		$uid = self::getCurrentTokenVar('uid');
		return $uid;
	}

	// 用户和CMS管理员都可以访问
	public static function needPrimaryScope(){
		// 获取
		$scope = self::getCurrentTokenVar('scope');
		if (!$scope) {
			// token 过期或无效
			throw new TokenException;
		}
		if ($scope >= ScopeEnum::User) {
			return true;
		}
		throw new ForbiddenException;
	}
	// 只有用户可以访问 如：订单
	public static function needExclusiveScope(){
		// 获取
		$scope = self::getCurrentTokenVar('scope');
		if (!$scope) {
			// token 过期或无效
			throw new TokenException;
		}
		if ($scope == ScopeEnum::User) {
			return true;
		}
		throw new ForbiddenException;
	}
	// 只有CMS管理员可以访问
	public static function needSuperScope()
	{
		$scope = self::getCurrentTokenVar('scope');
		if (!$scope) {
			// token 过期或无效
			throw new TokenException;
		}
		if ($scope == ScopeEnum::Super) {
			return true;
		}
		throw new ForbiddenException;
	}
	/*
		支付时，验证订单id与当前用户id是否匹配
		@param [string] $checkUID 待验证的user_id(即订单与用户对应的)
	*/
	public static function isCheckOperate($checkUID)
	{
		if (!$checkUID) {
			throw new Exception('检测UID时，必须传入一个检测的UID');
		}
		$currentOpenrateUID = self::getCurrentUid();
		if ($currentOpenrateUID == $checkUID) {
			return true;
		}
		return false;
	}
	/*
		token令牌效验
		@param [string] $token 客户端携带的token令牌
	*/
	public static function verifyToken($token)
	{
		$exist = Cache::get($token);
		if ($exist) {
			return true;
		}
		return false;
	}
}