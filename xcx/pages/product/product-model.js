import {Base} from '../../untils/base.js';

class Product extends Base{
    constructor(){
        super();
    }

    // 商品详情
    getDetailInfo(id,callback){
        this.request({
            url: 'product/' + id,
            callback:function (res) {
                callback && callback(res);
            }
        });
    }
}
export {Product};