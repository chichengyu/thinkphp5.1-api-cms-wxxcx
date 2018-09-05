<?php 
namespace app\api\validate;

class AppTokenGet extends BaseValidate
{
	protected $rule = array(
		'ac' => 'require|isNotEmpty',
		'se' => 'require|isNotEmpty'
	);
}