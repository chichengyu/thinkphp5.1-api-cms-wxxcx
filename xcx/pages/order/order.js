// pages/order/order.js
import {Cart} from "../cart/cart-model.js";
import {Address} from '../../untils/address.js';
import {Order} from './order-model.js';
var cart = new Cart(),
    address = new Address(),
    order = new Order();

Page({

    /**
     * 页面的初始数据
     */
    data: {
        orderID:null// 下单成功的订单ID,默认不存在
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function(options) {
        var from = options.from;
        if(from == 'cart'){
            this._fromCart(options);
        }else{
            this._fromOrder(options.id);
        }
    },
    /**
     * 生命周期函数--监听页面显示
     * 进入支付结果页面之后再返回订单页面，需要重新渲染
     * 下单后，支付成功或者失败后，点左上角返回时能够更新订单状态 所以放在onshow中
     */
    onShow: function () {
        // 去支付时，存的orderID订单ID
        if (this.data.orderID) {
            this._fromOrder(this.data.orderID);
        }
    },

    /*
        从购物车进入订单页
        @param [string] account 商品总价(从购物车页面过来携带的参数)
    */
    _fromCart: function (options){
        var account = options.account,
            productsArr = cart.getCartDataFromLocal(true);// 获取选中待付款商品
        this.data.fromCartFlag = options.from == 'cart',// 是否从购物车进入订单支付      
        this.setData({
            productsArr: productsArr,
            account: account,
            orderStatus: 0
        });

        // 绑定用户地址到页面
        address.getAddress(res => {
            this._bindAddressInfo(res);
        });
    },
    /*
        从我的订单进入订单页
        @param [string] orderID 订单id(从我的订单页面过来携带的参数)
    */
    _fromOrder:function(orderID){
        this.data.orderID = orderID;     
        var This = this;
        if (orderID) {
            //下单后，去支付,支付成功或者失败后，点左上角返回时能够更新订单状态 所以放在onshow中
            order.getOrderInfoById(orderID, res => {
                This.setData({
                    orderStatus: res.status,
                    productsArr: res.snap_items,
                    account: res.total_price,
                    basicInfo: {
                        orderTime: res.create_time,
                        orderNo: res.order_no
                    }
                });
                // 绑定地址
                var addressInfo = res.snap_address;
                addressInfo.totalDetail = address.setAddressInfo(addressInfo);
                This._bindAddressInfo(addressInfo);
            })
        }
    },
    // 编辑地址
    editAddress: function() {
        var This = this;
        // 微信原生地址API
        wx.chooseAddress({
            success: function(res) {
                var addressInfo = {
                    name: res.userName,
                    mobile: res.telNumber,
                    totalDetail: address.setAddressInfo(res)
                }

                // 绑定地址信息到页面
                This._bindAddressInfo(addressInfo);
                // 保存地址到数据库(服务器)
                address.submitAddress(res,flag=>{
                    if(!flag){
                        This.ShowTips('操作提示','地址信息更新失败！');
                    }
                });
            }
        })
    },
    /*
        绑定地址信息
        @param [object] addressInfo 传递处理好的地址信息数据对象
    */
    _bindAddressInfo:function(addressInfo){
        this.setData({
            addressInfo: addressInfo
        });
    },
    // 点击订单页面的去支付按钮拉起支付支付(不分购物车与我的订单，因为订单支付页是同一个页面)
    pay: function (event) {
        // 判断是否有地址
        if (!this.data.addressInfo) {
            this.ShowTips('下单提示','请填写你的收货地址');
        }
        // 判断是否从购物车页面进入订单支付
        if (this.data.fromCartFlag) {
            // 从购物车进入订单支付(即第一次支付)
            this._firstTimePay();
        } else {
            // 从我的订单进入订单支付
            this._oneMoresTimePay();
        }
    },
    // 购物车进入支付页面支付(第一次支付)
    _firstTimePay:function(){
        // 处理下单的商品数据
        var orderInfo = [],
            productInfo = this.data.productsArr;

        for(var i=0;i < productInfo.length;i++){
            orderInfo.push({
                product_id: productInfo[i].id,
                count: productInfo[i].counts
            });
        }
        // 支付分两步：1.生成订单号 2.根据订单号进行支付
        // 发送下单请求,生成订单号
        order.doOrder(orderInfo,res=>{
            if(res.pass){
                // 下单成功
                var orderID = res.order_id;
                this.data.orderID = orderID;
                // 更新标记状态(从购物车页面进入订单支付的)
                this.data.fromCartFlag = false;

                // 开始支付
                this._execPay(orderID);
            }else{
                // 下单失败
                this._orderFail(res);
            }
        });
    },
    // 从我的订单进入订单支付（再次支付）
    _oneMoresTimePay:function(){
        this._execPay(this.data.orderID);
    },
    /*
        下单失败，提示那些商品库存不足
        @param [object] res 商品信息对象
    */
    _orderFail:function(res){
        var productsArr = res.pStatusArray,
            nameArr = [],
            name = '';

        // 找出库存不足的商品名称
        for(var i=0;i < productsArr.length;i++){
            // 不存不够的商品
            if (!productsArr[i].haveStock){
                name = productsArr[i].name;
                if(name.length > 15){
                    name = name.substr(0,12) + '...';
                }
                nameArr.push(name);
                // 模态提示框每次只显示两个商品信息
                if(nameArr.length > 2){
                    break;
                }
            }
        }
        var str = nameArr.join('、');
        if(nameArr.length > 2){
            str += ' 等';
        }
        str += ' 缺货';
        // 模态提示框
        wx.showModal({
            title: '下单提示',
            content: str,
            showCancel:false,
            success:function(res){}
        })
    },
    /*
        发送支付
        @param [string] orderID 订单号
    */
    _execPay:function(orderID){
        order.execPay(orderID,res=>{
            //（返回状态：0=> 商品缺货等原因导致订单不能支付，1=> 支付失败或取消支付，2=> 支付成功）
            // res!=0，表示商品有货
            if(res != 0){
                // 只要有货就下单成功可以支付，此时不管支付结果成功还是失败，既然下单了，就生成了订单号，就应该把订单商品从购物车页面移除
                this.deleteProducts();// 从购物车移除商品

                var flag = (res == 2);
                // 跳转到支付结果页面,根据flag判断显示成功还是失败页面
                wx.navigateTo({
                    url: '../pay-result/pay-result?id=' + orderID + '&flag=' + flag + '&from=order',
                })
            }
        });
    },
    /*
        将已经下单的商品从购物车删除
    */
    deleteProducts:function(){
        var ids = [],
            products = this.data.productsArr;
        for(var i=0;i < products.length;i++){
            ids.push(products[i].id);
        }
        // 移除缓存的购物车中的商品
        cart.delete(ids);
    },
    /*
        保存地址失败的弹窗信息  ​显示模态弹窗
        @param [string] title 提示的标题
        @param [string] content 提示的内容
        @param [bool] flag 是否跳转到 => "我的"页面
    */
    ShowTips:function(title,content,flag){
        // ​显示模态弹窗
        wx.showModal({
            title:title,
            content:content,
            success:function(res){
                if(flag){
                    // 跳转到tabBar的"我的"页面
                    wx.switchTab({
                        url: '../my/my',
                    })
                }
            }
        });
    },
    // 点击订单支付页面商品进入商品详情页
    onProductTap:function(event){
        var productID = order.getDataSet(event,'id');
        wx.navigateTo({
            url: '../product/product?id=' + productID
        });
    }
});