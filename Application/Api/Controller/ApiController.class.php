<?php

namespace Api\Controller;

use Think\Controller;

Header("Access-Control-Allow-Origin: * ");
Header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");

class ApiController extends Controller
{
    //获取用户信息
    function addUser()
    {
        if (IS_POST) {
            $data = M('project_user')->where(array('phone' => $_POST['phone'], 'type' => $_POST['type'], 'gid' => $_POST['gid']))->find();
            if (count($data) > 0) {
                $this->ajaxReturn(array('code' => false, 'msg' => '已经领取过了'));
            } else {
                $_POST['time'] = time();
                if (M('project_user')->add($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '上传成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '上传失败'));
                }
            }
        }
    }

    //首页
    function dataIndex()
    {
        if (IS_POST) {
            $res = M('project_group')->where(array('id' => $_POST['id']))->select();
            //资料
            $limit = @!empty($_POST['limit']) ? $_POST['limit'] : 10;
            $page = @!empty($_POST['page']) ? $_POST['page'] : 1;
            $page = ($page - 1) * $limit;
            foreach ($res as $key) {
                $data = M('project_data')->where(array('gid' => $key['id'], 'del' => '1'))->select();
                $video = M('project_video')->where(array('gid' => $key['id'], 'del' => '1'))->select();
                $advertise = M('project_advertise')->where(array('gid' => $key['id'], 'del' => '1'))->select();
            }
            $i = 0;
            foreach ($data as $val) {
                $arr[$i] = $val;
                $arr[$i]['style'] = '1';
                $i++;
            }
            foreach ($video as $val) {
                $arr[$i] = $val;
                $arr[$i]['style'] = '2';
                $i++;
            }
            foreach ($advertise as $val) {
                $arr[$i] = $val;
                $arr[$i]['style'] = '3';
                $i++;
            }
            $data = $this->bubble_sort($arr);
            if ((count($data) - $page) < $limit) {
                $on = count($data);
            } else {
                $on = $limit;
            }
            for ($i = $page; $i < $on; $i++) {
                if (time() > ($data[$i]['time'] + (3600 * $data[$i]['ontime']))) {
                    if ($data[$i]['style'] == '1') {
                        M('project_data')->where(array('id' => $data[$i]['id']))->save(array('type' => '0'));
                    } elseif ($data[$i]['style'] == '2') {
                        M('project_video')->where(array('id' => $data[$i]['id']))->save(array('type' => '0'));
                    } elseif ($data[$i]['style'] == '3') {
                        M('project_advertise')->where(array('id' => $data[$i]['id']))->save(array('type' => '0'));
                    }
                    $data[$i]['type'] = '0';
                }
				if ($data[$i]['style'] == '1') {
                    $data[$i]['total'] = count(M('project_user')->where(array('type' => '1', 'gid' => $data[$i]['id']))->select());
                } elseif ($data[$i]['style'] == '2') {
                    $data[$i]['total'] = count(M('project_user')->where(array('type' => '2', 'gid' => $data[$i]['id']))->select());
                } elseif ($data[$i]['style'] == '3') {
                    $data[$i]['total'] = count(M('project_user')->where(array('type' => '3', 'gid' => $data[$i]['id']))->select());
                }
//				  $data[$i]['h'] = date('H', ($data[$i]['time'] + (3600 * $data[$i]['ontime'])) - time());
//                $data[$i]['i'] = date('i', ($data[$i]['time'] + (3600 * $data[$i]['ontime'])) - time());
//                $data[$i]['s'] = date('s', ($data[$i]['time'] + (3600 * $data[$i]['ontime'])) - time());
                $data[$i]['ontime'] = date('Y-m-d H:i:s', ($data[$i]['time'] + (3600 * $data[$i]['ontime'])));
				$data[$i]['time'] = date('Y-m-d H:i:s', $data[$i]['time']);
                $array[] = $data[$i];
                unset($array[$i]['del']);
                unset($array[$i]['gid']);
                unset($array[$i]['download']);
                unset($array[$i]['pattern']);
                unset($array[$i]['fileurl']);
                unset($array[$i]['chapter']);
                unset($array[$i]['hour']);
                unset($array[$i]['hoururl']);
            }
//            foreach ($res as $key) {
//                $data['data'] = M('project_data')->where(array('gid' => $key['id'], 'del' => '1'))->order('time desc')->limit($page,$limit)->select();
//                for ($i = 0; $i < count($data['data']); $i++) {
//                    if (time() > ($data['data'][$i]['time'] + (3600 * $data['data'][$i]['ontime']))) {
//                        M('project_data')->where(array('id' => $data['data'][$i]['id']))->save(array('type' => '0'));
//                    }
//                    $data['data'][$i]['h'] = date('H', $data['data'][$i]['time']-time());
//                    $data['data'][$i]['i'] = date('i', $data['data'][$i]['time']-time());
//                    $data['data'][$i]['s'] = date('s', $data['data'][$i]['time']-time());
//                    $data['data'][$i]['total'] = count(M('project_user')->where(array('type' => '1', 'gid' => $data['data'][$i]['id']))->select());
//                    unset($data['data'][$i]['fileurl']);
//                    unset($data['data'][$i]['download']);
//                    unset($data['data'][$i]['pattern']);
//                    unset($data['data'][$i]['del']);
//                    unset($data['data'][$i]['gid']);
//                }
//                //视频
//                $data['video'] = M('project_video')->where(array('gid' => $key['id'], 'del' => '1'))->order('time desc')->limit($page,$limit)->select();
//                for ($i = 0; $i < count($data['video']); $i++) {
//                    if (time() > ($data['video'][$i]['time'] + (3600 * $data['video'][$i]['ontime']))) {
//                        M('project_video')->where(array('id' => $data['video'][$i]['id']))->save(array('type' => '0'));
//                    }
//                    $data['video'][$i]['h'] = date('H', $data['video'][$i]['time']-time());
//                    $data['video'][$i]['i'] = date('i', $data['video'][$i]['time']-time());
//                    $data['video'][$i]['s'] = date('s', $data['video'][$i]['time']-time());
//                    $data['video'][$i]['total'] = count(M('project_user')->where(array('type' => '2', 'gid' => $data['video'][$i]['id']))->select());
//                    unset($data['video'][$i]['fileurl']);
//                    unset($data['video'][$i]['chapter']);
//                    unset($data['video'][$i]['hour']);
//                    unset($data['video'][$i]['hoururl']);
//                    unset($data['video'][$i]['pattern']);
//                    unset($data['video'][$i]['del']);
//                    unset($data['video'][$i]['gid']);
//                }
//                //广告
//                $data['advertise'] = M('project_advertise')->where(array('gid' => $key['id'], 'del' => '1'))->order('time desc')->limit(($_POST['page']-1),1)->select();
//                for ($i = 0; $i < count($data['advertise']); $i++) {
//                    if (time() > ($data['advertise'][$i]['time'] + (3600 * $data['advertise'][$i]['ontime']))) {
//                        M('project_advertise')->where(array('id' => $data['advertise'][$i]['id']))->save(array('type' => '0'));
//                    }
//                    $data['advertise'][$i]['h'] = date('H', $data['advertise'][$i]['time']-time());
//                    $data['advertise'][$i]['i'] = date('i', $data['advertise'][$i]['time']-time());
//                    $data['advertise'][$i]['s'] = date('s', $data['advertise'][$i]['time']-time());
//                    unset($data['advertise'][$i]['del']);
//                    unset($data['advertise'][$i]['gid']);
//                }
//            }
            $this->ajaxReturn($array);
        }
    }

    //资料详情
    function dataDetail()
    {
        if (IS_POST) {
            $data = M('project_data')->where(array('id' => $_POST['id'], 'del' => '1'))->find();
            if (time() > ($data['time'] + (3600 * $data['ontime']))) {
                M('project_data')->where(array('id' => $data['id']))->save(array('type' => '0'));
            }
            $data['time'] = date('Y-m-d H:i:s', $data['time']);
            $data['total'] = count(M('project_user')->where(array('type' => '1', 'gid' => $data['id']))->select());
			$data['ontime'] = date('Y-m-d H:i:s', ($data['time'] + (3600 * $data['ontime'])));
            unset($data['pattern']);
            unset($data['del']);
            unset($data['gid']);
            $this->ajaxReturn($data);
        }
    }

    //视频详情
    function videoDetail()
    {
        if (IS_POST) {
            $data = M('project_video')->where(array('id' => $_POST['id'], 'del' => '1'))->find();
            if (time() > ($data['time'] + (3600 * $data['ontime']))) {
                M('project_video')->where(array('id' => $data['id']))->save(array('type' => '0'));
            }
            $data['time'] = date('Y-m-d H:i:s', $data['time']);
            $data['total'] = count(M('project_user')->where(array('type' => '2', 'gid' => $data['id']))->select());
			$data['ontime'] = date('Y-m-d H:i:s', ($data['time'] + (3600 * $data['ontime'])));
            $chapter = explode(',', $data['chapter']);
            $hour = explode('|', $data['hour']);
            $hoururl = explode('|', $data['hoururl']);
            unset($data['chapter']);
            unset($data['hour']);
            unset($data['hoururl']);
            for ($i = 0; $i < count($chapter); $i++) {
                if ($chapter[$i] != '') {
                    $data['chapter'][$i]['title'] = $chapter[$i];
                }
                $hourArr = explode(',', $hour[$i]);
                $hoururlArr = explode(',', $hoururl[$i]);
                for ($j = 0; $j < count($hourArr); $j++) {
                    if ($hourArr[$j] != '') {
                        $data['chapter'][$i]['classes'][$j]['hour'] = $hourArr[$j];
                        $data['chapter'][$i]['classes'][$j]['hoururl'] = $hoururlArr[$j];
                    }
                }
            }
            unset($data['pattern']);
            unset($data['del']);
            unset($data['gid']);
            $this->ajaxReturn($data);
        }

    }

    function decrypt()
    {
        if (IS_POST) {
            vendor('wxAppBase64.wxBizDataCrypt#class');
            $sessionKey = $_POST['sessionKey'];
            $encryptedData = $_POST['encryptedData'];
            $appId = $_POST['appId'];
            $iv = $_POST['iv'];
            $pc = new \WXBizDataCrypt($appId, $sessionKey);
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
        if (IS_POST) {
            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $_POST['appId'] . '&secret=' . $_POST['appSecret'] . '&js_code=' . $_POST['code'] . '&grant_type=authorization_code';
            //Appid为开发者appid.appSecret为开发者的appsecret,都可以从微信公众平台获取；
            $info = file_get_contents($url);//发送HTTPs请求并获取返回的数据，推荐使用curl
            $json = json_decode($info);//对json数据解码
            $arr = get_object_vars($json);
            $this->ajaxReturn($arr);
        }
    }

    private function bubble_sort($array)
    {
        $count = count($array);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $count - 1; $j > $i; $j--) {
                if ($array[$j]['time'] > $array[$j - 1]['time']) {
                    $tmp = $array[$j];
                    $array[$j] = $array[$j - 1];
                    $array[$j - 1] = $tmp;
                }
            }
        }
        return $array;
    }

}

