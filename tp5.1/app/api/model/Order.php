<?php 
namespace app\api\model;


class Order extends Base
{
	protected $hidden = array('user_id','delete_time','update_time');

	public static function getSummaryByUser($uid,$page,$size)
	{
		$result = self::where('user_id',$uid)
			->order('create_time DESC')
			->paginate($size,true,array('page'=>$page));
		return $result->hidden(['snap_items','snap_address','prepay_id']);
	}
	public static function getSummaryByPage($page,$size)
	{
		$result = self::order('create_time DESC')
			->paginate($size,true,array('page'=>$page));
		return $result->hidden(['snap_items','snap_address']);
	}
	public function getCreateTimeAttr($value,$data)
	{
		return date('Y-m-d H:i:s',$value);
	}
	public function getSnapItemsAttr($value,$data)
	{
		if (empty($value)) {
			return null;
		}
		return json_decode($value);
	}
	public function getSnapAddressAttr($value,$data)
	{
		if (empty($value)) {
			return null;
		}
		return json_decode($value);
	}
}