import { Config } from './config.js';
import {Token} from './token.js';

class Base{
    constructor(){
        this.baseRequestUrl = Config.resUrl;
    }
    
    /**
     * 请求
     * @param [object] params  请求的参数对象
     * @param [bool] noRefetch 是否重发请求
     */
    request(params,noRefetch){
        var url = this.baseRequestUrl + params.url,
            This = this;
        wx.request({
            url: url,
            data: params.data || null,
            header: {
                'Content-type':'application/json',
                'token':wx.getStorageSync('token')
            },
            method: params.method || 'GET',
            success: function(res) {
                // 判断返回资源是否成功
                var status = res.statusCode.toString(),
                    startCode = status.charAt(0);
                
                if(startCode == '2'){
                    // 请求成功
                    params.callback && params.callback(res.data);
                }else{
                    // 判断token令牌是否过期或无效
                    if(status == '401'){
                        // 无效，再次向服务器请求获取token令牌后，才能发送请求
                        // 注意：noRefetch参数是为了防止一直不停的发送请求
                        if (!noRefetch){
                            This._refetch(params);
                        }
                    }
                    // 当前处于不再重发的状态时再返回
                    if(noRefetch){
                        params.eCallback && params.eCallback(res.data);
                    }
                }
            },
            fail: function(err) {
                console.log('错误',res);
            }
        })
    }
    /**
     * 重新获取token令牌后，再次发送请求
     * @param [object] params 请求的参数对象
    */
    _refetch(params){
        var token = new Token();
        token.getTokenFromServer((token)=>{
            // 得到tokend令牌后，再次发送请求
            this.request(params,true);
        });
    }
    // 获取标签上的属性data数据
    getDataSet(event,key){
        return event.currentTarget.dataset[key];
    }
}
export {Base};