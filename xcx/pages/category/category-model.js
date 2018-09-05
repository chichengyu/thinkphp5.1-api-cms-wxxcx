import {Base} from '../../untils/base.js';

class Category extends Base{
    constructor(){
        super();
    }

    // 获取所有分类
    getCategoryType(callback){
        this.request({
            url: 'category/all',
            callback:function(res){
                callback && callback(res);
            }
        });
    }
    // 获取指定分类下的所有商品
    getProductsCategory(cateID,callback){
        this.request({
            url: 'product/by_category?cateId=' + cateID,
            callback:function(res){
                callback && callback(res);
            }
        });
    }
}
export {Category};