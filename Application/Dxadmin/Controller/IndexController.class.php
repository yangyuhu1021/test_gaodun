<?php

namespace Dxadmin\Controller;

class IndexController extends ComController
{
    public function index()
    {
        $user = cookie('user');
        if (IS_POST) {
            if ($_GET['type'] == '1') {
                $model = new \Think\Model();
                $type = $model->query('SELECT * FROM qw_admin_user where uid = "'.$user['uid'].'" limit 1');
                if ($type[0]['did'] == '0') {
                    $data = $model->query('SELECT * FROM qw_auth_rule where islink = "1"');
                    for ($i = 0; $i < count($data); $i++) {
                        $res[$i]['id'] = $data[$i]['id'];
                        $res[$i]['text'] = $data[$i]['title'];
                        $res[$i]['pid'] = $data[$i]['pid'];
                    }
                    $arr = $this->getList($res, 0);
                    $this->ajaxReturn($arr);
                }else{
                    $department = $model->query('SELECT * FROM qw_auth_department where id = "'.$type[0]['did'].'" and type="1"');
                    $auth = $model->query('SELECT * FROM qw_auth_group where uid = "'.$department[0]['id'].'"');
                    $authArr=explode(',',$auth[0]['group_id']);
                    $res = $model->query('SELECT * FROM qw_auth_rule where pid=0 and islink="1" and type="1"');
                    $i=0;
                    foreach ($res as $key1){
                        $data[$i]['id'] = $key1['id'];
                        $data[$i]['text'] = $key1['title'];
                        $data[$i]['pid'] =$key1['pid'];
                        $two = $model->query('SELECT * FROM qw_auth_rule where pid="'.$key1['id'].'" and islink="1" and type="1"');
                        $type=true;
                        foreach ($two as $key2){
                            foreach ($authArr as $group){
                                if($key2['id'] == $group){
                                    $type=false;
                                    $i++;
                                    $data[$i]['id'] = $key2['id'];
                                    $data[$i]['text'] = $key2['title'];
                                    $data[$i]['pid'] =$key2['pid'];
                                    $three = $model->query('SELECT * FROM qw_auth_rule where pid="'.$key2['id'].'" and islink="1" and type="1"');
                                    foreach ($three as $key3){
                                        $i++;
                                        $data[$i]['id'] = $key3['id'];
                                        $data[$i]['text'] = $key3['title'];
                                        $data[$i]['pid'] =$key3['pid'];
                                    }
                                }
                            }
                        }
                        if($type){
                            unset($data[$i]['id']);
                            unset($data[$i]['text']);
                            unset($data[$i]['pid']);
                        }else{
                            $i++;
                        }

                    }
                    foreach ($data as $val){
                        if(count($val) > 0){
                            $arr[]=$val;
                        }
                    }
                    $arr = $this->getList($arr, 0);
                    $this->ajaxReturn($arr);
                }
            } elseif ($_GET['type'] == '2') {
                $Model = new \Think\Model(); // 实例化一个model对象 没有对应任何数据表
                $res = $Model->query("select * from qw_auth_rule where id='" . $_POST['id'] . "'");
                $this->ajaxReturn($res[0]['name']);
            }
        } else {
            $this->assign('user', $user['user']);
            $this->display();
        }
    }

    private function getList($data, $pid)
    {
        foreach ($data as $key) {
            if ($key['pid'] == $pid) {
                $key['nodes'] = $this->getList($data, $key['id']);
                if (isset($key['nodes'])) {
                    $key['selectable'] = false;
                } else {
                    $key['nodeid'] = $key['id'];
                    unset($key['nodes']);
                }
                unset($key['pid']);
                unset($key['id']);
                $list[] = $key;
            }
        }
        return $list;
    }
}
