<?php 
namespace app\lib\exception;

class BannerException extends BaseException
{
	protected $code = 400;
	protected $msg = 'Banner Not exists';
	protected $errorCode = 10004;

}