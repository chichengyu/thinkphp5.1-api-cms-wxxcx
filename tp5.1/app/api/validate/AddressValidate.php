<?php 
namespace app\api\validate;

class AddressValidate extends BaseValidate
{
	protected $rule = array(
		'name' 		=> 'require|isNotEmpty',
		'mobile' 	=> 'require|isMobile',
		'province' 	=> 'require|isNotEmpty',
		'city' 		=> 'require|isNotEmpty',
		'country' 	=> 'require|isNotEmpty',
		'detail'	=> 'require|isNotEmpty',
	);
	protected $message = array(
		'name' 		=> '姓名不能为空',
		'mobile' 	=> '手机号不正确',
		'province' 	=> '省不能为空',
		'city' 		=> '市不能为空',
		'country' 	=> '区不能为空',
		'detail' 	=> '详细地址不能为空',
	);
}