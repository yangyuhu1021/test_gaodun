<?php

namespace Dxadmin\Controller;

class AccaController extends ComController
{
    function index()
    {
        $pageSize = 20;
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $offset = $pageSize * ($p - 1);
        if($_GET['name'] != ''){
            $where['name']=$_GET['name'];
        }
        if($_GET['phone'] != ''){
            $where['phone']=$_GET['phone'];
        }
        if($_GET['email'] != ''){
            $where['email']=$_GET['email'];
        }
        if($_GET['type'] != ''&& $_GET['type'] != '0'){
            $where['type']=$_GET['type'];
        }
        $data=M('acca_info')->where($where)->order('id desc')->limit($offset,$pageSize)->select();
        for ($i=0;$i<count($data);$i++){
            $data[$i]['time']=date('Y-m-d H:i:s',$data[$i]['time']);
        }
        $count=count(M('acca_info')->select());
        $page = new  \Think\Page($count, $pageSize);
        $page = $page->show();
        $this->assign('page', $page);
        $this->assign('data', $data);
        $this->display();
    }
    function select(){
        $pageSize = 20;
        $p = isset($_GET['p']) ? intval($_GET['p']) : '1';
        $offset = $pageSize * ($p - 1);
        if($_GET['phone'] != ''){
            $where['phone']=$_GET['phone'];
        }
        $data=M('acca_info')->where($where)->order('id desc')->limit($offset,$pageSize)->select();
        for ($i=0;$i<count($data);$i++){
            $data[$i]['time']=date('Y-m-d H:i:s',$data[$i]['time']);
        }
        $count=count(M('acca_info')->select());
        $page = new  \Think\Page($count, $pageSize);
        $page = $page->show();
        $this->assign('page', $page);
        $this->assign('data', $data);
        $this->display();
    }

}