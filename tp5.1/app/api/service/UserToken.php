<?php 
namespace app\api\service;
use app\lib\exception\WeChatException;
use app\api\model\User as UserModel;
use app\api\service\Token;
use app\lib\exception\TokenException;
use app\lib\enum\ScopeEnum;

class UserToken extends Token
{
	protected $code;
	protected $wxAppID;
	protected $wxAppSecret;
	protected $wxLoginUrl;

	public function __construct($code)
	{
	 	$this->code = $code;
	 	$this->wxAppID = config('app.wx.app_id');
	 	$this->wxAppSecret = config('app.wx.app_secret');
	 	$this->wxLoginUrl = sprintf(config('app.wx.login_url'),$this->wxAppID,$this->wxAppSecret,$this->code);
	 } 
	// 获取 token(即 $key)
	public function get()
	{	
		// 携带code用curl向微信服务器发送一个请求
		// 返回一个json格式字符串
		$result = curl_get($this->wxLoginUrl);
		// 将返回的字符串转成数组
		$wxResult = json_decode($result,true);
		// 判断调用是否失败,传入的code不合法时，返回空
		if (empty($wxResult)) {
			throw new Exception('获取session_key及openID时异常，微信内部错误');
		} else {
			// 进一步判断，调用成功时，没有errcode，反之，则有errcode
			$loginFail = array_key_exists('errcode',$wxResult);
			if ($loginFail) {
				$this->processLoginError($wxResult);
			} else {
				return $this->grantToken($wxResult);
			}
		}
	}
	// 获取生成令牌,，并处理缓存数据
	private function grantToken($wxResult)
	{
		// 1.拿到openID
		// 2.去数据库查看一下，这个openID是不是存在
		// 3.如果存在，暂不处理，如果不存在，就添加一条user信息
		// 4.生成令牌，准备缓存数据，写入缓存
		// 5.把令牌返回给客户端
		$openid = $wxResult['openid'];
		$user = UserModel::getByopenID($openid);
		if ($user) {
			$uid = $user->id;
		} else {
			$uid = $this->newUser($openid);
		}
		// 缓存数据
		// key: 令牌
		// value: $wxResult,$uid，scope(权限级别)
		// 处理准备缓存数据
		$cachedValue = $this->prepareCachedValue($wxResult,$uid);
		// 缓存数据
		$token = $this->saveTocachedValue($cachedValue);
		return $token;
	}
	// 将数据写入缓存
	private function saveTocachedValue($cachedValue)
	{
		// 生成令牌 即 缓存数据的 key
		$key = self::generateToken();
		//由于 tp 自带的Cache不能直接缓存数组与对象,所以转成字符串
		$value = json_encode($cachedValue);
		// 过期时间
		$expire_in = config('token_expire_in');
		$res = Cache($key,$value,$expire_in);
		if (!$res) {
			throw new TokenException(array(
				'msg' => '服务器缓存异常',
				'errorCode' => 10005
			));
		}
		return $key;
	}
	// 处理准备缓存的数据
	private function prepareCachedValue($wxResult,$uid)
	{
		$cachedValue = $wxResult;
		$cachedValue['uid'] = $uid;
		// 16表示app用户权限数值，32表示CMS(管理员)用户的权限数值
		$cachedValue['scope'] = ScopeEnum::User;// 权限级别
		return $cachedValue;
	}
	// 添加数据
	private function newUser($openid)
	{
		$user = UserModel::create(array('openid'=>$openid));
		return $user->id;
	}
	// 抛出异常
	private function processLoginError($wxResult)
	{
		throw new WeChatException(array(
			'code' => $wxResult['errcode'],
			'msg'  => $wxResult['errmsg']
		));
	}
}