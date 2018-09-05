import { Base } from './base.js';

class Address extends Base{
    constructor(){
        super();
    }

    /**
     * 获取用户地址
     * @param [function] callback 获取用户地址的回调函数
     */
    getAddress(callback){
        var This = this;
        this.request({
            url: 'address',
            callback:function(res){
                // 拼接详细地址
                res.totalDetail = This.setAddressInfo(res);
                callback && callback(res);
            }
        });
    }
    /**
     * 拼接详细地址
     * @param [object] res 信息对象
     * 信息对象有两种来源：1.微信返回
     *                   2.我们自己服务器返回的收货信息
     * return [string] 返回拼接好的详细地址
     */
    setAddressInfo(res){
        var province = res.provinceName || res.province,// 省
            city = res.cityName || res.city,// 市
            country = res.countyName || res.country,// 区
            detail = res.detailInfo || res.detail;// 详细地址
        var totalDetail = city + country + detail;
        // 判断是否是直辖市，直辖市就不需要拼接省,不是就要拼接省
        if (!this._isCenterCity(city)){
            totalDetail = province + totalDetail;
        }
        return totalDetail;
    }
    /**
     * 判断是否是直辖市
     * @param [string] cityName 市名称
     */
    _isCenterCity(cityName){
        var centerCitys = ['北京市','天津市','上海市','重庆市'],
            flag = centerCitys.indexOf(cityName) >= 0;
        return flag;
    }
    /**
     * 保存地址到数据库(服务器)
     * @param [oject] data 地址信息对象
     * @param [function] callback 回调函数
     */
    submitAddress(data,callback){
        data = this._setUpAddress(data);
        this.request({
            url:'address',
            method:'POST',
            data:data,
            callback:function(res){
                callback && callback(true,res);
            },
            eCallback: function (res) {
                callback && callback(false,res);
            }
        });
    }
    /**
     * 处理要保存到数据库的地址信息
     * @param [object] res 信息对象
     */
    _setUpAddress(res){
        var formData = {
            name: res.userName,
            mobile: res.telNumber,
            province: res.provinceName,
            city: res.cityName,
            country: res.countyName,
            detail: res.detailInfo
        }
        return formData;
    }
}
export {Address};