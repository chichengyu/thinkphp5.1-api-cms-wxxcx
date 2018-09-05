import {Config} from './config.js';

class Token{
    constructor(){
        // 请求token地址
        this.tokenUrl = Config.resUrl + 'token/user';
        // 效验token令牌地址
        this._verifyUrl = Config.resUrl + 'token/verify';
    }

    // token令牌效验
    verify(){
        // 获取缓存的token令牌
        var token = wx.getStorageSync('token');
        if(token){
            // 存在进行token令牌效验
            this._verifyToken(token);
        }else{
            // 不存在token令牌，请求服务器，获取token令牌，并存入缓存
            this.getTokenFromServer();
        }
    }
    /**
     * 效验token令牌
     * @param [string] token 本地缓存token令牌码
     */
    _verifyToken(token){
        var This = this;
        wx.request({
            url: This._verifyUrl,
            method:'POST',
            data:{
                token: token
            },
            success:function(res){
                // 判断toekn令牌效验是否通过
                if(!res.data.isValid){
                    // 效验不通过，重新请求服务器获取token令牌
                    This.getTokenFromServer();
                }
            }
        })
    }
    /**
     * 请求服务器，获取token令牌并存入缓存
     * @param [function] callback 得到token令牌后的回调函数
     */
    getTokenFromServer(callback){
        var This =  this;
        wx.login({
            success:function(res){
                // 用code码向服务器请求token令牌
                wx.request({
                    url: This.tokenUrl,
                    method:'POST',
                    data:{
                        code:res.code
                    },
                    success:function(res){
                        // 将得到的token令牌存入缓存
                        wx.setStorageSync('token',res.data.token);
                        // 将得到的token令牌返回出去
                        callback && callback(res.data.token);
                    }
                })
            }
        })
    }
}
export {Token};