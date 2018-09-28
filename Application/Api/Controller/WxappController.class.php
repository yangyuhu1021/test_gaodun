<?php

namespace Api\Controller;

use Think\Controller;
use wxapp\aes\WXBizDataCrypt;

Header("Access-Control-Allow-Origin: * ");
Header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

class WxappController extends Controller
{
	
    private $appId = 'wxe43dd29ec2b221c2';
    private $secret = 'd637cf6295b5fbc494f210be04cbc270';

    /**
     * 获取sessionKey
     */
    public function getSessionKey(){
        if(empty($_POST['code'])){
            $data['code']=40001;
            $data['msg']='缺少参数：code';
            $this->ajaxReturn($data);
        }

        $code      = $_POST['code'];
        $appId     = $this->appId;
        $appSecret = $this->secret;

        $response = $this->httpGet("https://api.weixin.qq.com/sns/jscode2session?appid=$appId&secret=$appSecret&js_code=$code&grant_type=authorization_code");

        $response = json_decode($response, true);
        if (!empty($response['errcode'])) {
            $data['code']=40002;
            $data['msg']='操作失败:'.$response['errcode'];
            $this->ajaxReturn($data);
        }
        $data['code']=20000;
        $data['msg']='获取成功:';
        $data['data']=$response;
        $this->ajaxReturn($data);
    }

    /**
     * 小程序登录注册
     */
    public function login()
    {
        $data = $_POST;
        if(empty($_POST['openid'])){
            $data['code']=40001;
            $data['msg']='缺少参数openid';
            $this->ajaxReturn($data);
        }
        if(empty($_POST['session_key'])){
            $data['code']=40001;
            $data['msg']='缺少参数session_key';
            $this->ajaxReturn($data);
        }
        if(empty($_POST['encrypted_data'])){
            $data['code']=40001;
            $data['msg']='缺少参数encrypted_data';
            $this->ajaxReturn($data);
        }
        if(empty($_POST['iv'])){
            $data['code']=40001;
            $data['msg']='iv';
            $this->ajaxReturn($data);
        }

        $appId     = $this->appId;

        $openid     = $data['openid'];
        $sessionKey = $data['session_key'];

        $pc      = new WXBizDataCrypt($appId, $sessionKey);
        $errCode = $pc->decryptData($data['encrypted_data'], $data['iv'], $wxUserData);

        if ($errCode != 0) {
            $data['code']=40003;
            $data['msg']='检验数据失败';
            $this->ajaxReturn($data);
        }

        $find = M('app_wxapp_user')
            ->where('openid', $openid)
            ->find();

        if ($find) {
            session('app_wxapp_user',$find);
            $data['code']=20000;
            $data['msg']='登录成功';
            $this->ajaxReturn($data);
        } else {

            $array=array(
                'create_time'   => time(),
                'gender'        => $wxUserData['gender'],
                'nickname'      => $wxUserData['nickName'],
                'avatar'        => $wxUserData['avatarUrl'],
                'openid'        => $openid
            );

            $row=M('app_wxapp_user')->add($array);
            if($row){
                $data['code']=20000;
                $data['msg']='登录成功';
                $array['id']=$row;
                session('app_wxapp_user',$array);
                $this->ajaxReturn($data);
            }else{
                $data['code']=40004;
                $data['msg']='登录失败';
                $this->ajaxReturn($data);
            }

        }

    }

    /**
     * 获取微信用户手机号
     */
    public function getPhoneNumber(){
        $this->checkLoginUser();
        $data = $_POST;
        if(empty($_POST['openid'])){
            $data['code']=40001;
            $data['msg']='缺少参数openid';
            $this->ajaxReturn($data);
        }
        if(empty($_POST['session_key'])){
            $data['code']=40001;
            $data['msg']='缺少参数session_key';
            $this->ajaxReturn($data);
        }
        if(empty($_POST['encrypted_data'])){
            $data['code']=40001;
            $data['msg']='缺少参数encrypted_data';
            $this->ajaxReturn($data);
        }
        if(empty($_POST['iv'])){
            $data['code']=40001;
            $data['msg']='iv';
            $this->ajaxReturn($data);
        }
        $appId     = $this->appId;
        $sessionKey = $data['session_key'];

        $pc      = new WXBizDataCrypt($appId, $sessionKey);
        $errCode = $pc->decryptData($data['encrypted_data'], $data['iv'], $wxUserData);

        if ($errCode != 0) {
            $data['code']=4000;
            $data['msg']='检验数据失败';
            $this->ajaxReturn($data);
        }

        $mobile=$wxUserData['phoneNumber'];

        $row=M('app_wxapp_user')->where(array('openid'=>$data['openid']))->setField('mobile',$mobile);

        if($row!==false){
            $info=M('app_wxapp_user')->where(array('openid'=>$data['openid']))->find();
            session('app_wxapp_user',$info);
            $data['code']=20000;
            $data['msg']='绑定手机成功';
            $this->ajaxReturn($data);
        }else{
            $data['code']=40005;
            $data['msg']='绑定手机失败';
            $this->ajaxReturn($data);
        }
        $this->success('返回数据',$wxUserData);
    }

    /**
     * 提交问卷
     */
    public function submitQuestion(){
        $this->checkLoginUser(true);
        $param=$_POST['answer'];
        if(empty($param)){
            $data['code']=40001;
            $data['msg']='缺少参数answer';
            $this->ajaxReturn($data);
        }

        $session=session('app_wxapp_user');
        $userId=$session['id'];

        $model=M('app_wxapp_exam');
        $count=$model->count('id');
        $model->startTrans();
        $data['user_id']=$userId;
        $data['create_time']=time();

        $data['number']=intval(date('Ymd'))*10000+$count+1;
        $exam_id=$model->add($data);

        $detail=array();
        foreach ($param as $k=>$v){
            $detail[$k]['exam_id']=$exam_id;
            $detail[$k]['result']=$v['question_id'];
            $detail[$k]['question_title']=$v['question_title'];
            $detail[$k]['result']=$v['result'];
        }
        $row=M('app_wxapp_exam_detail')->addAll($detail);
        if($exam_id&&$row){
            $model->commit();
            $ret['code']=20000;
            $ret['msg']='提交成功';
            $this->ajaxReturn($ret);
        }else{
            $model->rollback();
            $ret['code']=40008;
            $ret['msg']='提交失败';
            $this->ajaxReturn($ret);
        }
    }

    /**
     * 检查用户信息
     * @param bool $is_check_mobile
     */
    protected function checkLoginUser($is_check_mobile=false){
        $session=session('app_wxapp_user');
        if(empty($session['id'])){
            $data['code']=40006;
            $data['msg']='您还没有登录';
            $this->ajaxReturn($data);
        }
        if($is_check_mobile&&empty($session['mobile'])){
            $data['code']=40007;
            $data['msg']='您还没有绑定手机';
            $this->ajaxReturn($data);
        }
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