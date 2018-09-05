<?php 
namespace app\api\controller\v1;
use app\api\controller\BaseController;
use app\api\validate\OrderValidate;
use app\api\validate\PagingParameterValidate;
use app\api\validate\IDMustValidate;
use app\api\service\Order as OrderService;
use app\api\service\Token as TokenService;
use app\api\model\Order as OrderModel;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;

class Order extends BaseController
{
/*
	业务逻辑：
		1.用户在选择商品后，向API提交包含所选商品的相关信息
		2.API在接收到信息后，需要查询订单商品的库存量
		3.有库存，吧订单数据存入数据库，下单成功，返回客户端信息，告诉客户可以支付了
		4.调用我们的微信支付接口，进行支付
		5.还需要再次进行查询库存
		6.服务器这边就可以调用微信的支付接口进行支付
		7.小程序根据服务器返回的结果拉起微信支付页面
		8.在支付的过程中，可能有延迟，也需要进行库存查询
		这时，微信会返回给我们一个支付结果(异步)
		9.支付成功，检测库存量,进行库存扣除

	注意：支付成功或失败，不是由我们返回给客户端的，因为微信的返回的结果不是实时的，也就是说，调用了微信接口，不是立马就返回给我们一个结果，微信会返回给我们的小程序一个支付结果
*/
	// 先验证权限
	protected $beforeActionList = array(
		'checkExclusiveScope' => array('only'=>'placeorder'),
		// 管理员和用户都能能访问订单
		'checkPrimaryScope' => array('only'=>'getsummarybyuser,getdetail'),
		// 只有CMS管理员可以访问
		'needSuperScope' => array('only'=>'getsummary,delivery'),
	);

	// 下单
	/*
		post 提交数据
		token 	 参数1：携带token令牌
		products 参数2：选中待支付的商品列表(二维数组)
			products = [['product_id商品id','count商品数量']...]
		return array(
			’pass‘ => true,// 标记成功
			'orderNo' => $orderNo,// 订单号
			'order_id' => $orderID,// 订单id
			'create_time' => date('Y-m-d H:i:s',$create_time)// 下单时间
		);
	*/
	public function placeOrder()
	{
		(new OrderValidate)->goCheck();
		$oProducts = input('post.products/a');
		$uid = TokenService::getCurrentUid();
		$status = (new OrderService)->place($uid,$oProducts);
		return $status;
	}
	/*
		订单列表（小程序用户） GET
		@param $page 当前页
		@param $size 获取的数据条数
	*/
	public function getSummaryByUser($page=1,$size=15)
	{
		(new PagingParameterValidate)->goCheck();
		$uid = TokenService::getCurrentUid();
		$res = OrderModel::getSummaryByUser($uid,$page,$size);
		if ($res->isEmpty()) {
			return array(
				'data' => array(),
				'current_page' => $page
			);
		}
		return array(
			'data' => $res,
			'current_page' => $page
		);
	}
	/*
		第三方CMS管理系统访问
		@param $page 当前页
		@param $size 获取的数据条数
	*/
	public function getSummary($page=1,$size=15)
	{
		(new PagingParameterValidate)->goCheck();
		$res = OrderModel::getSummaryByPage($page,$size);
		if ($res->isEmpty()) {
			return array(
				'data' => array(),
				'current_page' => $page
			);
		}
		return array(
			'data' => $res,
			'current_page' => $page
		);
	}
	/*
		@param $id 当前订单的id (用于查询当前订单具体购买的那些商品)
	*/
	public function getDetail($id)
	{
		(new IDMustValidate)->goCheck();
		$orderDetail = OrderModel::get($id);
		if (!$orderDetail) {
			throw new OrderException;
		}
		return $orderDetail->hidden(['prepay_id'])->toArray();
	}
	/*
		发送订单模板消息
		@param [string|int] $id 订单id
	*/
	public function delivery($id)
	{
		(new IDMustValidate)->goCheck();
		$orderService = new OrderService;
		$success = $orderService->delivery($id);
		if ($success) {
			return new SuccessMessage;
		}
	}
}