<?php 
namespace app\api\validate;

class TokenGet extends BaseValidate
{
	protected $rule = array(
		'code' => 'require|isNotEmpty'
	);
	protected $message = array(
		'code' => '必须有code值'
	);
}