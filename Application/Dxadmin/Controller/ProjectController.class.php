<?php

namespace Dxadmin\Controller;

class ProjectController extends ComController
{
    public function index()
    {
        $model = new  \Think\Model();
        if (IS_POST) {
            if ($_POST['type'] == 'add') {
                $map['name'] = $_POST['name'];
                $state = M('project_group')->add($map);
                if ($state) {
                    $array = array('code' => true, 'msg' => '添加成功');
                } else {
                    $array = array('code' => false, 'msg' => '网络繁忙，添加失败');
                }

            } elseif ($_POST['type'] == 'edit') {
                $map['id'] = $_POST['id'];
                if (@empty($_POST['name'])) {
                    $array = M('project_group')->where($map)->find();
                } else {
                    $update['name']=$_POST['name'];
                    if ( M('project_group')->where($map)->save($update)) {
                        $array = array('code' => true, 'msg' => '修改成功');
                    } else {
                        $array = array('code' => false, 'msg' => '网络繁忙，修改失败');
                    }
                }

            } elseif ($_POST['type'] == 'del') {
                $map['id'] = $_POST['id'];
                $state = M('project_group')->where($map)->delete();
                if ($state) {
                    $array = array('code' => true, 'msg' => '删除成功');
                } else {
                    $array = array('code' => false, 'msg' => '网络繁忙，删除失败');
                }
            }
            $this->ajaxReturn($array);
        }
        $pageSize = 10;
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $offset = $pageSize * ($p - 1);
        if (@$_GET['like'] != '') {
            $like = $_GET['like'];
            $res = $model->query('select * from qw_project_group where   name LIKE "%' . $like . '%" order by id desc limit ' . $offset . ',' . $pageSize);
        } else {
            $res = $model->query('select * from qw_project_group order by id desc limit ' . $offset . ',' . $pageSize);
        }
        for ($i = 0; $i < count($res); $i++) {
            $map['del'] = '1';
            $map['gid'] = $res[$i]['id'];
            $data[0] = M('project_data')->where($map)->select();
            $data[1] = M('project_video')->where($map)->select();
            $data[2] = M('project_advertise')->where($map)->select();
            $res[$i]['project_total'] = count($data[0]) + count($data[1]) + count($data[2]);
            for ($j = 0; $j < count($data); $j++) {
                foreach ($data[$j] as $key) {
                    $res[$i]['user_total'] += count(M('project_user')->where(array('type' => ($j + 1), 'gid' => $key['id']))->select());
                }
            }

        }
        $count = count(M('project_group')->select());
        $page = new  \Think\Page($count, $pageSize);
        $page = $page->show();
        $this->assign('page', $page);
        $this->assign('data', $res);
        $this->display();
    }

