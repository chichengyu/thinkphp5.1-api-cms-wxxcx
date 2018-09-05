<?php 
namespace app\api\controller\v1;
use app\api\validate\TokenGet;
use app\api\validate\AppTokenGet;
use app\api\service\UserToken;
use app\api\service\Token as TokenService;
use app\api\service\AppToken as AppTokenService;
use app\lib\exception\ParamsException;

class Token
{
	/*
		生成token令牌
		@param [string] $code 小程序wx.login得到的code码
	*/
	public function getToken($code='')
	{
		// 验证code
		(new TokenGet)->goCheck();
		// 根据code获取token
		$ut = new UserToken($code);
		$token = $ut->get();
		return array('token'=>$token);
	}
	/*
		第三方CMS应用获取令牌
		@url /app_token?
		@POST ac=:ac se=:secret
	*/
	public function getAppToken($ac='',$se='')
	{
		(new AppTokenGet)->goCheck();
		$token = (new AppTokenService)->get($ac,$se);
		return array('token'=>$token);
	}
	/*
		效验token令牌
		@param [string] $token 客户端携带的token令牌
	*/
	public function verifyToken($token='')
	{
		if (!$token) {
			throw new ParamsException(array('msg'=>'token不能为空'));
		}
		$valid = TokenService::verifyToken($token);
		return array('isValid'=>$valid);
	}
}