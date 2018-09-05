<?php 
namespace app\api\controller\v1;
use app\api\validate\AddressValidate;
use app\api\model\User as UserModel;
use app\api\model\UserAddress as UserAddressModel;
use app\lib\exception\UserException;
use app\lib\exception\SuccessMessage;
use app\api\controller\BaseController;
use app\api\service\Token as TokenService;

class Address extends BaseController
{
	// 每次调用 createOrUpdateAddress 前先验证权限
	protected $beforeActionList = [
        'checkPrimaryScope'  =>  ['only'=>'createorupdateaddress,getuseraddress'],
    ];

	// 获取用户地址
	public function getUserAddress()
	{
		// 用携带的token令牌获取用户uid
		$uid = TokenService::getCurrentUid();
		$userAddress = UserAddressModel::where('user_id',$uid)->find();
		if (!$userAddress) {
			throw new UserException(array(
				'msg' => '用户地址不存在',
				'errorCode' => 60001
			));
		}
		return $userAddress;
	}
	// 添加 与 更新 用户地址
	public function createOrUpdateAddress()
	{
		$validater = new AddressValidate;
		$validater->goCheck();
		// 1.根据客户端携带的token令牌来获取缓存的 uid
		// 2.根据 uid 查询用户是否存在,不存在绕出异常
		// 3.获取用户提交的地址信息
		// 4.根据用户uid判断，地址信息是新增还是更新
		$uid = TokenService::getCurrentUid();
		$user = UserModel::get($uid);
		if (!$user) {
			throw new UserException;
		}
		// 对提交的数据进行安全过滤筛选
		$data = $validater->getDataByRule(input('post.'));
		// 获取 address 关联模型的数据
		$address = $user->address;
		if ($address) {
			// 存在就更新
			$user->address->save($data);
		} else {
			// 不存在就新增
			$user->address()->save($data);
		}
		throw new SuccessMessage;
	}
}