<?php 
require_once './extend/WxPay/WxPay.Api.php';
// require_once './extend/WxPay/WxPay.Data.php';
// require_once './extend/WxPay/WxPay.Notify.php';
// require_once './extend/WxPay/WxPay.Config.php';

class WxNotify extends WxPayNotify
{
	protected $orderModel = null;
	protected $productModel = null;

	public function __construct()
	{
		if (!$this->orderModel || !$this->productModel) {
			$this->orderModel = new \app\api\model\Order;
			$this->productModel = new \app\api\model\Product;
		}
	}
	// <xml>
	//    <appid>wx2421b1c4370ec43b</appid>
	//    <attach>支付测试</attach>
	//    <body>JSAPI支付测试</body>
	//    <mch_id>10000100</mch_id>
	//    <detail><![CDATA[{ "goods_detail":[ { "goods_id":"iphone6s_16G", "wxpay_goods_id":"1001", "goods_name":"iPhone6s 16G", "quantity":1, "price":528800, "goods_category":"123456", "body":"苹果手机" }, { "goods_id":"iphone6s_32G", "wxpay_goods_id":"1002", "goods_name":"iPhone6s 32G", "quantity":1, "price":608800, "goods_category":"123789", "body":"苹果手机" } ] }]]></detail>
	//    <nonce_str>1add1a30ac87aa2db72f57a2375d8fec</nonce_str>
	//    <notify_url>http://wxpay.wxutil.com/pub_v2/pay/notify.v2.php</notify_url>
	//    <openid>oUpF8uMuAJO_M2pxb1Q9zNjWeS6o</openid>
	//    <out_trade_no>1415659990</out_trade_no>
	//    <spbill_create_ip>14.23.150.211</spbill_create_ip>
	//    <total_fee>1</total_fee>
	//    <trade_type>JSAPI</trade_type>
	//    <sign>0CB01533B8C1EF103065174F50BCA001</sign>
	// </xml>
	/*
		重写WxPay.Notify.php文件下回调基础类的 NotifyProcess 方法
	*/
	public function NotifyProcess($objData, $config, &$msg)
	{
		$orderModel = $this->orderModel;
		$productModel = $this->productModel;
		// 为了保证操作数据库不出错，开启事务
		$db = new \think\Db;
		$db::startTrans();

		//TODO 用户基础该类之后需要重写该方法，成功的时候返回true，失败返回false
		if ($objData['result_code'] == 'SUCCESS') {
			// 说明支付成功
			// 获取返回的订单号(即我们发送的订单号)
			$orderNo = $objData['out_trade_no'];
			try {
				$order = $orderModel::where('order_no',$order_no)->find();
				if ($order->status == 1) {
					//库存检测
					$orderServerice = new \app\api\service\Order;
					$stockStatus = $orderServerice->payCheckOrderStock($order->id);
					// 检测库存通过
					if ($stockStatus['pass']) {
						// 库存足够，更新为已支付状态
						$this->updateOrderStatus($order->id,true);
						// 减库存
						$this->reduceStock($stockStatus);
					} else {
						// 库存足够，更新为已支付 但库存不足状态
						$this->updateOrderStatus($order->id,false);
					}
				}
				$db::commit();
				// 告诉微信服务器,我们已经正确处理完成，不需要在持续向我们发送异步通知了
				return true;
			} catch (Exception $e) {
				$db::rollback();
				$log = new \think\facade\Log;
				$log::write($e,'error');
				return false;
			}
		} else {	
			// 告诉微信服务器，我们已接收到支付失败的消息，如果返回false，微信服务器还会向我们持续发送支付结果，会以为我们没接收到支付结果
			return true;
		}
	}
	// 减库存
	/*
		@param [obj] $stockStatus 检测库存后返回的相关数据对象
	*/
	private function reduceStock($stockStatus)
	{
		$productModel = $this->productModel;
		foreach ($stockStatus['pStatusArray'] as $v) {
			$productModel::where('id',$v['id'])->setDec('stock',$v['count']);
		}
	}
	// 更新order订单表订单的支付状态
	/*
		@param [string] $orderId 订单id
		@param [bool] 	$success 支持成功后，库存是否足够
	*/
	private function updateOrderStatus($orderId,$success)
	{
		$orderModel = $this->orderModel;
		$obj = new \app\lib\enum\OrderStatusEnum;
		$status = $success?($obj::PAID):($obj::PAID_BUT_OUT_OF);
		$orderModel::where('id',$orderId)->update(['status'=>$status]);
	}
}