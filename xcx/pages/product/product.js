import {Product} from './product-model.js';
import {Cart} from '../cart/cart-model.js';
var product = new Product(),
    cart = new Cart();

Page({

    /**
     * 页面的初始数据
     */
    data: {
        countArry:[1,2,3,4,5,6,7,8,9,10],
        productCount:1,
        productAttrArray:['商品详情','产品参数','售后保障'],
        currentTabsIndex:0
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.data.id = options.id;
        this._load();
    },
    _load:function(){
        product.getDetailInfo(this.data.id,res=>{
            this.setData({
                cartTotalCounts: cart.getCartTotalCounts(),
                product:res
            });
        });
    },
    // 选择商品数量
    bindPickerChange:function(event){
        var index = event.detail.value;
        this.setData({
            productCount: this.data.countArry[index]
        });
    },
    // 商品属性点击切换
    onTabsItemTap:function(event){
        var index = product.getDataSet(event,'index');
        this.setData({
            currentTabsIndex:index
        });
    },
    // 添加购物车
    onAddingCartTap:function(event){
        this.addToCart();
        var counts = this.data.cartTotalCounts + this.data.productCount;
        this.setData({
            cartTotalCounts:counts
        });
    },
    addToCart:function(){
        var tempObj = {},
            keys = ['id','name','main_img_url','price'];
        for (var key in this.data.product){
            if(keys.indexOf(key) >= 0){
                tempObj[key] = this.data.product[key];
            }
        }
        // 添加购物车
        cart.add(tempObj, this.data.productCount);
    },
    // 点击右上角购物车跳转tabBar购物车页面
    onCartTap:function(event){
        wx.switchTab({
            url: '../cart/cart',
        })
    }
})