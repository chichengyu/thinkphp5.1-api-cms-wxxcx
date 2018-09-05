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

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
   'img_prifix' => httpHost().$_SERVER['HTTP_HOST'].'/public/static/image',

   // 一串随机的字符,用于令牌token加密
   'salt' => 'CHhsICsAscS',
   // token过期时间 即 缓存数据过期时间
   'token_expire_in' => 7200,

   // wx微信
   'wx' => array(
   		'app_id' 		=> 'wx993172f7da9acf1f',
   		'app_secret'	=> 'e3c87b4723aed071cfdfb846baf1e98c',
   		// 注意 %s 是占位符
   		'login_url' 	=> 'https://api.weixin.qq.com/sns/jscode2session?'.'appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
         // 异步通知地址
         'pay_back_url' => httpHost().$_SERVER['HTTP_HOST'].'/api/v1/notify',
         // 发送模板消息时，获取access_token的请求地址
         'access_token_url' => 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s',
   ),
];
