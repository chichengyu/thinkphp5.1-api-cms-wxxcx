import {Base} from '../../untils/base.js';

class My extends Base{
    constructor(){
        super();
    }

    // 获取微信用户信息 拉起授权
    getUserInfo(callback){
        wx.getUserInfo({
            success: function(res) {
                callback && callback(res);
            },
            fail: function(res) {
                callback && callback(res);
            }
        })
    }
}

export {My};