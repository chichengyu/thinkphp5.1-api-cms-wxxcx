<?php 
namespace app\api\service;
use app\api\model\Product as ProductModel;
use app\lib\exception\OrderException;
use app\api\model\UserAddress as UserAddressModel;
use app\lib\exception\UserException;
use app\api\model\OrderProduct as OPModel;
use app\api\model\Order as OrderModel;
use app\lib\enum\OrderStatusEnum;
use think\Db;

class Order
{
	/*	订单的商品列表，也就是客户端提交过来的products商品列表，格式与OrderValidate验证器的products的商品列表数据格式一样
		$products = [[商品id,商品数量],[商品id,商品数量],.....];
		如：$products = [
			['product_id'=>'商品id','count'=>商品数量],
			['product_id'=>'商品id','count'=>商品数量],
			.....
		]
	*/
	protected $oProducts;
	// 数据库真实的商品相关信息，如：库存量
	// 如：订单商品列表的每件商品的库存量
	protected $products;
	// 用户id
	protected $uid;


	/*
		此方法用于外部调用，如支付、支付完成后回调 再次进行库存检测
		@param [string] $orderID 当前要支付的订单id
	*/
	public function payCheckOrderStock($orderID)
	{
		// 从订单商品表查询出要支付的订单商品，用于检测库存
		$oProducts = OPModel::where('order_id',$orderID)->select();
		$this->oProducts = $oProducts;
		$this->products = $this->getProductByOrder($oProducts);
		// 检测库存
		$status = $this->getOrderStatus();
		return $status;
	}
	/*
		@param [string]	   	$uid 用户id
		@param [array二维] 	$oProducts  用户提交订单数据数组
		return [object]	返回一个下单成功的信息对象
	*/
	public function place($uid,$oProducts)
	{
		// 客户端提交的订单列表
		$this->oProducts = $oProducts;
		// 订单列表每件商品的真实信息，查询数据库获取
		$this->products = $this->getProductByOrder($oProducts);
		$this->uid = $uid;

		// 检测订单所有商品，并返回相关信息
		$status = $this->getOrderStatus();
		if (!$status['pass']) {
			
			// 为了同步下一步下单成功后返回的订单id的数据格式
			// 所以，检测不通过时，返回一个不存在的订单id
			$status['order_id'] = -1;
			return $status;
		}
		// 生成订单快照数据
		$orderSnap = $this->snapOrder($status);
		
		// 创建订单，即入库
		$order = $this->createOrder($orderSnap);
		$order['pass'] = true;// 标记已成功
		return $order;
	}
	// 真正创建订单
	/*
		@param [array] $orderSnap 生成的订单快照数据
	*/
	public function createOrder($orderSnap)
	{
		Db::startTrans();
		try {
			$orderNo = $this->makeOrderNo();//生成订单号
			$orderModel = new \app\api\model\Order;
			$orderModel->order_no = $orderNo;
			$orderModel->user_id = $this->uid;
			$orderModel->total_price = $orderSnap['orderPrice'];
			$orderModel->total_count = $orderSnap['totalCount'];
			$orderModel->create_time = time();
			$orderModel->snap_name = $orderSnap['snamName'];
			$orderModel->snap_img = $orderSnap['snamImage'];
			// 数组转字符串
			$orderModel->snap_items = json_encode($orderSnap['pStatus']);
			// 数组转字符串
			$orderModel->snap_address = json_encode($orderSnap['snamAddress']);

			// 写入 order 订单表
			$orderModel->save();

			$create_time = $orderModel->create_time;
			// 获取下单成功后的订单 id ,写入这个中间表 order_product 订单商品表
			$orderID = $orderModel->id;
			foreach ($this->oProducts as $k => $v) {
				$this->oProducts[$k]['order_id'] = $orderID;
			}
			$orderProductModel = new \app\api\model\OrderProduct;
			$orderProductModel->saveAll($this->oProducts);
			Db::commit();
			return array(
				'orderNo' => $orderNo,// 订单号
				'order_id' => $orderID,// 订单id
				'create_time' => $create_time// 下单时间
			);
		} catch (Exception $e) {
			Db::rollback();
			throw $e;
		}
	}
	// 生成订单号
	public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }
	// 生成订单快照（即缩略图，就是用户查询每个订单具体的买了什么的信息）
	// 因为商品信息随时可能会变，所以需要存购买时的信息
	/*
		@param [array] $status 检测通过订单列表的所有商品
		return [array] 返回处理好的订单所有商品信息数据，准备入库
	*/
	private function snapOrder($status)
	{
		// 定义快照初始值
		$snap = array(
			'orderPrice' => 0,// 订单总价格(就是付款时的价格)
			'totalCount' => 0,// 订单总数量(总共多少件商品)
			'pStatus' => array(),// 每个订单里，每件商品具体购买信息
			'snamAddress'=> null,// 用户收货地址
			'snamName' => '',// 订单列表页面每个订单默认显示第一件商品名称
			'snamImage' => '',// 订单列表页面每个订单默认显示第一件商品的图片
		);
		$snap['orderPrice'] = $status['orderPrice'];
		$snap['totalCount'] = $status['totalCount'];
		$snap['pStatus'] = $status['pStatusArray'];
		$snap['snamAddress'] = $this->getUserAddress();
		$snap['snamName'] = $this->products[0]['name'];// 默认显示第一件商品名称
		$snap['snamImage'] = $this->products[0]['main_img_url'];

		if (count($this->products) > 1) {
			$snap['snamName'] .= '等...';
		}
		return $snap;
	}
	// 检测用户地址并返回
	private function getUserAddress()
	{
		$res = UserAddressModel::where('user_id','=',$this->uid)->find();
		if (!$res) {
			throw new UserException(array(
				'msg' => '用户地址不存在，下单失败',
				'errorCode' => 60001
			));
		}
		return $res->toArray();
	}
	// 对订单列表的所有商品进行检测
	private function getOrderStatus(){
		// 顶义订单列表返回数据的默认值
		$status = array(
			'pass' => true,// 订单检测，默认通过
			'orderPrice' => 0,// 订单列表所有商品的总价格
			'totalCount' => 0,// 总共买了几件商品(包括同样的商品)
			'pStatusArray' => array(), // 所有检测通过的订单列表的每件商品的详细信息,默认空数组
		);
		foreach ($this->oProducts as $v) {
			$pStatus = $this->getProductStatus($v['product_id'],$v['count'],$this->products);
			if (!$pStatus['haveStock']) {
				$status['pass'] = false;
			}
			// 计算订单列表所有商品的总价与总共多少件商品
			$status['orderPrice'] += $pStatus['totalPrice'];
			$status['totalCount'] += $pStatus['counts'];
			$status['pStatusArray'][] = $pStatus;
		}
		return $status;
	}
	// 当前商品检测
	/*
		@param [string] $oPID  	  用户提交订单中的一件商品id
		@param [string] $$oCount  用户提交订单的一件商品购买的数量
		@param [array]  $products 返回提交订单所有商品对应的数据库的真实商品信息,如：库存多少
		return array 一件商品经计算后的信息
	*/
	private function getProductStatus($oPID,$oCount,$products){
		// 定义当前这件商品的信息默认值
		$pStatus = array(
			'id' => null,// 当前检测的商品id
			'haveStock' => false,// 当前商品的库存量是否足够，默认不够
			'counts'=> 0,// 购买当前商品的数量，买了多少件
			'name' => '',// 当前商品名称
			'price' => 0,// 当前商品单价
			'totalPrice' => 0, // 计算购买当前商品的总价
			'main_img_url' => '',// 当前这件商品的图片
		);
		
		$pIndex = -1;// 用来标记通过检测当前商品id
		for ($i=0,$len=count($products); $i < $len; $i++) { 
			if ($oPID == $products[$i]['id']) {
				$pIndex = $i;
			}
		}
		// 判断当前商品是否存在
		if ($pIndex == -1) {
			// 客户端提交的 id 为 product_id 的商品可能不存在
			throw new OrderException(array(
				'msg' => 'id为'.$oPID.'的商品不存在，下单失败.'
			));
		}
		// 判断当前商品库存是否足够
		if (($products[$pIndex]['stock'] - $oCount) >= 0) {
			$pStatus['haveStock'] =  true;
		}
		// 存在并检测通过的商品进行计算
		$pStatus['id'] = $oPID;
		$pStatus['counts'] = $oCount;
		$pStatus['name'] = $products[$pIndex]['name'];
		$pStatus['price'] = $products[$pIndex]['price'];
		$pStatus['main_img_url'] = $products[$pIndex]['main_img_url'];
		$pStatus['totalPrice'] = $products[$pIndex]['price'] * $oCount;
		return $pStatus;
	}
	/*
		查询库所有商品的相关信息，如：库存量
		@param [array二维数组] $oProducts 用户提交订单信息
		return [array] 返回查询到的商品信息
	*/
	private function getProductByOrder($oProducts){
		$Ids = array();
		foreach ($oProducts as $item) {
			array_push($Ids,$item['product_id']);
		}
		$products = ProductModel::all($Ids)
				->visible(['id','name','price','stock','main_img_url'])
				->toArray();
		return $products;
	}
	/*
		发送订单模板消息
		@param [string|int] $id 订单id
		@param [string] $jumpPage 点击模板消息后的跳转地址
	*/
	public function delivery($id,$jumpPage='')
	{
		// 检测订单是否存在
		$order = OrderModel::where('id',$id)->find();
		if (!$order) {
			throw new OrderException;
		}
		// 检测订单支付状态
		if ($order->status != OrderStatusEnum::PAID) {
			throw new OrderException(array(
				'msg' => '订单还没支付或已发货',
				'errorCode' => 80002,
				'code' => 403
			));
		}
		// 更新订单在数据库的状态
		$order->status = OrderStatusEnum::DELIVERED;
		$order->save();
		//发送模板消息
		$message = new DeliveryMessage;
		return $message->sendDeliveryMessage($id,$jumpPage);
	}
}