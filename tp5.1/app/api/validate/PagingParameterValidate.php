<?php 
namespace app\api\validate;

class PagingParameterValidate extends BaseValidate
{
	protected $rule = array(
		'page' => 'isIdInteger',
		'size' => 'isIdInteger',
	);
	protected $message = array(
		'page' => '分页参数必须是正整数',
		'size' => '分页参数必须是正整数',
	);
}