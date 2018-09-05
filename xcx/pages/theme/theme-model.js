import {Base} from '../../untils/base.js';

class Theme extends Base{
    constructor(){
        super();
    }

    // 指定专题下的所有商品
    getProductsData(themeID,callback){
        this.request({
            url: 'theme/' + themeID,
            callback:function(res){
                callback && callback(res);
            }
        });
    }
}
export {Theme};