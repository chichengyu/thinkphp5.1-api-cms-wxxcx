<?php 
namespace app\lib\exception;

class ForbiddenException extends BaseException
{
	protected $code = 401;
	protected $msg = '权限不够';
	protected $errorCode = 10001;
}
	