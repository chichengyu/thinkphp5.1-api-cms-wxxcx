<?php 
namespace app\lib\exception;

class ThemeException extends BaseException
{
	protected $code = 404;
	protected $msg = 'Theme Not Exists';
	protected $errorCode = 30000;
}