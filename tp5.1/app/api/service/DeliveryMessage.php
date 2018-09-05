<?php 
namespace app\api\service;
use app\api\model\User;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;

class DeliveryMessage extends WxMessage
{
	// 模板消息的模板ID
	const DELIVERY_MSG_ID = 'TzcM97CXisk7v61_D4Vv2_mrZPNpx5i_suOWUvr4q80';

	/*
		发送模板消息
		@param [object] $order 订单信息对象
		@param [string] $tplJumpPage 用户点击模板消息之后跳转地址
	*/
	public function sendDeliveryMessage($order,$tplJumpPage='')
	{
		if (!$order) {
			throw new OrderException;
		}
		// 所需下发的模板消息的模板id
		$this->tplID = self::DELIVERY_MSG_ID;
		// 表单提交场景下，为 submit 事件带上的 formId；支付场景下，为本次支付的 prepay_id
		$this->formID = $order->prepay_id;
		// 用户点击模板消息之后跳转地址
		$this->page = $tplJumpPage;
		//模板内容
		$this->prepareMessageData($order);
		// 模板需要放大的关键词
		$this->emphasisKeyWord('keyword2.DATA');
	
		return parent::sendMessage($this->getUserOpenID($order->user_id));
	}
	/*
		模板消息关键字
		@param [object] $order 订单信息对象
	*/
	private function prepareMessageData($order)
	{
		$dt = new \DataTime();
		$data = array(
			'keyword1' => array('value'=>$order->snap_name,'color'=>'red'),
			'keyword2' => array('value'=>$dt->format('Y-m-d H:i:s')),
			'keyword3' => array('value'=>'购买时间'),
			'keyword4' => array('value'=>'快递公司'),
		);
		$this->data = $data;
	}
	private function getUserOpenID($orderID)
	{
		$user = User::get($orderID);
		if (!$user) {
			throw new UserException;
		}
		return $user->openid;
	}
}