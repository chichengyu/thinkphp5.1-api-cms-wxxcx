import {Base} from '../../untils/base.js';

class Home extends Base{
    constructor(){
        super();
    }
    
    // 轮播
    getBannerData(id,callback){
        this.request({
            url: 'banner/' + id,
            callback: function (res) {
                callback && callback(res.banner_items);
            }
        });
    }
    // 精选主题
    getThemeData(callback) {
        this.request({
            url: 'theme',
            callback: function (res) {
                callback && callback(res);
            }
        });
    }
    // 最近新品
    getProductsData(callback){
        this.request({
            url: 'product/recent?count=15',
            callback: function(res) {
                callback && callback(res);
            }
        });
    }
}
export {Home};