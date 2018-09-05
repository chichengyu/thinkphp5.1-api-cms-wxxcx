<?php 
namespace app\api\model;


class Product extends Base
{
	protected $hidden = array('pivot','create_time','update_time','delete_time');

	public function themes()
	{
		return $this->belongsToMany('Theme','theme_product','theme_id','product_id');
	}
	public function productImages()
	{
		return $this->hasMany('ProductImage','product_id','id');
	}
	public function productPropertys()
	{
		return $this->hasMany('ProductProperty','product_id','id');
	}
	// 获取器处理日期
	public function getMainImgUrlAttr($value,$data)
	{
		return $this->prifixImage($value,$data);
	}
	// 最新商品列表
	public static function getMostRecent($limit)
	{
		$result = self::order('create_time DESC')->limit($limit)->select();
		return $result->hidden(['summary']);
	}
	// 该分类下的所有商品
	public static function getProductCategory($cateId)
	{
		$result = self::where('category_id',$cateId)->select();
		return $result->hidden(['summary']);
	}
}