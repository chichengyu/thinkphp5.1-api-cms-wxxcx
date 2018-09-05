<?php 
namespace app\lib\exception;

class SuccessMessage extends BaseException
{
	protected $code = 200;
	protected $msg = 'Success';
	protected $errorCode = 0;
}