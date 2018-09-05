<?php 
namespace app\lib\exception;

class UserException extends BaseException
{
	protected $code = 404;
	protected $msg = 'User Not Exists';
	protected $errorCode = 60000;
}