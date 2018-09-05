// pages/cart/cart.js
import { Category } from './category-model.js';
var category = new Category();

Page({

    /**
     * 页面的初始数据
     */
    data: {
        categoryItemIndex:0
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        this._load();
    },
    _load: function () {
        category.getCategoryType(res => {
            this.setData({
                categoryTypeArr: res
            });
            var data = res;
            // 默认显示第一个分类下的所有商品
            category.getProductsCategory(data[0].id,res=>{
                var  resData = {
                    products:res,
                    title: data[0].name,
                    topImgUrl: data[0].images.url
                }
                this.setData({
                    categoryProducts: resData
                });
            });
        });
    },
    // 左侧分类导航点击切换
    onCategoryItemTap: function(event) {
        var categoryIndex = category.getDataSet(event, 'index'),
            cateTypeID = category.getDataSet(event, 'typeid');

        // 当前分类下的所有商品
        category.getProductsCategory(cateTypeID, res => {
            var resData = {
                products: res,
                title: this.data.categoryTypeArr[categoryIndex].name,
                topImgUrl: this.data.categoryTypeArr[categoryIndex].images.url
            }
            this.setData({
                categoryItemIndex: categoryIndex,// 左侧导航
                categoryProducts: resData// 右侧商品
            });
        });
    },
    // 点击商品跳转商品详情
    onProductsItemTap:function(event){
        var id = category.getDataSet(event,'id');
        wx.navigateTo({
            url: '../product/product?id=' + id
        })
    }
})