<?php 
namespace app\api\controller;
use think\Controller;
use app\api\service\Token as TokenService;

class BaseController extends Controller
{
	// 权限验证
	// 用户和CMS管理员都可以访问
	protected function checkPrimaryScope()
	{
		TokenService::needPrimaryScope();
	}
	// 只有用户可以访问 如：订单
	protected function checkExclusiveScope()
	{
		TokenService::needExclusiveScope();
	}
	// 只有CMS管理员可以访问，如：发货后台系统
	protected function needSuperScope()
	{
		TokenService::needSuperScope();
	}
}
