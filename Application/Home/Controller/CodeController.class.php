<?php

namespace Home\Controller;

class CodeController extends ComController
{

    function index()
    {
        $model = new  \Think\Model();
        if (IS_POST) {
            if (@!empty($_POST['key'])) {
                $model->execute('update qw_user_code set phone="' . $_POST['key'] . '" where code_id="' . $_POST['id'] . '" and openid ="' . $_SESSION['openid'] . '"');
            }
            if (@$_POST['code']) {
                $model->execute('update qw_user_code set is_on="1" where code_id="' . $_POST['id'] . '" and openid ="' . $_SESSION['openid'] . '"');
            }
        }
        if (@!empty($_GET['type'])) {
            S('get', $_GET, 3600);
        }

        if (empty($_SESSION['openid'])) {
            $this->wxCode('Home/Code/url');
            exit;
        }
        $get = S('get');
        if (@!empty($get['id']) && !empty($get['type'])) {

            $id = $get['id'];
            $type = $get['type'];
            $data = $model->query('select * from qw_group_code where id="' . $id . '" and type="' . $type . '"');

            if ($type == '1') {
                $mysql = 'qw_one_code';
            } elseif ($type == '2') {
                $mysql = 'qw_ones_code';
                $res = $model->query('select * from ' . $mysql . ' where group_id="' . $id . '" and del = "1" ');
                foreach ($res as $key) {
                    if (time() > $key['time']) {
                        $model->execute('update qw_ones_code set del="0" where id="' . $key['id'] . '"');
                    }
                }
            }

            if ($data[0]['key'] == '1') {
                $res = $model->query('select * from ' . $mysql . ' where group_id="' . $data[0]['id'] . '" and del="1" ');
                if (count($res[0]) > 0) {
                    for ($i = 0; $i < count($res); $i++) {
                        $user = $model->query('select * from qw_user_code where code_id="' . $res[$i]['id'] . '" and openid ="' . $_SESSION['openid'] . '" and type="' . $type . '" ');
                        if (count($user) > 0) {
                            $key = array('code' => false, 'id' => $user[0]['code_id']);
                            break;
                        }
                        $key = array('code' => true);
                    }
                }
            }

            if ($key['code']) {
                $res = $model->query('select * from ' . $mysql . ' where group_id="' . $data[0]['id'] . '" and type="1"');
                $user = count($model->query('select * from qw_user_code where code_id="' . $res[0]['id'] . '" and is_on ="1" '));
                if ($user > $res[0]['key']) {
                    $model->execute('update ' . $mysql . ' set type="2" where id="' . $res[0]['id'] . '"');
                    $res = $model->query('select * from ' . $mysql . ' where group_id="' . $id . '" and type ="0" limit 1');
                    $model->execute('update ' . $mysql . ' set type="1" where id="' . $res[0]['id'] . '"');
                }

                $map = array(
                    'openid' => $_SESSION['openid'],
                    'time' => time(),
                    'type' => $type,
                    'position' => $this->ip(),
                    'code_id' => $res[0]['id'],
                    'is_on' => '0'
                );
                if (M('user_code')->add($map)) {

                    $key['id'] = $res[0]['id'];
                } else {
                    echo '网络繁忙，请稍后再试';
                    exit;
                }

            }
        }
        $data = $model->query('select * from ' . $mysql . ' where id="' . $key['id'] . '"');
        $this->assign('data', $data[0]);
        $this->display();
    }

    function ip()
    {
        if (empty($ip)) $ip = $_SERVER["REMOTE_ADDR"];  //get_client_ip()为tp自带函数，如没有，自己百度搜索。此处就不重复复制了
        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip;
        $result = file_get_contents($url);
        $result = json_decode($result, true);
        if ($result['code'] !== 0 || !is_array($result['data'])) return false;
        return $result['data']['region'];
    }

    function wxCode($mvc)
    {
        $model = new  \Think\Model();
        $config = $model->query('select * from qw_wxConfig where id=1 limit 1');
        $redirectUrl = urlencode("http://fapiao.gaodun.com/" . $mvc);
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $config[0]['AppId'] . '&redirect_uri=' . $redirectUrl . '&response_type=code&scope=snsapi_base&state=STATE&connect_redirect=1#wechat_redirect';
        header("Location:" . $url);
    }

    function url()
    {
        if (!empty($_GET)) {
            $code = $_GET['code'];//获取code
            //exit($code);
            $model = new  \Think\Model();
            $config = $model->query('select * from qw_wxConfig where id=1 limit 1');
            $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $config[0]['AppId'] . '&secret=' . $config[0]['AppSecret'] . '&code=' . $code . '&grant_type=authorization_code';//通过code换取网页授权access_token
            $str = file_get_contents($url);
            $arr = json_decode($str, true);
            $_SESSION['openid'] = $arr['openid'];
            header("Location:" . U('home/code/index'));
        }
    }


}
