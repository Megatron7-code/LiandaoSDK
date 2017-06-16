<?php
/**
 * Created by PhpStorm.
 * User: megatron
 * Date: 2017/3/14
 * Time: 上午11:48
 * Tips:1.如果部署到线上请求不成功，请检查服务器ip是否在链岛的白名单里。
 */
class Liandao{
    const test_key = '*****************';//上管理平台查看
    const test_sceret = '********';
    const test_dist_code = 'backstage';
    const test_url = 'http://top-api-test.teshehui.com/service';

    const online_key = '***********';
    const online_sceret = '**********';
    const online_dist_code = '*********';
    const online_url = 'http://top-api.teshehui.com/service';

    const flag = 'online';//test代表测试

    /**
     * 生成签名
     * @param $data
     * @return string
     */
    private function generateSignature($data){
        if(self::flag == 'test'){
            $signature = self::test_sceret;
        }else{
            $signature = self::online_sceret;
        }
        foreach ($data as $key=>$val){
            if(is_array($val))
                $val = $this->encode_json($val);
            $signature .= $key.$val;
        }
        $signature .= 'formatjsonplatformsign_methodmd5timestampversion1.0';
        if(self::flag == 'test'){
            $signature .= self::test_sceret;
        }else{
            $signature .= self::online_sceret;
        }
        return  mb_strtoupper(MD5($signature));
    }

    /**
     * 推送订单到链岛
     * @param array $orderInfo
     * @param addressName 收货人名称
     * @param thirdOrderCode 订单号
     * @param addressMobile 手机号
     * @param address 收货地址
     * @param skuCode 对应skuCode
     * @param quantity 数量
     * @param orderRemark 订单备注（可选字段）
     * @return array
     */
    public function pushOrderToLiandao($orderInfo){
        //检测传入的订单信息
        if(empty($orderInfo['addressName']) || (strlen($orderInfo['addressName']) > 100))
            return array('code'=>4000, 'msg'=>'收货人姓名参数错误');
        if(empty($orderInfo['thirdOrderCode']) || (strlen($orderInfo['thirdOrderCode']) > 50))
            return array('code'=>4000, 'msg'=>'订单号错误');
        if(empty($orderInfo['addressMobile']) || (strlen($orderInfo['addressMobile']) != 11))
            return array('code'=>4000, 'msg'=>'收货人手机号错误');
        if(empty($orderInfo['address']) || (strlen($orderInfo['address']) > 512))
            return array('code'=>4000, 'msg'=>'收货地址错误');
        if(empty($orderInfo['skuCode']) || (strlen($orderInfo['skuCode']) > 32))
            return array('code'=>4000, 'msg'=>'skuCode错误');
        if(empty($orderInfo['quantity']) || !is_int($orderInfo['quantity']))
            return array('code'=>4000, 'msg'=>'数量错误');
        $action = 'createOrder';
        return $this->autoPush($action, $orderInfo);
    }

    /**
     * 推送多个sku订单到链岛
     * @param array $orderInfo
     * @param addressName 收货人名称
     * @param thirdOrderCode 订单号
     * @param addressMobile 手机号
     * @param address 收货地址
     * @param orderSkuInfoList sku数组
     * @param skuCode 对应skuCode
     * @param quantity 数量
     * @param orderRemark 订单备注（可选字段）
     * @return array
     */
    public function pushMultiSkuOrderToLiandao($orderInfo){
        //检测传入的订单信息
        if(empty($orderInfo['addressName']) || (strlen($orderInfo['addressName']) > 100))
            return array('code'=>4000, 'msg'=>'收货人姓名参数错误');
        if(empty($orderInfo['thirdOrderCode']) || (strlen($orderInfo['thirdOrderCode']) > 50))
            return array('code'=>4000, 'msg'=>'订单号错误');
        if(empty($orderInfo['addressMobile']) || (strlen($orderInfo['addressMobile']) != 11))
            return array('code'=>4000, 'msg'=>'收货人手机号错误');
        if(empty($orderInfo['address']) || (strlen($orderInfo['address']) > 512))
            return array('code'=>4000, 'msg'=>'收货地址错误');
        if(empty($orderInfo['orderSkuInfoList']) || (count($orderInfo['orderSkuInfoList']) < 0) || !is_array($orderInfo['orderSkuInfoList'])){
            return array('code'=>4000, 'msg'=>'sku列表错误');
        }else{
            foreach ($orderInfo['orderSkuInfoList'] as $val){
                if(empty($val['skuCode']) || (strlen($val['skuCode']) > 32))
                    return array('code'=>4000, 'msg'=>'skuCode错误');
                if(empty($val['quantity']) || !is_int($val['quantity']))
                    return array('code'=>4000, 'msg'=>'数量错误');
            }
        }
        $action = 'batchCreateOrder';
        return $this->autoPush($action, $orderInfo);
    }

    /**
     * 获取订单详情
     * @param string $orderCode 链岛订单号
     * @return array
     */
    public function getOrdersDetails($orderCode){
        if(empty($orderCode))
            return array('code'=>4000, 'msg'=>'orderCode不能为空');
        $action = 'getOrderDetail';
        return $this->autoPush($action, array('orderCode'=>$orderCode));
    }

