<?php

namespace Portal\Controller;

use Dxadmin\Controller\CropBaseController;

class IndexController extends CropBaseController
{
    /**
     * 首页
     */
    public function index(){
        $list=M('app_article_view')->where(array('is_recommend'=>1,'delete_time'=>0,'status'=>1))->select();
        $this->assign('list',$list);
        $this->assign('categories',getCategories());
        $this->display();
    }

    /**
     * 推荐
     */
    public function hot(){
        $list=M('app_article_view')->where(array('is_hot'=>1,'delete_time'=>0,'status'=>1))->select();
        $this->assign('list',$list);
        $this->assign('categories',getCategories());
        $this->display();
    }

    /**
     * 分类
     */
    public function category(){
        $id=I('get.id',0,'intval');
        $list=M('app_article_view')->where(array('delete_time'=>0,'status'=>1,'category_id'=>$id))->select();
        $this->assign('list',$list);
        $this->assign('categories',getCategories());
        $this->display();
    }

    /**
     * 详情
     */
    public function detail(){
        $id=I('get.id',0,'intval');
        $info=M('app_article_view')->where(array('delete_time'=>0,'status'=>1,'id'=>$id))->find();
        if(empty($info)){
            $this->error('文章不存在');
        }
        $this->assign($info);
        $this->display();
    }

}
