<?php 
namespace app\lib\exception;

class ProductException extends BaseException
{
	protected $code = 404;
	protected $msg = 'This is product not exists,Please check params';
	protected $errorCode = 20000;

}