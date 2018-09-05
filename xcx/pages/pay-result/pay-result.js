// pages/pay-result/pay-result.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
  
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
      this.setData({
          payResult:options.flag,
          orderID:options.id,
          from:options.from
      });
  },
  // 查看订单点击
  viewOrder:function(event){
      if(this.data.from == 'my'){
          // 从我的订单进行支付的
          wx.navigateTo({
              url: '../order/order?from=order&id=' + this.data.orderID
          });
      }else{
          // 返回订单页面(从购物车进行支付)
          wx.navigateBack({
              delta: 1
          });
      }
  }
})