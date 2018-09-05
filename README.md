# think5.1-api-cms-wxxcx

  API使用的是thinkphp5.1,cms是手动的写的简易,xcx是小程序

  优化：
	1.字段缓存
		每次执行数据库查询时。都会先执行一套获取所有字段的sql语句
			show columns from 表名
		这样大大影响了数据库的性能，生成字段缓存cmd命令：php think optimize:schema
	2.路由缓存
		生成路由缓存cmd命令：php think optimize:route


  注意：从购物车页面面进入订单支付页面与从我的订单进入支付页面是不同的
	1.从购物车页面进入支付页面，此时还没有生成订单号(即没有请求生成订单号的接口)，而从我的当但进入订单支付页面，已经生成了订单，只是还没有进行支付而已