<?php 
namespace app\api\service;
use app\api\model\ThirdApp as ThirdAppModel;
use app\lib\exception\TokenException;

class AppToken extends Token
{
	public function get($ac,$se)
	{
		$app = ThirdAppModel::check($ac,$se);
		if (!$app) {
			throw new TokenException(array(
				'msg' => '授权失败',
				'errorCode' => 10004
			));
		}else{
			$scope = $app->scope;
			$uid = $app->id;
			$values = array(
				'scope' => $scope,
				'uid' => $uid,
			);
			// 存缓存
			$token = $this->saveToCache($values);
			return $token;
		}
	}
	private function saveToCache($values)
	{
		// 获取令牌存缓存并返回回去
		$token = self::generateToken();
		$res = Cache($token,json_encode($values),config('app.token_expire_in'));
		if (!$res) {
			throw new TokenException(array(
				'msg' => '服务器缓存异常',
				'errorCode' => 10005
			));
		}
		return $token;
	}
}