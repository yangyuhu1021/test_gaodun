<?php

namespace Api\Controller;

use Think\Controller;

Header("Access-Control-Allow-Origin: * ");
Header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

class AccaController extends Controller
{
	
    private $appId = 'wx4449fc9af831462e';
    private $secret = '99a8e69bd3a30b49b5f094e80c45f6ee';
	private $appId_1 = 'wx6a9defd3dea89c2e';
    private $secret_2 = 'a893882362fc037cd28405f17840b4f3';


    function Email(){
        if (IS_POST) {
            $phone = "/^1[345678]{1}\d{9}$/";
            $email = "/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
            if (empty($_POST['phone']) || $_POST['phone'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '手机号为空'));
            }
            if (empty($_POST['email']) || $_POST['email'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '邮箱为空'));
            }
            if (!preg_match($phone, $_POST['phone'])) {
                $this->ajaxReturn(array('code' => 202, 'msg' => '手机号格式不正确'));
            }
            if (!preg_match($email, $_POST['email'])) {
                $this->ajaxReturn(array('code' => 202, 'msg' => '电子邮箱格式不正确'));
            }
            $_POST['time'] = time();
            $_POST['type'] = '必备资料';
            if (M('acca_info')->add($_POST)) {
                $this->ajaxReturn(array('code' => 200, 'msg' => 'ok'));
            } else {
                $this->ajaxReturn(array('code' => 203, 'msg' => '未知错误，上传失败'));
            };
        } else {
            $this->ajaxReturn(array('code' => 204, 'msg' => '请使用POST方式'));
        }
    }

    function Info(){
        if (IS_POST) {
            $phone = "/^1[345678]{1}\d{9}$/";
            $email = "/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
            if (empty($_POST['phone']) || $_POST['phone'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '手机号为空'));
            }
            if (empty($_POST['name']) || $_POST['name'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '姓名为空'));
            }
            if (empty($_POST['email']) || $_POST['email'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '邮箱为空'));
            }
            if (!preg_match($phone, $_POST['phone'])) {
                $this->ajaxReturn(array('code' => 202, 'msg' => '手机号格式不正确'));
            }
            if (!preg_match($email, $_POST['email'])) {
                $this->ajaxReturn(array('code' => 202, 'msg' => '电子邮箱格式不正确'));
            }
            $_POST['time'] = time();
            $_POST['type'] = '代理报名';
            if (M('acca_info')->add($_POST)) {
                $this->ajaxReturn(array('code' => 200, 'msg' => 'ok'));
            } else {
                $this->ajaxReturn(array('code' => 203, 'msg' => '未知错误，上传失败'));
            };
        } else {
            $this->ajaxReturn(array('code' => 204, 'msg' => '请使用POST方式'));
        }
    }

    function Assess(){
        if (IS_POST) {
            $phone = "/^1[345678]{1}\d{9}$/";
            if (empty($_POST['phone']) || $_POST['phone'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '手机号为空'));
            }
            if (empty($_POST['name']) || $_POST['name'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '姓名为空'));
            }
            if (!preg_match($phone, $_POST['phone'])) {
                $this->ajaxReturn(array('code' => 202, 'msg' => '手机号格式不正确'));
            }
            if (empty($_POST['identity']) || $_POST['identity'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '身份信息为空'));
            }
            if (empty($_POST['education']) || $_POST['education'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '学历为空'));
            }
            if (empty($_POST['major']) || $_POST['major'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '专业为空'));
            }
            if (empty($_POST['english']) || $_POST['english'] == '') {
                $this->ajaxReturn(array('code' => 201, 'msg' => '英语能力为空'));
            }
            $_POST['time'] = time();
            $_POST['type'] = '学前评估';
            if (M('acca_info')->add($_POST)) {
                $this->ajaxReturn(array('code' => 200, 'msg' => 'ok'));
            } else {
                $this->ajaxReturn(array('code' => 203, 'msg' => '未知错误，上传失败'));
            };
        } else {
            $this->ajaxReturn(array('code' => 204, 'msg' => '请使用POST方式'));
        }
    }

    function select(){
        $phone = "/^1[345678]{1}\d{9}$/";
        if (empty($_POST['phone']) || $_POST['phone'] == '') {
            $this->ajaxReturn(array('code' => 201, 'msg' => '手机号为空'));
        }
        if (empty($_POST['name']) || $_POST['name'] == '') {
            $this->ajaxReturn(array('code' => 201, 'msg' => '姓名为空'));
        }
        if (!preg_match($phone, $_POST['phone'])) {
            $this->ajaxReturn(array('code' => 202, 'msg' => '手机号格式不正确'));
        }
        
        if (empty($_POST['school']) || $_POST['school'] == '') {
            $this->ajaxReturn(array('code' => 201, 'msg' => '学校为空'));
        }
        if (empty($_POST['graduation']) || $_POST['graduation'] == '') {
            $this->ajaxReturn(array('code' => 201, 'msg' => '是否毕业为空'));
        }
        if (empty($_POST['major']) || $_POST['major'] == '') {
            $this->ajaxReturn(array('code' => 201, 'msg' => '专业为空'));
        }
        $_POST['time'] = time();
        $_POST['type'] = '免考查询';
        if (M('acca_info')->add($_POST)) {
            $this->ajaxReturn(array('code' => 200, 'msg' => 'ok'));
        } else {
            $this->ajaxReturn(array('code' => 203, 'msg' => '未知错误，上传失败'));
        };
    }

	
    function decrypt()
    {
        if (IS_POST) {
            vendor('wxAppBase64.wxBizDataCrypt#class');
            $sessionKey = $_POST['sessionKey'];
            $encryptedData = $_POST['encryptedData'];
            $iv = $_POST['iv'];
            $pc = new \WXBizDataCrypt($this->appId, $sessionKey);
            $errCode = $pc->decryptData($encryptedData, $iv, $data);
            if ($errCode == 0) {
                $this->ajaxReturn($data);
            } else {
                $this->ajaxReturn($errCode);
            }
        }
    }

    public function user_info()
    {
        if(IS_POST){
            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->appId . '&secret=' . $this->secret . '&js_code=' . $_POST['code'] . '&grant_type=authorization_code';
            //Appid为开发者appid.appSecret为开发者的appsecret,都可以从微信公众平台获取；
            $info = file_get_contents($url);//发送HTTPs请求并获取返回的数据，推荐使用curl
            $json = json_decode($info);//对json数据解码
            $arr = get_object_vars($json);
            $this->ajaxReturn($arr);
        }
    }  
	
	function decrypt_1()
    {
        if (IS_POST) {
            vendor('wxAppBase64.wxBizDataCrypt#class');
            $sessionKey = $_POST['sessionKey'];
            $encryptedData = $_POST['encryptedData'];
            $iv = $_POST['iv'];
            $pc = new \WXBizDataCrypt($this->appId_1, $sessionKey);
            $errCode = $pc->decryptData($encryptedData, $iv, $data);
            if ($errCode == 0) {
                $this->ajaxReturn($data);
            } else {
                $this->ajaxReturn($errCode);
            }
        }
    }

    public function user_info_1()
    {
        if(IS_POST){
            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->appId_1 . '&secret=' . $this->secret_2 . '&js_code=' . $_POST['code'] . '&grant_type=authorization_code';
            //Appid为开发者appid.appSecret为开发者的appsecret,都可以从微信公众平台获取；
            $info = file_get_contents($url);//发送HTTPs请求并获取返回的数据，推荐使用curl
            $json = json_decode($info);//对json数据解码
            $arr = get_object_vars($json);
            $this->ajaxReturn($arr);
        }
    }
	
	

	
}