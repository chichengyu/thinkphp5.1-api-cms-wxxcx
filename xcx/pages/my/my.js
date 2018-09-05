// pages/my/my.js
import { Address } from '../../untils/address.js';
import { Order } from '../order/order-model.js';
import {My} from './my-model.js';
var address = new Address(),
    order = new Order(),
    my = new My();

Page({

    /**
     * 页面的初始数据
     */
    data: {
        page:1,// 默认第一页的订单列表
        orderArr:[],// 存放订单列表
        isLoadedAll:false,// 是否已加载到最后一页,默认false
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this._load();
    },
    _load:function(){
        // 绑定地址
        address.getAddress(res=>{
            this._bindAddressInfo(res);
        });
        
        // 绑定订单列表
        this._getOrders();
    },
    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        // 绑定用户信息
        this.setData({
            userInfo: wx.getStorageSync('userInfo')
        });

        // 判断是否有新订单，就有重新请求的订单数据
        if (order.hasNewOrder()){
            this.refresh();
        }
    },
    // 有新订单，我的订单页面就重新请求
    refresh:function(){
        // 重新请求时，默认数据初始化
        this.data.orderArr = [];// 存放订单列表
        this._getOrders(()=>{
            order.execSetStorageSync(false);// 将标记是否有新订单的标记更新为false
            this.data.page = 1;// 默认第一页的订单列表
            this.data.isLoadedAll = false;// 是否已加载到最后一页,默认false
        });
    },
    // 点击登陆跳转授权
    toLogin:function(event){
        wx.navigateTo({
            url: '../login/login',
        });
    },
    /**
     * 绑定订单列表
     */
    _getOrders: function (callback) {
        order.getOrders(this.data.page, res => {
            var resData = res.data.data;

            if (resData && resData.length > 0) {
                this.data.orderArr.push.apply(this.data.orderArr, resData);
                this.setData({
                    orderArr: this.data.orderArr
                });
            } else {
                this.data.isLoadedAll = true;
            }
            // 加载完成执行
            callback && callback();
        });
    },
    // 点击付款
    rePay:function(event){
        var orderID = my.getDataSet(event,'id'),
            index = my.getDataSet(event, 'index');// 当前支付订单在订单数组中的下标(用于显示支付状态)
        
        // 拉起支付
        this._exexPay(orderID,index);
    },
    /**
     * 进行支付
     * @param [int] orderID 订单id
     * @param [int] index 当前订单在订单数组的下标(用于显示支付状态)
    */
    _exexPay:function(orderID,index){
        order.execPay(orderID,res=>{
            // 支付的回调函数（返回状态：0=> 商品缺货等原因导致订单不能支付，1=> 支付失败或取消支付，2=> 支付成功）
            if(res >= 0){
                var flag = res == 2;
                if(flag){
                    this.data.orderArr[index].status = 2;
                    this.setData({
                        orderArr: this.data.orderArr
                    });

                    // 跳转支付结果页面
                    wx.navigateTo({
                        url: '../pay-result/pay-result?from=my&flag=' + flag + '&id=' + orderID
                    });
                }
            }else{
                this.showTips('支付失败！','商品已下架或库存不足');
            }
        });
    },
    // 点击地址管理，获取地址
    editAddress:function(){
        var This = this;
        wx.chooseAddress({
            success: function (res) {
                var addressInfo = {
                    name: res.userName,
                    mobile: res.telNumber,
                    totalDetail: address.setAddressInfo(res)
                };

                // 绑定地址到页面
                This._bindAddressInfo(addressInfo);
                // 将获取的地址保存到数据库
                address.submitAddress(res,(flag)=>{
                    if(!flag){
                        This.showTips('操作提示', '地址信息更新失败！');
                    }
                });
            }
        })
    },
    /**
     * 绑定用户地址
     * @param [object] addressInfo 用户信息对象
     */
    _bindAddressInfo:function(addressInfo){
        this.setData({
            addressInfo: addressInfo
        });
    },
    /**
     * 模态提示框
     * @param [string] title 提示标题
     * @param [string] content 提示内容
     */
    showTips:function(title,content){
        wx.showModal({
            title: title,
            content: content,
            success:function(res){}
        })
    },
    // 向上拉刷新加载订单
    onReachBottom:function(){
        if (!this.data.isLoadedAll){
            this.data.page++;
            this._getOrders();
        }
    },
    // 点击订单号进入订单详情
    showOrderDetailInfo:function(event){
        var orderID = order.getDataSet(event,'id');
        wx.navigateTo({
            url: '../order/order?from=order&id=' + orderID
        });
    }
});