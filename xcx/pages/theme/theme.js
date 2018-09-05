// pages/theme/theme.js
import {Theme} from 'theme-model.js';
var theme = new Theme();

Page({

    /**
     * 页面的初始数据
     */
    data: {

    },

    /**
     * 生命周期函数--监听页面初次渲染完成
    */
    onReady: function () {
        wx.setNavigationBarTitle({
            title: this.data.name
        })
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this.data.id = options.id;
        this.data.name = options.name;
        this._load();
    },
    _load:function(){
        // themeID 就是指定专题的id 点击时
        theme.getProductsData(this.data.id,res=>{
            this.setData({
                themeInfo:res
            });
        });
    },
    // 专题下的商品点击跳转详情页
    onProductsItemTap:function(){
        wx.navigateTo({
            url: '../product/product?id=' + this.data.id
        })
    }
})