    function data()
    {
        if (IS_POST) {
            if ($_POST['key'] == 'add') {
                $_POST['time'] = time();
                $_POST['gid'] = S('id');
                if (M('project_data')->add($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,添加失败'));
                }
            } elseif ($_POST['key'] == 'select') {
                $map['id'] = $_POST['id'];
                $data = M('project_data')->where($map)->find();
                $this->ajaxReturn($data);
            } elseif ($_POST['key'] == 'edit') {
                $id['id'] = $_POST['id'];
                if ($_POST['cover'] == '') {
                    unset($_POST['cover']);
                }
                if ($_POST['fileurl'] == '') {
                    unset($_POST['fileurl']);
                }
                unset($_POST['id']);
                unset($_POST['key']);
                if (M('project_data')->where($id)->save($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,修改失败'));
                }
            } elseif ($_POST['key'] == 'del') {
                unset($_POST['key']);
                $update['del'] = '0';
                if (M('project_data')->where($_POST)->save($update)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '删除成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,删除失败'));
                }
            } elseif ($_POST['key'] == 'dels') {
                unset($_POST['key']);
                $map['del'] = '0';
                if (M('project_data')->where('id in (' . $_POST['id'] . ')')->save($map)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '删除成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,删除失败'));
                }
            }
        } elseif (IS_GET) {
            if (@!empty($_GET['id'])) {
                S('id', $_GET['id'], 0);
            }
        }
        $pageSize = 10;
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $offset = $pageSize * ($p - 1);
        $model = new \Think\Model();
        if (@$_GET['like'] != '') {
            $like = $_GET['like'];
            $data = $model->query('select * from qw_project_data where gid="' . S('id') . '" and del = "1" and name LIKE "%' . $like . '%" order by id desc limit ' . $offset . ',' . $pageSize);
        } else {
            $data = $model->query('select * from qw_project_data where gid="' . S('id') . '" and del = "1" order by id desc limit ' . $offset . ',' . $pageSize);
        }
        for ($i = 0; $i < count($data); $i++) {
            if (time() >= ($data[$i]['time'] + (3600 * $data[$i]['ontime']))) {
                $id['id'] = $data[$i]['id'];
                $update['type'] = '0';
                M('project_data')->where($id)->save($update);
            }
            if ($data[$i]['pattern'] == '1') {
                $data[$i]['pattern'] = '获取手机号';
            } else {
                $data[$i]['pattern'] = '砍价';
            }
            if ($data[$i]['type'] == '1') {
                $data[$i]['type'] = '进行中';
            } else {
                $data[$i]['type'] = '已结束';
            }
            $map['gid'] = $data[$i]['id'];
            $map['type'] = '1';
            $data[$i]['user'] = count(M('project_user')->where($map)->select());
            $data[$i]['time'] = date('Y-m-d H:i:s', $data[$i]['time']);
        }
        $map['del'] = "1";
        $map['gid'] = S('id');
        $count = count(M('project_data')->where($map)->select());
        $page = new  \Think\Page($count, $pageSize);
        $page = $page->show();
        $this->assign('page', $page);
        $this->assign('data', $data);
        $this->display();
    }

    function upload()
    {
        $dir = iconv("UTF-8", "GBK", "public/appUpload");
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $name = time() . strstr($_FILES['file']['name'], '.');
        $url = $dir . '/' . $name;
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $url)) {
            $this->ajaxReturn(array('code' => true, 'url' => $url));
        } else {
            $this->ajaxReturn(array('code' => false, 'msg' => '上传失败，上传文件过大'));
        }
    }

    function video()
    {
        if (IS_POST) {

            if ($_POST['key'] == 'add') {
                unset($_POST['key']);
                $_POST['time'] = time();
                $_POST['gid'] = S('id');
                if (M('project_video')->add($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,添加失败'));
                }
            } elseif ($_POST['key'] == 'select') {
                $map['id'] = $_POST['id'];
                $data = M('project_video')->where($map)->find();
                $this->ajaxReturn($data);
            } elseif ($_POST['key'] == 'edit') {

                $id['id'] = $_POST['id'];
                if ($_POST['cover'] == '') {
                    unset($_POST['cover']);
                }
                if ($_POST['fileurl'] == '') {
                    unset($_POST['fileurl']);
                }
                unset($_POST['id']);
                unset($_POST['key']);
                if (M('project_video')->where($id)->save($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,修改失败'));
                }
            } elseif ($_POST['key'] == 'del') {
                unset($_POST['key']);
                $update['del'] = '0';
                if (M('project_video')->where($_POST)->save($update)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '删除成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,删除失败'));
                }
            } elseif ($_POST['key'] == 'dels') {
                unset($_POST['key']);
                $map['del'] = '0';
                if (M('project_video')->where('id in (' . $_POST['id'] . ')')->save($map)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '删除成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,删除失败'));
                }
            }
        }
        $pageSize = 10;
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $offset = $pageSize * ($p - 1);
        $model = new \Think\Model();
        if (@$_GET['like'] != '') {
            $like = $_GET['like'];
            $data = $model->query('select * from qw_project_video where gid="' . S('id') . '" and del = "1" and name LIKE "%' . $like . '%" order by id desc limit ' . $offset . ',' . $pageSize);
        } else {
            $data = $model->query('select * from qw_project_video where gid="' . S('id') . '" and del = "1" order by id desc limit ' . $offset . ',' . $pageSize);
        }
        for ($i = 0; $i < count($data); $i++) {
            if (time() >= ($data[$i]['time'] + (3600 * $data[$i]['ontime']))) {
                $id['id'] = $data[$i]['id'];
                $update['type'] = '0';
                M('project_video')->where($id)->save($update);
            }
            if ($data[$i]['type'] == '1') {
                $data[$i]['type'] = '进行中';
            } else {
                $data[$i]['type'] = '已结束';
            }
            if ($data[$i]['pattern'] == '1') {
                $data[$i]['pattern'] = '获取手机号';
            } else {
                $data[$i]['pattern'] = '砍价';
            }
            $map['gid'] = $data[$i]['id'];
            $map['type'] = '2';
            $data[$i]['user'] = count(M('project_user')->where($map)->select());
            $data[$i]['time'] = date('Y-m-d H:i:s', $data[$i]['time']);
        }
        $map['del'] = "1";
        $map['gid'] = S('id');
        $count = count(M('project_video')->where($map)->select());
        $page = new  \Think\Page($count, $pageSize);
        $page = $page->show();
        $this->assign('page', $page);
        $this->assign('data', $data);
        $this->display();
    }

    function advertise()
    {
        if (IS_POST) {
            if ($_POST['key'] == 'add') {
                unset($_POST['key']);
                $_POST['time'] = time();
                $_POST['gid'] = S('id');
                if (M('project_advertise')->add($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,添加失败'));
                }
            } else if ($_POST['key'] == 'select') {
                unset($_POST['key']);
                $data = M('project_advertise')->where($_POST)->find();
                $this->ajaxReturn($data);
            } else if ($_POST['key'] == 'edit') {
                unset($_POST['key']);
                if ($_POST['fileurl'] == '') {
                    unset($_POST['fileurl']);
                }
                $map['id'] = $_POST['id'];
                unset($_POST['id']);
                if (M('project_advertise')->where($map)->save($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '修改成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,修改失败'));
                }
            } elseif ($_POST['key'] == 'del') {
                unset($_POST['key']);
                $update['del'] = '0';
                if (M('project_advertise')->where($_POST)->save($update)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '删除成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,删除失败'));
                }
            } elseif ($_POST['key'] == 'dels') {
                unset($_POST['key']);
                $map['del'] = '0';
                if (M('project_advertise')->where('id in (' . $_POST['id'] . ')')->save($map)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '删除成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,删除失败'));
                }
            }
        }
        $pageSize = 10;
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $offset = $pageSize * ($p - 1);
        $model = new \Think\Model();
        if (@$_GET['like'] != '') {
            $like = $_GET['like'];
            $data = $model->query('select * from qw_project_advertise where gid="' . S('id') . '" and del = "1" and name LIKE "%' . $like . '%" order by id desc limit ' . $offset . ',' . $pageSize);
        } else {
            $data = $model->query('select * from qw_project_advertise where gid="' . S('id') . '" and del = "1" order by id desc limit ' . $offset . ',' . $pageSize);
        }
        for ($i = 0; $i < count($data); $i++) {
            if (time() >= ($data[$i]['time'] + (3600 * $data[$i]['ontime']))) {
                $id['id'] = $data[$i]['id'];
                $update['type'] = '0';
                M('project_advertise')->where($id)->save($update);
            }
            if ($data[$i]['type'] == '1') {
                $data[$i]['type'] = '进行中';
            } else {
                $data[$i]['type'] = '已结束';
            }
            $data[$i]['time'] = date('Y-m-d H:i:s', $data[$i]['time']);
        }
        $map['del'] = "1";
        $map['gid'] = S('id');
        $count = count(M('project_advertise')->where($map)->select());
        $page = new  \Think\Page($count, $pageSize);
        $page = $page->show();
        $this->assign('page', $page);
        $this->assign('data', $data);
        $this->display();
    }

    function recycle()
    {
        if (IS_POST) {
            if ($_POST['key'] == 'edit') {
                unset($_POST['key']);
                $update['del'] = '1';
                if ($_POST['type'] == '资料') {
                    $mysql = 'project_data';
                } elseif ($_POST['type'] == '视频') {
                    $mysql = 'project_video';
                } elseif ($_POST['type'] == '广告') {
                    $mysql = 'project_advertise';
                }
                unset($_POST['type']);
                if (M($mysql)->where($_POST)->save($update)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '还原成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '网络繁忙,还原失败'));
                }
            } elseif ($_POST['key'] == 'edits') {
                unset($_POST['key']);
                $array = explode(',', $_POST['id']);
                $strOk = '';
                $strOn = '';
                $type = true;
                foreach ($array as $key) {
                    $arr = explode('-', $key);
                    if ($arr[1] == '资料') {
                        $mysql = 'project_data';
                    } elseif ($arr[1] == '视频') {
                        $mysql = 'project_video';
                    } elseif ($arr[1] == '广告') {
                        $mysql = 'project_advertise';
                    }
                    $map['id'] = $arr[0];
                    $update['del'] = '1';
                    if (M($mysql)->where($map)->save($update)) {
                        $strOk .= $map['id'] . '.';
                    } else {
                        $strOn .= $map['id'] . '.';
                        $type = false;
                    }
                }

                if ($type) {
                    $this->ajaxReturn(array('msg' => '还原成功'));
                } else {
                    $this->ajaxReturn(array('msg' => '编号为' . $strOk . '还原成功,编号为' . $strOn . '还原失败'));
                }
            }
        }
        $map['del'] = '0';
        $map['gid'] = S('id');
        $data[0] = M('project_data')->where($map)->order('id desc')->select();
        $data[1] = M('project_video')->where($map)->order('id desc')->select();
        $data[2] = M('project_advertise')->where($map)->order('id desc')->select();
        for ($i = 0; $i < count($data); $i++) {
            for ($r = 0; $r < count($data[$i]); $r++) {
                $data[$i][$r]['time'] = date('Y-m-d H:i:s', $data[$i][$r]['time']);
                if ($i == 0) {
                    $data[$i][$r]['type'] = '资料';
                } elseif ($i == 1) {
                    $data[$i][$r]['type'] = '视频';
                } elseif ($i == 2) {
                    $data[$i][$r]['type'] = '广告';
                }
            }
        }
        foreach ($data as $key) {
            foreach ($key as $val) {
                $res[] = $val;
            }
        }
        $pageSize = 10;
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $offset = $pageSize * ($p - 1);

        for ($i = 0; $i < $pageSize; $i++) {
            if ($res[$i + $offset] == '') {
                break;
            }
            if (@!empty($_GET['like'])) {
                if ($res[$i + $offset]['name'] == $_GET['like']) {
                    $arr[] = $res[$i + $offset];
                }
            } else {
                $arr[] = $res[$i + $offset];
            }
        }

        $page = new  \Think\Page(count($res), $pageSize);
        $page = $page->show();
        $this->assign('page', $page);
        $this->assign('data', $arr);
        $this->display();
    }

    function user_list()
    {
        $pageSize = 20;
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $offset = $pageSize * ($p - 1);
        $map['type'] = $_GET['type'];
        $map['gid'] = $_GET['id'];
        $data = M('project_user')->where($map)->order('id desc')->limit($offset, $pageSize)->select();
        for ($i=0;$i<count($data);$i++){
            $data[$i]['time']=date('Y-m-d H:i:s',$data[$i]['time']);
        }
        $count = count(M('project_user')->where($map)->order('id desc')->select());
        $page = new  \Think\Page($count, $pageSize);
        $page = $page->show();
        $this->assign('page', $page);
        $this->assign('data', $data);
        $this->display();
    }

    function statistics()
    {
        $model = new \Think\Model();
        if (IS_POST) {
            $time = strtotime(date('Ymd'), true);
            if (!empty($_POST['key'])) {
                S('key', $_POST['key']);
            } else {
                S('key', null);
            }

            if ($_POST['type'] == 'type') {
                if ($_POST['id'] != '0') {
                    S('g_id', $_POST['id'], 0);
                } else {
                    S('g_id', null);
                }
            }
            $key = S('key');

            if ($key == '1') {
                $array = array('time' => $time - (3600 * 24), 'int' => 24, 'date' => 'H:i', 'times' => 3600, 'title' => '昨日人数统计');
            } elseif ($key == '2') {
                $array = array('time' => $time - (3600 * 24 * 6), 'int' => 7, 'date' => 'm-d', 'times' => 3600 * 24, 'title' => '7日人数统计');
            } elseif ($key == '3') {
                $array = array('time' => $time - (3600 * 24 * 29), 'int' => 30, 'date' => 'm-d', 'times' => 3600 * 24, 'title' => '30日人数统计');
            } else {
                $array = array('time' => $time, 'int' => 24, 'date' => 'H:i', 'times' => 3600, 'title' => '今日人数统计');
            }

            for ($i = 0; $i < $array['int']; $i++) {
                $data['time'][$i] = date($array['date'], $array['time']);
                if (S('g_id')) {
                    $map['gid'] = S('g_id');
                    $map['del'] = "1";
                    $res_data = M('project_data')->where($map)->select();
                    $res_video = M('project_video')->where($map)->select();
                    foreach ($res_data as $key) {
                        $data['data'][$i] += count($model->query('select * from qw_project_user where gid = "' . $key['id'] . '" and time >= "' . $array['time'] . '" and time < "' . ($array['time'] + $array['times']) . '" and type ="1"'));
                    }
                    foreach ($res_video as $key) {
                        $data['video'][$i] += count($model->query('select * from qw_project_user where gid = "' . $key['id'] . '" and time >= "' . $array['time'] . '" and time < "' . ($array['time'] + $array['times']) . '" and type ="2"'));
                    }
                    $data['total'][$i] = $data['data'][$i] + $data['video'][$i];

                } else {
                    $data['data'][$i] = count($model->query('select * from qw_project_user where time >= "' . $array['time'] . '" and time < "' . ($array['time'] + $array['times']) . '" and type ="1"'));
                    $data['video'][$i] = count($model->query('select * from qw_project_user where time >= "' . $array['time'] . '" and time < "' . ($array['time'] + $array['times']) . '" and type ="2"'));
                    $data['total'][$i] = $data['data'][$i] + $data['video'][$i];
                }
                if ($array['time'] + 3600 > time()) {
                    break;
                }
                $array['time'] += $array['times'];
            }

            $data['title'] = $array['title'];


            $this->ajaxReturn($data);
        } else {
            $data = M('project_group')->select();
            $this->assign('data', $data);
            $this->display();
        }

    }


}