    /**
     * 批量获取订单详情
     * @param array $orderCodeList 链岛订单号数组
     * @return array
     */
    public function getOrdersListDetails($orderCodeList){
        if(empty($orderCodeList))
            return array('code'=>4000, 'msg'=>'orderCodeList不能为空');
        if(is_array($orderCodeList)){
            foreach ($orderCodeList as $value){
                if(empty($value))
                    return array('code'=>4000, 'msg'=>'orderCode不能为空');
            }
        }else{
            return array('code'=>4000, 'msg'=>'orderCodeList参数错误');
        }
        $action = 'getOrderDetailList';
        return $this->autoPush($action, array('orderCodeList'=>$orderCodeList));
    }

    /**
     * 获取物流轨迹接口
     * @param string $orderCode 链岛订单号
     * @return array
     */
    public function getDeliveryInfo($orderCode){
        if(empty($orderCode))
            return array('code'=>4000, 'msg'=>'orderCode不能为空');
        $action = 'getDeliveryInfo';
        return $this->autoPush($action, array('orderCode'=>$orderCode));
    }

    /**
     * 获取物流信息接口
     * @param $orderCode 链岛订单号
     * @return array
     */
    public function getProductDelivery($orderCode){
        if(empty($orderCode))
            return array('code'=>4000, 'msg'=>'orderCode不能为空');
        $action = 'getProductDelivery';
        return $this->autoPush($action, array('orderCode'=>$orderCode));
    }

    /**
     * 订单支付
     * @param string $orderCode 链岛订单号
     * @return array
     */
    public function orderPay($orderCode){
        if(empty($orderCode))
            return array('code'=>4000, 'msg'=>'orderCode不能为空');
        $action = 'restoreOrderPay';
        return $this->autoPush($action, array('orderCode'=>$orderCode));
    }

    /**
     * 获取商品详情
     * @param $productList
     * @return array
     */
    public function getProductsDetails($productList){
        if(empty($productList) || count($productList) < 0)
            return array('code'=>4000, 'msg'=>'orderCode不能为空');
        $action = 'getProductDetail';
        return $this->autoPush($action, array('productList'=>$productList));
    }

    /**
     * 获取商品列表
     * @param $pageNo 页码  0<pageNo<=100
     * @param $pageSize 每页数据条数  0<pageNo<=1000
     * @return array
     */
    public function getProductList($pageNo, $pageSize){
        if(empty($pageNo) || empty($pageSize))
            return array('code'=>4000, 'msg'=>'pageNo,pageSize不能为空');
        if($pageNo < 1 || $pageNo > 100 || $pageSize < 1 || $pageSize > 1000)
            return array('code'=>4000, 'msg'=>'pageNo,pageSize参数超出范围');
        $action = 'getProductList';
        return $this->autoPush($action, array('pageNo'=>$pageNo, 'pageSize'=>$pageSize));
    }

    /**
     * 获取更新商品列表
     * @param $queryTime
     * @return array
     */
    public function getUpdateProductList($queryTime){
        if(empty($queryTime))
            return array('code'=>4000, 'msg'=>'queryTime不能为空');
        $action = 'getProductListByTime';
        return $this->autoPush($action, array('queryTime'=>$queryTime));
    }

    /**
     * 自动完成推送请求
     * @param $action
     * @param $orderInfo
     * @return array
     */
    private function autoPush($action, $orderInfo){
        //data数据需要按ASCII排序
        $this->sortArrayWithKey($orderInfo);
        //1.组织数据
        if(self::flag == 'test'){
            $url = self::test_url;
            $pushData = array(
                'action'=>$action,
                'app_key'=>self::test_key,
                'dist_code'=>self::test_dist_code,
                'typeId'=>1,
                'data'=>$orderInfo,
            );
        }else{
            $url = self::online_url;
            $pushData = array(
                'action'=>$action,
                'app_key'=>self::online_key,
                'dist_code'=>self::online_dist_code,
                'typeId'=>1,
                'data'=>$orderInfo,
            );
        }
        //2.生成签名
        $pushData['sign'] = $this->generateSignature($pushData);
        //3.发起请求
        $result = $this->httpPost($url, json_encode($pushData));
        if($result['code'] == 0){
            return array('code'=>200, 'data'=>$result['data']);
        }else{
            return array('code'=>4000, 'msg'=>$result['msg']);
        }
    }

    /**
     * 发送http请求
     * @param $url
     * @param $params
     * @return mixed
     */
    private function httpPost($url, $params){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params); // Post提交的数据包
        //curl_setopt($curl, CURLOPT_COOKIEFILE, $GLOBALS['cookie_file']); // 读取上面所储存的Cookie信息
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return json_decode($tmpInfo, true); // 返回数据
    }

    /**
     * 数组按key排序
     * @param $arr
     */
    private function sortArrayWithKey(&$arr){
        if(is_array($arr)){
            foreach ($arr as $key=>$value){
                if($key == 'orderSkuInfoList'){
                    foreach ($value as $k=>$v){
                        ksort($arr['orderSkuInfoList'][$k]);
                    }
                }
            }
            ksort($arr);
        }
    }

    private function encode_json($str) {
        return urldecode(json_encode($this->url_encode($str)));
    }

    /**
     * url中文不转码，兼容php5.3一下版本。高于5.4可以使用json_encode($var, JSON_UNESCAPED_UNICODE);
     * @param $str
     * @return array|int|string
     */
    private function url_encode($str) {
        if(is_array($str)) {
            foreach($str as $key=>$value) {
                $str[urlencode($key)] = $this->url_encode($value);
            }
        }elseif(is_int($str)){
            $str = intval(urlencode($str));
        }else {
            $str = urlencode($str);
        }

        return $str;
    }
}
