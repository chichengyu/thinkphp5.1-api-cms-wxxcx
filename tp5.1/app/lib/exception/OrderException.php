<?php 
namespace app\lib\exception;

class OrderException extends BaseException
{
	protected $code = 404;
	protected $msg = 'Product Not Exists';
	protected $errorCode = 80000;
}