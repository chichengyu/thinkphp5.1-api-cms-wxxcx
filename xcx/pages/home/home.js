// pages/home/home.js
import {Home} from 'home-model.js';
var home = new Home();

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
    this._load();
    },
    _load:function(){
    // 轮播 
    var id = 1;
    home.getBannerData(id,res=>{
        this.setData({
            'banner':res
        });
    });
    // 精选主题
    home.getThemeData(res => {
        this.setData({
            'theme': res
        });
    });
    // 最近新品
    home.getProductsData(res=>{
        this.setData({
            'products':res
        });
    });
    },
    // 轮播与最近新品点击跳转与传参
    onProductsItemTap:function(event){
        var id = home.getDataSet(event, 'id');
        wx.navigateTo({
            url: '../product/product?id=' + id,
        })
    },
    // 精选主题点击跳转与传参
    onThemeItemTap:function(event){
        var id = home.getDataSet(event, 'id'),
            name = home.getDataSet(event,'name');
        wx.navigateTo({
            url: '../theme/theme?id=' + id + '&name=' + name,
        })
    }
})