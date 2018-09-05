<?php 
namespace app\api\validate;

class ProductValidate extends BaseValidate
{
	protected $rule = array(
		'count' => 'between:1,15|isIdInteger'
	);
}