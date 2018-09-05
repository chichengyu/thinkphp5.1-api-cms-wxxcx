<?php 
namespace app\api\service;
use think\Exception;

class WxMessage 
{
	// 发送模板消息地址
	private $sendUrl = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=%s';
	// 接收者（用户）的 openid
	private $touser;
	// 模板内容字体的颜色 不让子类控制其颜色
	private $color = 'black';

	// 所需下发的模板消息的模板id
	protected $tplID;
	// 表单提交场景下，为 submit 事件带上的 formId；支付场景下，为本次支付的 prepay_id
	protected $formID;
	// $page 用户点击模板消息之后跳转地址
	protected $page;
	//模板内容
	protected $data;
	// 模板需要放大的关键词
	protected $emphasisKeyWord;

	public function __construct()
	{
		$accessToken = new AccessToken();
		$token = $accessToken->get();
		$this->sendUrl = sprintf($this->sendUrl,$token);
	}
	/*
		开发工具中拉起的微信支付prepay_id是无效的，需要在真机上拉起支付
		@param [string] $openID 用户的openid 
	*/
	public function sendMessage($openID)
	{
		$data = array(
			'touser' => $openID,
			'template_id' => $this->tplID,
			'page' => $this->page,
			'form_id' => $this->formID,
			'data' => $this->data,
			'emphasis_keyword' => $this->emphasisKeyWord
		);
		$result = curl_post_raw($this->sendUrl,$data);
		$result = json_decode($result,true);
		if ($result['errcode'] == 0) {
			return true;
		}
		throw new Exception('模板消息发送失败, '. $result['errmsg']);
	}
}