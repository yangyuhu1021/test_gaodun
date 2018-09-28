<?php

namespace Dxadmin\Controller;

class AuthController extends ComController
{
    function user()
    {
        if (IS_POST) {
            if ($_POST['key'] == 'add') {
                unset($_POST['key']);
                $_POST['time'] = time();
                $_POST['password'] = password($_POST['password']);
                $data = M('admin_user')->where(array('user' => $_POST['user']))->find();
                if ($data != '') {
                    $this->ajaxReturn(array('code' => false, 'msg' => '该成员已存在'));
                }
                if (M('admin_user')->add($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '添加成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '添加失败'));
                };
            } elseif ($_POST['key'] == 'select') {
                $data['data'] = M('admin_user')->where(array('uid' => $_POST['id']))->find();
                $data['d']=M('auth_department')->where(array('del'=>'1'))->select();
                $this->ajaxReturn($data);
            } elseif ($_POST['key'] == 'del') {
                if (M('admin_user')->where(array('uid' => $_POST['id']))->save(array('del' => '0'))) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '删除成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '删除失败'));
                };
            } elseif ($_POST['key'] == 'type') {
                $type = $_POST['type'] == '1' ? '0' : '1';
                $text = $_POST['type'] == '1' ? '关闭' : '启用';
                if (M('admin_user')->where(array('uid' => $_POST['id']))->save(array('type' => $type))) {
                    $this->ajaxReturn(array('code' => true, 'msg' => $text . '成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => $text . '失败'));
                };
            } elseif ($_POST['key'] == 'edit') {
                unset($_POST['key']);
                $id = $_POST['id'];
                $_POST['password'] = password($_POST['password']);
                unset($_POST['id']);
                if (M('admin_user')->where(array('uid' => $id))->save($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '编辑成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '编辑失败'));
                };
            }
        } else {
            $pageSize = 20;
            $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
            $offset = $pageSize * ($p - 1);
            if (@!empty($_GET['name']) && @empty($_GET['d'])) {
                $data = M('admin_user')->where(array('del' => '1', 'name' => ['like', '%' . $_GET['name'] . '%']))->limit($offset, $pageSize)->select();
            } elseif (@empty($_GET['name']) && @!empty($_GET['d'])) {
                $data = M('admin_user')->where(array('del' => '1', 'did' => $_GET['d']))->limit($offset, $pageSize)->select();
            } elseif (@!empty($_GET['name']) && @!empty($_GET['d'])) {
                $data = M('admin_user')->where(array('del' => '1', 'name' => ['like', '%' . $_GET['name'] . '%'], 'did' => $_GET['d']))->limit($offset, $pageSize)->select();
            } else {
                $data = M('admin_user')->where(array('del' => '1'))->limit($offset, $pageSize)->select();
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['time'] = date('Y-m-d H:i:s', $data[$i]['time']);
                $data[$i]['logintime'] = date('Y-m-d H:i:s', $data[$i]['logintime']);
                $department = M('auth_department')->where(array('id' => $data[$i]['did']))->find();
                $data[$i]['department'] = $department['name'];
            }
            $count = count(M('admin_user')->where(array('del' => '1'))->select());
            $page = new  \Think\Page($count, $pageSize);
            $page = $page->show();
            $select = M('auth_department')->where(array('del' => '1'))->select();
            $this->assign('page', $page);
            $this->assign('select', $select);
            $this->assign('data', $data);
            $this->display();
        }
    }

    function department()
    {
        if (IS_POST) {
            if ($_POST['key'] == 'add') {
                unset($_POST['key']);
                $_POST['time'] = time();
                if (M('auth_department')->add($_POST)) {
                    $data = M('auth_department')->where($_POST)->find();
                    if(M('auth_group')->add(array('uid' => $data['id']))){
                        $this->ajaxReturn(array('code' => true, 'msg' => '添加成功'));
                    }else{
                        $this->ajaxReturn(array('code' => false, 'msg' => '未知错误'));
                    };
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '添加失败'));
                };
            } elseif ($_POST['key'] == 'select') {
                $data = M('auth_department')->where(array('id' => $_POST['id']))->find();
                $this->ajaxReturn($data);
            } elseif ($_POST['key'] == 'del') {
                if (M('auth_department')->where(array('id' => $_POST['id']))->save(array('del' => '0'))) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '删除成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '删除失败'));
                };
            } elseif ($_POST['key'] == 'type') {
                $type = $_POST['type'] == '1' ? '0' : '1';
                $text = $_POST['type'] == '1' ? '关闭' : '启用';
                if (M('auth_department')->where(array('id' => $_POST['id']))->save(array('type' => $type))) {
                    $this->ajaxReturn(array('code' => true, 'msg' => $text . '成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => $text . '失败'));
                };
            } elseif ($_POST['key'] == 'edit') {
                unset($_POST['key']);
                $id = $_POST['id'];
                unset($_POST['id']);
                if (M('auth_department')->where(array('id' => $id))->save($_POST)) {
                    $this->ajaxReturn(array('code' => true, 'msg' => '编辑成功'));
                } else {
                    $this->ajaxReturn(array('code' => false, 'msg' => '编辑失败'));
                };
            }
        } else {
            $pageSize = 20;
            $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
            $offset = $pageSize * ($p - 1);
            $data = M('auth_department')->where(array('del' => '1'))->limit($offset, $pageSize)->select();
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['time'] = date('Y-m-d H:i:s', $data[$i]['time']);
                $data[$i]['number'] = count(M('admin_user')->where(array('did' => $data[$i]['id'],'del'=>'1'))->select());
            }
            $count = count(M('auth_department')->where(array('del' => '1'))->select());
            $page = new  \Think\Page($count, $pageSize);
            $page = $page->show();
            $this->assign('page', $page);
            $this->assign('data', $data);
            $this->display();
        }
    }

    function group()
    {
        if(IS_POST){
            if(M('auth_group')->where(array('uid'=>$_POST['id']))->save(array('group_id'=>$_POST['str']))){
                $this->ajaxReturn(array('code' => true, 'msg' => '编辑成功'));
            } else {
                $this->ajaxReturn(array('code' => false, 'msg' => '编辑失败'));
            }
        }else{
            $res = M('auth_rule')->where(array('pid' => 0, 'islink' => '1', 'type' => '1'))->select();
            $auth=M('auth_group')->where(array('uid'=>$_GET['id']))->find();
            $authArr=explode(',',$auth['group_id']);
            for ($i = 0; $i < count($res); $i++) {
                $res[$i]['two'] = M('auth_rule')->where(array('pid' => $res[$i]['id'], 'islink' => '1', 'type' => '1'))->select();
                for($j=0;$j<count($res[$i]['two']);$j++){
                    foreach ($authArr as $id){
                        if($res[$i]['two'][$j]['id'] == $id){
                            $res[$i]['two'][$j]['type']=true;
                            break;
                        }else{
                            $res[$i]['two'][$j]['type']=false;
                        }
                    }
                }
            }
            $this->assign('res', $res);
            $this->assign('id', $_GET['id']);
            $this->display();
        }
    }
}