<?php 
namespace app\api\controller\v1;
use app\api\controller\BaseController;
use app\api\validate\IDMustValidate;
use app\api\service\Order as OrderService;
use app\api\service\Token as TokenService;
use app\api\service\Pay as PayService;

class Pay extends BaseController
{
	protected $beforeActionList = array(
		'checkExclusiveScope' => array('only'=>'getpreorder'),
	);

	/*
		post
		@param $id 订单id(下单成功后，返回订单id)
		return [object] 返回支付参数对象 
	*/
	public function getPreOrder($id='')
	{
		
		(new IDMustValidate)->goCheck();
		$pay = new PayService($id);
		return $pay->pay();
	}
	// 处理支付后的异步通知
	public function receiveNotify()
	{
	/*	异步通知频率 15/15/30/180/1800/1800/1800/1800/3600，单位：秒
		1.检测库存量，是否超卖
		2.更新这个订单的status状态
		3.减库存

		注意：
			1.微信调用此回调时是post方式，所以我们路由定义成post方式
			2.通知传递的数据是xml格式，不携带参数
	*/
		require_once './app/api/service/WxNotify.php';
		(new \WxNotify)->Handle(new \WxPayConfig);
	}
}