<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 轮播 http://test.com/api/v1/banner/1
Route::get('api/:version/banner/:id', 'api/:version.Banner/banner');


// 专题列表 http://test.com/api/v1/theme?ids=1,2,3,4
Route::get('api/:version/theme', 'api/:version.Theme/getSimpelist');
// 指定专题下的所有商品 http://test.com/api/v1/theme/1
Route::get('api/:version/theme/:id', 'api/:version.Theme/getComplexOne');

// 商品
Route::group('api/:version/product',function(){
	// 最新商品列表 http://test.com/api/v1/product/recent?count=15
	Route::get('recent', 'api/:version.Product/getRecent');
	// 当前分类下的所有商品 http://test.com/api/v1/product/by_category?cateId=2
	Route::get('by_category', 'api/:version.Product/getAllInCategory');
	// 一件商品详情 http://test.com/api/v1/product/2
	Route::get(':id', 'api/:version.Product/getProductOne',[],['id'=>'\d+']);
});


// 所有分类 http://test.com/api/v1/category/all
Route::get('api/:version/category/all', 'api/:version.Category/getAllCategorys');


// 用户添加或修改收获地址 http://test.com/api/v1/address
Route::post('api/:version/address', 'api/:version.Address/createOrUpdateAddress');
// 获取用户地址 
Route::get('api/:version/address', 'api/:version.Address/getUserAddress');

// 获取token令牌 http://test.com/api/v1/token/user
// 注意：前端携带token访问时，必须放在header头里，参数名为 token:值
Route::post('api/:version/token/user', 'api/:version.Token/getToken');
// token令牌效验 http://test.com/api/v1/token/verify
Route::post('api/:version/token/verify', 'api/:version.Token/verifyToken');
// CMS登陆  http://test.com/api/v1/token/app
Route::post('api/:version/token/app', 'api/:version.Token/getAppToken');


// 订单
// 下单 http://test.com/api/v1/order
Route::post('api/:version/order', 'api/:version.Order/placeOrder');
// 订单列表 http://test.com/api/v1/order/by_user?page=2&size=2
Route::get('api/:version/order/by_user', 'api/:version.Order/getSummaryByUser');
// 当前订单商品详情 http://test.com/api/v1/order/1
Route::get('api/:version/order/:id', 'api/:version.Order/getDetail',[],['id'=>'\d+']);
// CMS管理系统 订单列表
Route::get('api/:version/order/paginate', 'api/:version.Order/getSummary');
// CMS管理员发送订单模板消息
Route::put('api/:version/order/delivery', 'api/:version.Order/delivery');



// 支付 http://test.com/api/v1/pay_order
Route::post('api/:version/pay_order', 'api/:version.Pay/getPreOrder');
// 异步通知
Route::post('api/:version/notify', 'api/:version.Pay/receiveNotify');

