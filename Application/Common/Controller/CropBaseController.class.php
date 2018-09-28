<?php

namespace Dxadmin\Controller;

use Common\Controller\BaseController;

class CropBaseController extends BaseController
{

    protected $cropId,$agentId,$secret;

    public function _initialize(){
        $this->cropId='ww843bdd654b7453dc';
        $this->secret='68UlL4TQnvN_ui_Or_aN1uytKxsg9SNEd-wJJEw-oOE';
        $this->agentId='1000002';

        $this->checkLogin();
    }

    /**
     * 检查登录
     */
    public function checkLogin(){
        $cropUserId=session('crop_user.id');
        if(empty($cropUserId)){
            $code=I('get.code');
            if(empty($code)){
                $redirect=U('Home/Crop/index');
                $this->getCode($redirect);
            }
            require_once VENDOR_PATH."CropJssdk/jssdk.php";
            $jssdk=new \JSSDK($this->cropId,$this->secret);
            $access_token=$jssdk->getAccessToken();
            $userinfoUrl = " https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token=$access_token&code=$code";
            $userinfo = json_decode($this->httpGet($userinfoUrl), true);
            if($userinfo['errcode']==0){
                $where=array();
                if(isset($userinfo['UserId'])){
                    $userOpenInfo=$this->convert_to_openid($access_token,$userinfo['UserId']);
                    if($userOpenInfo['errcode']!=0){
                        $this->error($userOpenInfo['errcode'].':'.$userOpenInfo['errmsg']);
                    }
                    $data['openid']=$userOpenInfo['openid'];
                    $where['openid']=$userOpenInfo['openid'];
                    //内部成员
                    $cropUserInfo=$this->getCropUserInfo($access_token,$userinfo['UserId']);
                    if($cropUserInfo['errcode']!=0){
                        $this->error($cropUserInfo['errcode'].':'.$cropUserInfo['errmsg']);
                    }
                    $data['userid']=$cropUserInfo['userid'];
                    $data['name']=$cropUserInfo['name'];
                    $data['mobile']=$cropUserInfo['mobile'];
                    $data['department']=json_encode($cropUserInfo['department']);
                    $data['position']=$cropUserInfo['position'];
                    $data['gender']=$cropUserInfo['gender'];
                    $data['email']=$cropUserInfo['email'];
                    $data['avatar']=$cropUserInfo['avatar'];
                    $data['qr_code']=$cropUserInfo['qr_code'];
                    $data['user_type']=1;
                }

                if(isset($userinfo['OpenId'])){
                    $where['openid']=$userinfo['OpenId'];
                    //企业外部成员
                    $data['openid']=$userinfo['OpenId'];
                    $data['user_type']=2;
                }

                if(!empty($data)){
                    $info=M('app_crop_user')->where($where)->find();
                    if(empty($info)){
                        $data['device_id']=$userinfo['DeviceId'];
                        $data['create_time']=time();
                        $data['update_time']=time();
                        $data['last_login_time']=time();
                        $data['last_login_ip']=get_client_ip(0,true);
                        $info=$data;
                        M('app_crop_user')->add($data);
                    }else{
                        $data['last_login_time']=time();
                        $data['last_login_ip']=get_client_ip(0,true);
                        M('app_crop_user')->where($where)->save($data);
                    }
                    session('crop_user',$info);
                }else{
                    $this->error('获取用户信息出错');
                }
            }else{
                $this->error($userinfo['errcode'].':'.$userinfo['errmsg']);
            }
        }

    }

    /**
     * 获取成员信息
     * @param $access_token
     * @param $userId
     * @return mixed
     */
    public function getCropUserInfo($access_token,$userId){
        $userinfoUrl="https://qyapi.weixin.qq.com/cgi-bin/user/get?access_token=$access_token&userid=$userId";
        $userinfo = json_decode($this->httpGet($userinfoUrl), true);
        return $userinfo;
    }

    /**
     * userid转openid
     * @param $access_token
     * @param $userId
     * @return mixed
     */
    public function convert_to_openid($access_token,$userId){
        $userinfoUrl=" https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid?access_token=$access_token";
        $data=array('userid'=>$userId);
        $userinfo = json_decode($this->httpPost($userinfoUrl,$data), true);
        return $userinfo;
    }

    /**
     * 获取CODE
     * @param string $redirect 重定向地址
     * @param string $scope
     */
    public function getCode($redirect,$scope='snsapi_base'){
        $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->cropId."&redirect_uri=$redirect&
        response_type=code&scope=$scope&agentid=".$this->cropId."&state=state#wechat_redirect";
        header("Location:$url");
    }

    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    private function httpPost($url,$data,$header=array()){ // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, false); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        $result = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Error POST'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $result; // 返回数据
    }

}