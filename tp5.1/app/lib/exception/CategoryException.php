<?php 
namespace app\lib\exception;

class CategoryException extends BaseException
{
	protected $code = 404;
	protected $msg = 'Category Not Exists';
	protected $errorCode = 50000;
}