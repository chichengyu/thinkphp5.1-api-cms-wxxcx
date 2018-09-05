import {Cart} from './cart-model.js';
var cart = new Cart();

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

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        // 获取购物车所有商品
        var cartData = cart.getCartDataFromLocal();
        // 计算购物车选中商品的总数量与总价格
        var cal = this._calcTotalAccountAndCounts(cartData);

        this.setData({
            selectedCounts: cal.selectedCounts,
            selectedTypeCounts: cal.selectedTypeCounts,
            account: cal.account,
            cartData:cartData
        });
    },
    /**
    * 生命周期函数--监听页面隐藏
    * 如 navigateTo 或底部 tab 切换到其他页面，小程序切入后台等会执行。
    */
    onHide: function () {
        // 当前切换到其他页面时，再切换回购物车页面时，商品选中状态应该不变
        // 重新设置缓存
        cart.execSetStorageSync(this.data.cartData);
    },
    /*
        计算购物车选中商品的总数量与总价格
        @param [array] cartData 缓存的购物车所有商品数组
    */
    _calcTotalAccountAndCounts(cartData){
        var account = 0,// 选中的商品总价格
            selectedCounts = 0,// 选中的商品的总数量
            selectedTypeCounts = 0;// 选中的商品的类型有几种(用于全选按钮的判断)

        var multiple = 100;// 解决js计算浮点数时的偏差
        for(var i=0,leng=cartData.length;i < leng;i++){
            if (cartData[i].selectStatus){
                account += (cartData[i].counts * multiple) * (cartData[i].price * multiple);
                selectedCounts += cartData[i].counts;
                selectedTypeCounts++;
            }
        }
        return {
            selectedCounts: selectedCounts,
            selectedTypeCounts: selectedTypeCounts,
            account: account/(multiple * multiple)
        }
    },
    // 购物车单个商品的选中与取消状态,然后重新绑定数据
    toggleSelect:function(event){
        var id = cart.getDataSet(event,'id'),
            status = cart.getDataSet(event,'status');

        // 根据商品id找到购物车数组中的该商品的下标
        var index = this._getProductIndexById(id);
        // 更改商品的选中状态
        this.data.cartData[index].selectStatus = !status;
        // 重新计算选中商品总数与总价格，并重新绑定数据
        this._resetCartData();
    },
    // 全选按钮的点击改变状态
    toggleSelectAll:function(event){
        var status = cart.getDataSet(event,'status') == 'true';
        var data = this.data.cartData;

        for (var i=0,leng=data.length;i < leng;i++){
            data[i].selectStatus = !status;
        }
        // 选中状态更新后,并重新绑定数据
        this._resetCartData();        
    },
    /*
        根据商品id找到商品在购物车缓存数组中的下表
        @param [number|string] id 商品id
        return index 返回下标
    */
    _getProductIndexById(id){
        var data = this.data.cartData;
        for(var i=0,leng=data.length;i < leng;i++){
            if(data[i].id == id){
                return i;
            }
        }
    },
    // 选中状态更新后,重新计算购物车选中商品总数与总价格，并重新绑定数据
    _resetCartData:function(){
        var newData = this._calcTotalAccountAndCounts(this.data.cartData);
        this.setData({
            selectedCounts: newData.selectedCounts,
            selectedTypeCounts: newData.selectedTypeCounts,
            account: newData.account,
            cartData: this.data.cartData
        });
    },
    // 购物车页面的商品数量加减
    changeCounts:function(event){
        var id = cart.getDataSet(event,'id'),// 商品id
            index = this._getProductIndexById(id),// 商品下表
            type = cart.getDataSet(event,'type');// 判断点击的是+还是-
        
        var counts = 1;// 用于页面计算修改的商品数量
        if(type == 'add'){
            // +
            cart.addCounts(id);// 缓存数组中商品数量+
        }else{
            // -
            cart.cutCounts(id);// 缓存数组中商品数量-
            counts = -1;
        }
        // 页面绑定数量加减
        this.data.cartData[index].counts += counts;
        // 更新页面绑定数据
        this._resetCartData();
    },
    // 删除购物车商品
    delete:function(event){
        var id = cart.getDataSet(event, 'id'),// 商品id
            index = this._getProductIndexById(id);// 商品下表
        
        // 删除页面中的商品
        this.data.cartData.splice(index,1);// 从数组中删除某一个
        // 删除缓存中的商品
        cart.delete(id);
        // 更新页面数据
        this._resetCartData();
    },
    // 添加商品缩略图，进入商品详情页
    onProductTap:function(event){
        var id = cart.getDataSet(event,'id');
        wx.navigateTo({
            url: '../product/product?id=' + id,
        })
    },
    // 点击下单或箭头跳转订单页面
    submitOrder:function(event){
        wx.navigateTo({
            url: '../order/order?account=' + this.data.account + '&from=cart'// 标识从购物车页面进入订单
        })
    }
})