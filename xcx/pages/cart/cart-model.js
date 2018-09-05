import {Base} from '../../untils/base.js';

class Cart extends Base{
    constructor(){
        super();
        // 购物车缓存名称
        this._storageKeyName = 'cart';
    }

    /*
        添加购物车：
            1.如果之前没有这件商品，就直接添加，数量为counts
            2.如有有，则只将相应的数量 + counts
        @param [object] item    当前待添加商品
        @param [number] counts  当前待添加商品数量
    */
    add(item,counts){
        // 1.获取本地缓存的购物车信息
        var cartData = this.getCartDataFromLocal();
        // 2.检测购物车是否有这件商品
        var isHasInfo = this._isHasThatOne(item.id,cartData);
        if(isHasInfo.index == -1){
            item.counts = counts;
            item.selectStatus = true;// 添加商品到购物车后，默认为选中状态
            cartData.push(item);
        }else{
            cartData[isHasInfo.index].counts += counts;
        }
        // 缓存设置
        wx.setStorageSync(this._storageKeyName, cartData);
    }
    /*
        计算购物车所有商品的总数量 与 计算购物车页面选中的商品总数量
        @param [bool] flag 购物车商品选中状态 默认计算购物车全部商品数量
        false  默认计算购物车全部商品数量 
        true   计算购物车页面选中商品总数量
     */
    getCartTotalCounts(flag){
        var cartData = this.getCartDataFromLocal(),
            counts = 0;
        for(var i=0,leng=cartData.length;i < leng;i++){
            if (flag){
                if(cartData[i].selectStatus){
                    counts += cartData[i].counts;
                }
            }else{
                counts += cartData[i].counts;
            }
        }
        return counts;
    }
    /*
        设置缓存(用于tabBar切换页面时更新缓存中商品的选中状态)
        @param [object] data 页面选中状态改变后的购物粗数组
    */
    execSetStorageSync(data) {
        wx.setStorageSync(this._storageKeyName, data);
    }
    /*
        获取缓存的购物车数据
        @param [bool] flag 是否过滤未选中的商品
    */
    getCartDataFromLocal(flag){
        var res = wx.getStorageSync(this._storageKeyName);
        if(!res){
            res = [];
        }
        if(flag){
            var newData = [];
            for(var i=0;i < res.length;i++){
                if(res[i].selectStatus){
                    newData.push(res[i]);
                }
            }
            res = newData;
        }
        return res;
    }
    /*
        检测购物车中是否有这件商品
        @param [number] id   商品id
        @param [array]  arr  当前购物车所有商品
        return [object] 返回该商品信息
    */
    _isHasThatOne(id,arr){
        // 默认没有这件商品
        var result = {
            index:-1
        };
        for(var i=0,leng=arr.length;i < leng;i++){
            if(id == arr[i].id){
                result = {
                    index:i,
                    data:arr[i]
                };
                break;
            }
        }
        return result;
    }
    /*
        计算购物车页面商品数量的加减
        @param [number|string] id 商品id
        @param [number] counts 每点击一次，要加减多少的数
    */
    _changeCounts(id,counts){
        var cartData = this.getCartDataFromLocal(),
            hasInfo = this._isHasThatOne(id,cartData);//获取当前点击商品信息

        if(hasInfo.index != -1){
            if (hasInfo.data.counts > 1) {
                cartData[hasInfo.index].counts += counts;
            }
        }
        // 更新缓存
        wx.setStorageSync(this._storageKeyName, cartData);
    }
    /*
        购物车加商品数量
        @param [number|string] id 商品id
    */
    addCounts(id){
        // 此处写死，每次点击只能加1
        this._changeCounts(id,1);
    }
    /*
        购物车减商品数量
        @param [number|string] id 商品id
    */
    cutCounts(id) {
        // 此处写死，每次点击只能减1
        this._changeCounts(id, -1);
    }
    /*
        删除购物车某一个商品
        @param [number|array] ids 商品id单个或数组
    */
    delete(ids) {
        // 可以从缓存删除多个商品
        if(!(ids instanceof Array)){
            ids = [ids];// 单个数转成数组
        }
        var cartData = this.getCartDataFromLocal();
        for(var i=0;i < ids.length;i++){
            var hasInfo = this._isHasThatOne(ids[i], cartData);      
            // 判断缓存中是否有这件商品
            if(hasInfo.index != -1){
                // 有就删除
                cartData.splice(hasInfo.index,1);// 从缓存中删除商品
            }
        }
        // 更新缓存（重新设置缓存）
        wx.setStorageSync(this._storageKeyName, cartData);
    }
}
export {Cart};