<?php

namespace Dxadmin\Controller;

/**
 * 文章类目管理
 * Class AppArticleCategoryController
 * @package Dxadmin\Controller
 * @author Tiger
 * @date 2018/9/23
 */
class AppArticleCategoryController extends ComController
{
    /**
     * 文章类目列表
     */
    public function index(){
        $categories1 = M('app_article_category')->order('list_order asc')
            ->where(array('status'=>1))->select();
        $categories2 = M('app_article_category')->order('list_order asc')
            ->where(array('status'=>0))->select();
        $list=array_merge($categories1,$categories2);
        $this->assign('list',$list);
        $this->assign('count',count($list));
        $this->assign($_REQUEST);
        $this -> display();
    }

    public function add(){
        $category_name=$_POST['name'];

        if(empty($category_name)){
            $this->error('类目名称不能为空');
        }
        $find=M('app_article_category')->where(array('name'=>$category_name))->find();
        if($find){
            $this->error($category_name.'已经存在');
        }

        $count=M('app_article_category')->where(array('status'=>1))->count();

        $data['name']=$category_name;
        $data['list_order']=$count+1;
        $data['status']=1;
        $row=M('app_article_category')->add($data);
        if($row){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }

    public function getInfo(){
        $id=I('request.id');
        $info=M('app_article_category')->where(array('id'=>$id))->find();
        if($info){
            $this->success($info);
        }else{
            $this->error('分类不存在');
        }

    }

    public function update(){
        $id=I('request.id');
        if(empty($_POST['name'])){
            $this->error('类目不能为空');
        }
        $row=M('app_article_category')
            ->where(array('id'=>$id))->save(array('name'=>$_POST['name']));
        if($row){
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }

    }

    public function push(){
        $id=I('post.id');
        $type=I('post.type');

        $info=M('app_article_category')->where(array('id'=>$id))->find();
        if(!$info){
            $this->error('分类不存在');
        }
        $model=M('app_article_category');
        $model->startTrans();

        if($type=='yes'){
            $count=M('app_article_category')->where(array('status'=>1))->count();
            $row=M('app_article_category')->where(array('id'=>$id))->save(array('status'=>1,'list_order'=>$count+1));
            $row2=M('app_article_category')
                ->where(array('status'=>$info['status'],'list_order'=>array('gt',$info['list_order'])))
                ->setDec('list_order');
        }else{
            $count=M('app_article_category')->where(array('status'=>0))->count();
            $row=M('app_article_category')->where(array('id'=>$id))->save(array('status'=>0,'list_order'=>$count+1));
            $row2=M('app_article_category')
                ->where(array('status'=>$info['status'],'list_order'=>array('gt',$info['list_order'])))
                ->setDec('list_order');
        }

        if($row!==false&&$row2!==false){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }


    public function listOrder(){
        $id=I('request.id');
        $type=I('request.type');

        $find=M('app_article_category')->where(array('id'=>$id))->find();
        $current_list_order=$find['list_order'];
        if($type==1) {
            $preOrder = $current_list_order - 1;//上移
        }else{
            $preOrder = $current_list_order + 1;//下移
        }
        $preId=M('app_article_category')->where(array('list_order'=>$preOrder,'status'=>$find['status']))->getField('id');
        if($preId){
            M('app_article_category')->where(array('id'=>$preId))->setField('list_order',$current_list_order);
            M('app_article_category')->where(array('id'=>$id))->setField('list_order',$preOrder);
        }

        $this->success('操作成功');

    }

    public function delete(){
        $id=I('get.id');
        $info=M('app_article_category')->where(array('id'=>$id))->find();
        if(!$info){
            $this->error('分类不存在');
        }
        $model=M('app_article_category');
        $model->startTrans();
        $row=M('app_article_category')->where(array('id'=>$id))->setField('delete_time',time());
        $row2=M('app_article_category')
            ->where(array('status'=>$info['status'],'list_order'=>array('gt',$info['list_order'])))
            ->setDec('list_order');
        if($row!==false&&$row2!==false){
            $model->commit();
            $this->success('删除成功');
        }else{
            $model->rollback();
            $this->error('删除失败');
        }
    }

}