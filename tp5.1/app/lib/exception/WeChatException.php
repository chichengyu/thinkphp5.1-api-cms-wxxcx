<?php 
namespace app\lib\exception;

class WeChatException extends BaseException
{
	protected $code = 400;
	protected $msg = '微信服务器接口调用失败';
	protected $errorCode = 999;
}