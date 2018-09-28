<?php

namespace Home\Controller;

use Vendor\Page;
use Vendor\Wxpay;

class IndexController extends ComController
{
    public function index()
    {
        echo date('Y-m-d H:i:s', time());
        die;
        $this->display();
    }

    //接口密钥
    private function key($openid)
    {
        return md5($openid . 'conent_f');
    }

//    获取发票信息
    public function receipt()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info, true);
            die;
        }
        //如果存在相同的 直接数据库查找
        $whe['invoiceDataCode'] = $res['invoiceCode'];
        $whe['invoiceNumber'] = $res['invoiceNumber'];
        $whe['billTime'] = $res['billTime'];
        $whe['checkCode'] = $res['checkCode'];
        //校验码
        $whe['openid'] = $res['openid'];
        $lists = M('receipt')->field("id,invoiceDataCode,invoiceNumber,invoiceTypeName,invoiceTypeCode,billingTime,checkDate,purchaserName,taxpayerBankAccount,checkCode,taxpayerNumber,salesName,salesTaxpayerBankAccount,totalTaxSum,totalTaxNum,totalAmount")->where($whe)->find();
        if ($lists) {
            $lists['checkDate'] = date('Y-m-d', time());
            $data1['checkDate'] = date('Y-m-d', time());
            M('receipt')->data($data1)->where($whe)->save();
            $info['status'] = 1;
            $info['info'] = $lists;
            echo json_encode($info);
            die;
        } else {
            $nums = M('member')->where("openid='" . $res['openid'] . "' ")->getfield('nums');
            if ($nums > 0) {
                $data['invoiceCode'] = $res['invoiceCode'];
                //发票代码
                $data['invoiceNumber'] = $res['invoiceNumber'];
                //发票号码
                $data['billTime'] = $res['billTime'];
                //开票时间
                $data['checkCode'] = $res['checkCode'];
                //校验码
                $data['invoiceAmount'] = $res['invoiceAmount'];
                //开具金额
                $data['token'] = $this->token();
                //授权码
                $url = "https://open.leshui365.com/api/invoiceInfoForCom";
                $postdata = http_build_query($data);
                $opts = array('http' => array('method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata));
                $context = stream_context_create($opts);
                $result = file_get_contents($url, false, $context);
                $result = json_decode($result, true);
                if ($result['RtnCode'] == '00') {
                    //成功
                    if ($result['resultCode'] == '2001') {
                        $info['status'] = 0;
                        $info['info'] = $result['resultMsg'];
                        echo json_encode($info);
                        die;
                    }
                    $result = json_decode($result['invoiceResult'], true);
                    $result['openid'] = $res['openid'];
                    $result['invoiceDetailData'] = serialize($result['invoiceDetailData']);
                    switch ($result['invoiceTypeCode']) {
                        case 01:
                            $result['invoiceTypeCode'] = '增值税专票';
                            break;
                        case 02:
                            $result['invoiceTypeCode'] = '货物运输业增值税专用发票';
                            break;
                        case 03:
                            $result['invoiceTypeCode'] = '机动车销售统一发票';
                            break;
                        case 04:
                            $result['invoiceTypeCode'] = '增值税普通发票';
                            break;
                        case 05:
                            $result['invoiceTypeCode'] = '增值税专票';
                            break;
                        case 06:
                            $result['invoiceTypeCode'] = '增值税专票';
                            break;
                        case 07:
                            $result['invoiceTypeCode'] = '增值税专票';
                            break;
                        case '08':
                            $result['invoiceTypeCode'] = '增值税专票';
                            break;
                        case '09':
                            $result['invoiceTypeCode'] = '增值税专票';
                            break;
                        case 10:
                            $result['invoiceTypeCode'] = '电子发票';
                            break;
                        case 11:
                            $result['invoiceTypeCode'] = '卷式普通发票';
                            break;
                        case 12:
                            $result['invoiceTypeCode'] = '增值税专票';
                            break;
                        case 13:
                            $result['invoiceTypeCode'] = '增值税专票';
                            break;
                        case 14:
                            $result['invoiceTypeCode'] = '电子普通[通行费]发票';
                            break;
                        case 15:
                            $result['invoiceTypeCode'] = '二手车统一发票';
                            break;
                        case 20:
                            $result['invoiceTypeCode'] = '国税';
                            break;
                        case 30:
                            $result['invoiceTypeCode'] = '地税';
                            break;
                        default:
                            $result['invoiceTypeCode'] = '暂无';
                    }
                    $result['checkDate'] = date('Y-m-d', time());
                    $rec = M('receipt')->data($result)->add();
                    if ($rec) {
                        //自减余额
                        M('member')->where("openid='" . $res['openid'] . "'")->setDec('nums');
                    }
                    $info['status'] = 1;
                    $info['info'] = $result;
                } else {
                    //失败
                    $info['status'] = 0;
                    $info['info'] = '信息有误！';
                }
            } else {
                $info['status'] = 0;
                $info['info'] = '余额不足！';
            }
            echo json_encode($info);
        }
    }

    //获取乐税token
    private function token()
    {
        $appSecret = M('setting')->where("k='sitename'")->getfield('v');
        $appKey = M('setting')->where("k='title'")->getfield('v');
        $url = "https://open.leshui365.com/getToken?appKey=" . $appKey . "&appSecret=" . $appSecret . " ";
        $result = json_decode(file_get_contents($url, false), true);
        return $result['token'];
    }

    //查询发票历史
    public function receiptlist()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        $offset = $res['nums'] * ($res['p'] - 1);
        //计算记录偏移量
        $where['openid'] = $res['openid'];
        $list = M('receipt')->field("id,invoiceDataCode,invoiceNumber,invoiceTypeName,invoiceTypeCode,billingTime,checkDate,purchaserName,taxpayerBankAccount,salesName,checkCode,taxpayerNumber,salesTaxpayerBankAccount,totalTaxSum,totalTaxNum,totalAmount")->where($where)->order('checkDate desc')->limit($offset . ',' . $res['nums'])->select();
        $count = ceil((M('receipt')->where($where)->count()) / $res['nums']);
        $info['status'] = 1;
        $info['info'] = $list;
        $info['count'] = $count;
        echo json_encode($info);
    }    //单个查询发票历史

    public function search_receipt()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        if ($res['invoiceDataCode']) {
            $where['invoiceDataCode'] = $res['invoiceDataCode'];
        }
        if ($res['invoiceNumber']) {
            $where['invoiceNumber'] = $res['invoiceNumber'];
        }
        if ($res['billingTime']) {
            $where['billingTime'] = $res['billingTime'];
        }
        $list = M('receipt')->field("id,invoiceDataCode,invoiceNumber,invoiceTypeName,invoiceTypeCode,billingTime,checkDate,purchaserName,taxpayerBankAccount,salesName,checkCode,taxpayerNumber,salesTaxpayerBankAccount,totalTaxSum,totalTaxNum,totalAmount")->where($where)->find();
        $info['status'] = 1;
        $info['info'] = $list;
        echo json_encode($info);
    }

    //查询添加发票历史
    public function getnums()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        $vars = M('setting')->select();
        $da1 = explode('/', $vars['0']['v']);
        $da2 = explode('/', $vars['1']['v']);
        $info['status'] = 1;
        $info['info']['0']['price'] = $da1[0];
        $info['info']['0']['count'] = $da1[1];
        $info['info']['1']['price'] = $da2[0];
        $info['info']['1']['count'] = $da2[1];
        echo json_encode($info);
    }

    //查询添加发票历史
    public function myreceiptlist()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        $offset = $res['nums'] * ($res['p'] - 1);
        //计算记录偏移量
        $where['openid'] = $res['openid'];
        $list = M('myreceipt')->where($where)->limit($offset . ',' . $res['nums'])->select();
        $count = ceil((M('myreceipt')->where($where)->count()) / $res['nums']);
        $info['count'] = $count;
        $info['status'] = 1;
        $info['info'] = $list;
        echo json_encode($info);
    }

    //自我添加发票
    public function addmyreceipt()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        if ($res['id']) {
            //存在就修改
            $where['id'] = $res['id'];
            $result = M('myreceipt')->data($res)->where($where)->save();
        } else {
            //不存在就添加
            $result = M('myreceipt')->data($res)->add();
        }
        $info['status'] = 1;
        $info['info'] = '成功！';
        echo json_encode($info);
    }

    //编辑发票历史
    public function editmyreceipt()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        $where['id'] = $res['id'];
        $list = M('myreceipt')->where($where)->find();
        $info['status'] = 1;
        $info['info'] = $list;
        echo json_encode($info);
    }    //编辑发票历史

    public function editreceipt()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        $where['id'] = $res['id'];
        $list = M('receipt')->field("id,invoiceDataCode,invoiceNumber,invoiceTypeName,invoiceTypeCode,billingTime,checkDate,purchaserName,taxpayerBankAccount,salesName,checkCode,taxpayerNumber,salesTaxpayerBankAccount,totalTaxSum,totalTaxNum,totalAmount")->where($where)->find();
        $info['status'] = 1;
        $info['info'] = $list;
        echo json_encode($info);
    }

    //查询余额次数
    public function nums()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        $where['openid'] = $res['openid'];
        $result = M('member')->where($where)->getfield('nums');
        $info['status'] = 1;
        $info['info'] = $result;
        echo json_encode($info);
    }

    //删除发票历史
    public function delmyreceipt()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        $where['id'] = $res['id'];
        $result = M('myreceipt')->where($where)->delete();
        $info['status'] = 1;
        $info['info'] = '删除成功！';
        echo json_encode($info);
    }

    //获取用户openid
    public function getopenid()
    {
        $data = $_REQUEST;
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $data['appid'] . '&secret=' . $data['secret'] . '&js_code=' . $data['js_code'] . '&grant_type=' . $data['grant_type'] . '';
        //yourAppid为开发者appid.appSecret为开发者的appsecret,都可以从微信公众平台获取；
        $info = file_get_contents($url);//发送HTTPs请求并获取返回的数据，推荐使用curl
        $json = json_decode($info);//对json数据解码
        $arr = get_object_vars($json);
        $info1['status'] = 1;
        $info1['openid'] = $arr['openid'];
        echo json_encode($info1);
    }    //获取用户登录信息

    public function user_login()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        $data['openid'] = $res['openid'];
        $data['head'] = $res['head'];
        $data['user'] = $res['user'];
        $uid = M('member')->where("openid='" . $data['openid'] . "'")->getfield('uid');
        if ($uid) {
            //存在就修改
            $result = M('member')->data($data)->where("uid='" . $uid . "'")->save();
        } else {
            //不存在就添加
            $data['t'] = time();
            $data['nums'] = M('setting')->where("k='keywords'")->getfield('v');
            $result = M('member')->data($data)->add();
        }
        $info['status'] = 1;
        $info['info'] = '成功！';
        echo json_encode($info);
    }

    //微信支付接口
    public function pay()
    {
        $res = $_REQUEST;
        //判断是否来自对接前端请求
        if ($this->key($res['openid']) != $res['key']) {
            $info['status'] = 0;
            $info['info'] = '密钥不匹配！';
            echo json_encode($info);
            die;
        }
        //统一下单接口
        $result = $this->weixinapp($res);
        echo json_encode($result);
    }

    /**     * 输出xml字符（数组转换成xml）     * @param $params 参数名称     * return string 返回组装的xml     * */
    private function array2xml($params)
    {
        if (!is_array($params) || count($params) <= 0) {
            return false;
        }
        $xml = "<xml>";
        foreach ($params as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**     * 将xml转为array     * @param string $xml * return array */
    private function xml2array($xml)
    {
        if (!$xml) {
            return false;
        }
        //将XML转为array        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    //微信小程序接口
    private function weixinapp($data)
    {
        //统一下单接口
        $unifiedorder = $this->unifiedorder($data);
        $parameters = array('appId' => C('appid'),
            //小程序ID
            'timeStamp' => time(),
            //时间戳
            'nonceStr' => $this->createNoncestr(),
            //随机串
            'package' => 'prepay_id=' . $unifiedorder['prepay_id'],
            //数据包
            'signType' => 'MD5'//签名方式
        );
        //签名
        $parameters['paySign'] = $this->getSign($parameters);
        return $parameters;
    }

    //统一下单接口
    private function unifiedorder($data)
    {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $order_id = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $parameters = array('appid' => C('appid'),
            //小程序ID
            'mch_id' => C('mch_id'),
            //商户号
            'nonce_str' => $this->createNoncestr(),
            //随机字符串
            'body' => $data['body'],
            //商品描述
            'out_trade_no' => $order_id,
            //商户订单号
            'total_fee' => $data['total_fee'] * 100,
            //总金额 单位 分
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            //终端IP
            'notify_url' => 'https://fapiao.gaodun.com/index.php/Home/Index/notify',
            //通知地址
            'openid' => $data['openid'],
            //用户id
            'trade_type' => 'JSAPI'
            //交易类型
        );
        //统一下单签名
        $parameters['sign'] = $this->getSign($parameters);
        $xmlData = $this->array2xml($parameters);
        $return = $this->xml2array($this->postXmlCurl($xmlData, $url, false, 60));
        //先保存数据库
        if ($return['return_code'] == 'SUCCESS') {
            $add['openid'] = $data['openid'];
            $add['body'] = $data['body'];
            $add['out_trade_no'] = $order_id;
            $add['total_fee'] = $data['total_fee'];
            $add['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];//终端IP;
            $add['time_start'] = date('Y-m-d H:i:s', time());
            $add['nums'] = $data['nums'];
            $add['success_status'] = 0;
            M('order')->data($add)->add();
        }
        return $return;
    }

    /**     * 以post方式提交xml到对应的接口url     *      * @param string $xml 需要post的xml数据     * @param string $url  url     * @param bool $useCert 是否需要证书，默认不需要     * @param int $second   url执行超时时间，默认30s     * @throws WxPayException */
    private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return $error;
            throw new WxPayException("curl出错，错误码:$error");
        }
    }

    //作用：产生随机字符串，不长于32位
    private function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }     //作用：生成签名

    private function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . C('key');         //签名步骤三：MD5加密
        $String = md5($String);         //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }    ///作用：格式化参数，签名过程需要使用

    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }          //微信支付回调验证

    public function notify()
    {
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];          //将服务器返回的XML数据转化为数组
        $data = $this->xml2array($xml);         // 保存微信服务器返回的签名sign
        $data_sign = $data['sign'];          // sign不参与签名算法
        unset($data['sign']);
        $sign = $this->getSign($data);                    // 判断签名是否正确  判断支付状态
        if (($sign === $data_sign) && ($data['return_code'] == 'SUCCESS') && ($data['result_code'] == 'SUCCESS')) {
            if (($sign === $data_sign) && ($data['return_code'] == 'SUCCESS') && ($data['result_code'] == 'SUCCESS')) {
                $nums = M('order')->where("out_trade_no='" . $data['out_trade_no'] . "'")->getfield('nums');
                //更新用户次数
                M('member')->where("openid='" . $data['openid'] . "'")->setInc('nums', $nums);            //更新数据库
                $save['success_status'] = 1;
                $save['time_expire'] = date('Y-m-d H:i:s', time());
                $save['transaction_id'] = $data['transaction_id'];
                $result = M('order')->data($save)->where("out_trade_no='" . $data['out_trade_no'] . "'")->save();
            } else {
                $result = false;
            }          // 返回状态给微信服务器
            if ($result) {
                $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            } else {
                $str = '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
            }
            echo $str;
            return $result;
        }
    }
}
