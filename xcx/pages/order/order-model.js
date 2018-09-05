import {Base} from '../../untils/base.js';

class Order extends Base{
    constructor(){
        super();
        this._storageKeyName = 'newOrder';// 此状态用于 '我的' 页面时会用到，用于判断是否有新订单，就自动刷新我的订单页面
    }
    /**
     * 下单请求
     * @param [二维array] data 待付款的商品信息数组
     * @param [function] callback 下单的回调函数
     */
    doOrder(data,callback){
        var This = this;
        this.request({
            url: 'order',
            method: 'POST',
            data: {products:data},
            callback:function(res){
                // 此时新添加的订单，更新属性标记状态，用于判断是否有新订单，有，那么就自动刷新我的订单页面
                This.execSetStorageSync(true);
                callback && callback(res);
            }
        });
    }
    /**
     * 根据订单id获取订单详细信息
     * @param [string|number] orderID 订单Id
     * @param [function] callback 回调函数
     */
    getOrderInfoById(orderID,callback){
        this.request({
            url: 'order/' + orderID,
            callback:function(res){
                callback && callback(res);
            }
        });
    }
    /**
     * 拉起支付
     * @param [string|int] orderID 待支付的订单号
     * @param [function] callback 支付的回调函数（返回状态：0=>商品缺货等原因导致订单不能支付，1=>支付失败或取消支付，2=>支付成功）
     */
    execPay(orderID,callback){
        this.request({
            url: 'pay_order',
            method: 'POST',
            data:{id:orderID},
            callback:function(data){
                // 是否可以进行支付
                if (data.timeStamp){
                    wx.requestPayment({
                        timeStamp: data.timeStamp.toString(),
                        nonceStr: data.nonceStr,
                        package: data.package,
                        signType: data.signType,
                        paySign: data.paySign,
                        success:function(res){
                            // 调用支付成功
                            callback && callback(2);
                        },
                        fail:function(err){
                            // 调用支付失败或用户取消支付
                            callback && callback(1);
                        }
                    });
                }else{
                    // 商品缺货或商品已下架
                    callback && callback(0);
                }
            }
        });
    }
    /**
     * 获取所有订单列表
     * @param [string|number] page 第几页
     * @param [function] callback 回调函数
     */
    getOrders(page,callback){
        this.request({
            url: 'order/by_user',
            data:{page:page},
            callback:function(res){
                callback && callback(res);
            }
        });
    }
    /**
     * 缓存更新
     * @param [void] data 缓存的数据
     */
    execSetStorageSync(data) {
        wx.setStorageSync(this._storageKeyName, data);
    }
    // 判断是否有新添加了订单
    hasNewOrder(){
        var flag = wx.getStorageSync(this._storageKeyName);
        return flag == true;
    }
}

export {Order};