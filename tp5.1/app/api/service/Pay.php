<?php 
namespace app\api\service;
use app\api\model\Order as OrderModel;
use app\lib\exception\ParamsException;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use app\api\service\Order as OrderService;
use app\api\service\Token as TokenService;
use app\lib\enum\OrderStatusEnum;
use think\facade\Log;

class Pay
{
	private $orderID;// 订单id
	private $orderNo;// 订单号

	public function __construct($orderID)
	{
		if (!$orderID) {
			throw new ParamsException(array('msg'=>'订单号不允许为NULL'));
		}
		$this->orderID = $orderID;
	}
	// 支付
	public function pay()
	{
		// 1.检测订单id(即订单号)是否存在
		// 2.检测订单号与当前用户id是否存在
		// 3.订单是否已支付
		// 4.库存量检测
		$this->checkOrderValid();
		$status = (new OrderService)->payCheckOrderStock($this->orderID);
		if (!$status['pass']) {
			// 库存不足
			return $status;
		}
		// 5.往微信服务器发送预订单
		return $this->makeWXPreOrder($status['orderPrice']);
	}
	/*
		预订单参数配置(统一下单)
		@param [obj] $totalPrice 订单总价格
	*/
	private function makeWXPreOrder($totalPrice)
	{
		// openid
		$openid = TokenService::getCurrentTokenVar('openid');
		if (!$openid) {
			throw new TokenException;
		}
		// 统一下单
		require_once './extend/WxPay/WxPay.Api.php';
		$wxOrderData = new \WxPayUnifiedOrder;
		// 配置订单号
		$wxOrderData->SetOut_trade_no($this->orderNo);
		// 配置交易类型
		$wxOrderData->SetTrade_type('JSAPI');
		// 配置订单总金额 微信服务器价格单位是分,我们价格单位是元，所以 *100 转成分
		$wxOrderData->SetTotal_fee($totalPrice*100);
		// 配置商品或支付单简要描述
		$wxOrderData->SetBody('小二的小程序');
		// 配置openid
		$wxOrderData->SetOpenid($openid);
		// 配置接收微信支付异步通知回调地址 非常重要 '记得填回调地址'
		$wxOrderData->SetNotify_url(config('app.wx.pay_back_url'));
	
		return $this->getPaySingnatrue($wxOrderData);
	}
	/*
		向微信服务器发送预订单
		@param [obj] $wxOrderData 预订单参数配置对象
	*/
	private function getPaySingnatrue($wxOrderData)
	{
		// 由于此方法在预订单参数配置方法 makeWXPreOrder 引入了WxPay.Api.php,所以此处不用引入，可直接调用
		// 发送预订单到微信服务器
		$wxOrder = \WxPayApi::unifiedOrder(new \WxPayConfig,$wxOrderData);
		if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
			// 写入日志
			Log::write('获取预支付订单失败','error');
			Log::write($wxOrder,'error');
		}
		// 处理成功后,返回的$wxOrder里的prepay_id参数
		// prepay_id 作用：订单微信支付的预订单id（用于发送模板消息）存入数据库
		$this->recordPreOrder($wxOrder['prepay_id']);
		$signatrue = $this->sign($wxOrder);
		return $signatrue;
	}
	/*
		处理发送预订单成功后返回给客户端的数据
		@param [obj] $wxOrder 发送预订单到微信服务器成功后返回的对象
		return [object] 返回预订单参数对象
	*/
	private function sign($wxOrder)
	{
		$jsApiPayData = new \WxPayJsApiPay;
		// 设置小程序appid
		$jsApiPayData->SetAppid(config('app.wx.app_id'));
		// 随机字符串
		$str = md5(time().mt_rand(0,9999));
		$jsApiPayData->SetNonceStr($str);
		// 设置时间戳
		$jsApiPayData->SetTimeStamp((string)time());// 注意：必须转成字符串型
		// 设置统一下单接口返回的 prepay_id 参数值
		$jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
		// 设置签名方式
		$jsApiPayData->SetSignType('md5');
		// 设置签名 客户端需要的 paySign 参数设置
		// 生成签名
		$sign = $jsApiPayData->MakeSign(new \WxPayConfig,false);
		$jsApiPayData->SetPaySign($sign);

		// 生成我们需要数组格式数据
		$rawValues = $jsApiPayData->GetValues();
		// 由于客户端不需要appid，所以直接从 rawValues 里删除 appId
		unset($rawValues['appId']);
		return $rawValues;
	}
	/*
		将返回对象$wxOrder里的参数的 prepay_id 存到订单表
		@param [string] $prepayId 返回对象$wxOrder里的参数的prepay_id
	*/
	private function recordPreOrder($prepayId)
	{
		OrderModel::where('id',$this->orderID)->update(['prepay_id'=>$prepayId]);
	}
	// 支付前的验证检测
	private function checkOrderValid()
	{
		$order = OrderModel::where('id',$this->orderID)->find();
		if (!$order) {
			throw new OrderException(array('msg'=>'订单不存在'));
		}
		if (!TokenService::isCheckOperate($order->user_id)) {
			throw new TokenException(array(
				'msg' => '订单与用户不匹配',
				'errorCode' => 10003
			));
		}
		if ($order['status'] != OrderStatusEnum::UNPAID) {
			throw new OrderException(array(
				'msg' => '订单已支付',
				'errorCode' => 80003,
				'code' => 400
			));
		}
		$this->orderNo = $order->order_no;
		return true;
	}
}