<?php 
namespace app\lib\exception;

class TokenException extends BaseException
{
	protected $code = 401;
	protected $msg = 'Token Invalid or Expired';
	protected $errorCode = 20001;
}