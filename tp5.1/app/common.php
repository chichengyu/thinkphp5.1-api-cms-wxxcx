<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/*
	获取协议http https
	return [string] http/https协议
*/
function httpHost()
{
	return $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
}

/*
	GET请求
	$url 	  [string]	请求地址
	$httpCode [number] 	返回状态码
*/
function curl_get($url,&$httpCode=0)
{
	$ch = curl_init();
	// 需要获取的URL地址
	curl_setopt($ch,CURLOPT_URL,$url);
	// CURLOPT_RETURNTRANSFER 将获取的信息以文件流的形式返回，而不是直接输出。 
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

	// 不做证书效验，部署在linux环境下改为true;
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	// 在发起连接前等待的时间，如果设置为0，则无限等待。
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);

	// 执行
	$file_contents = curl_exec($ch);
	// 获取一个cURL连接资源句柄的信息,
	// CURLINFO_HTTP_CODE - 最后一个收到的HTTP代码 
	$httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);

	curl_close($ch);
	return $file_contents;
}

/*
	POST请求
	$url 	 [string]	请求地址
	$rawData [array]	发送的数据
*/
function curl_post_raw($url,$rawData)
{
	$ch = curl_init();
	// 需要获取的URL地址
	curl_setopt($ch,CURLOPT_URL,$url);
	// 请求头
	curl_setopt($ch,CURLOPT_HEADER,0);
	// CURLOPT_RETURNTRANSFER 将获取的信息以文件流的形式返回，而不是直接输出。 
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	// 发送常规的post请求
	curl_setopt($ch,CURLOPT_POST,1);
	// 不做证书效验，部署在linux环境下改为true;
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
	// 在发起连接前等待的时间，如果设置为0，则无限等待。
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10);
	// 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串类似'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组。如果value是一个数组，Content-Type头将会被设置成multipart/form-data。  
	curl_setopt($ch,CURLOPT_POSTFIELDS,$rawData);
	// 一个用来设置HTTP头字段的数组。使用如下的形式的数组进行设置： array('Content-type: text/plain', 'Content-length: 100') 
	curl_setopt($ch,CURLOPT_HTTPHEADER,array('Content-type:text'));

	// 执行
	$file_contents = curl_exec($ch);
	curl_close($ch);
	return $file_contents;
}

/*
	获取指定长度的随机字符串
	$length [number] 指定长度
*/
function getRandChars($length){
    $str="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $max = strlen($str) - 1;
    $key = "";
    for($i=0;$i<$length;$i++)
    {
        $key .= $str[mt_rand(0,$max)];//生成php随机数
    }
    return $key;
 }