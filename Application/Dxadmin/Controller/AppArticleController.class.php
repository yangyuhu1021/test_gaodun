<?php

namespace Dxadmin\Controller;

/**
 * 文章管理
 * Class AppArticleController
 * @package Dxadmin\Controller
 * @author Tiger
 * @date 2018/9/23
 */
class AppArticleController extends ComController
{
    /**
     * 文章列表
     */
    public function index(){
        $p=I('request.p',1,'intval');
        $where =array('delete_time'=>0);
        $category_id=I('request.category_id',0,'intval');
        if(!empty($category_id)){
            $where['category_id']=$category_id;
        }
        $status=I('request.status',0,'intval');
        if(!empty($status)){
            switch ($status){
                case 1:
                    $where['category_id']=1;
                    break;
                case 2:
                    $where['category_id']=0;
                    break;
            }
        }

        $start_time=I('request.start_time');
        if(!empty($start_time)){
            $where['create_time']=array(
                array('EGT',$start_time)
            );
        }
        $end_time=I('request.end_time');
        if(!empty($end_time)){
            if(empty($where['create_time'])){
                $where['create_time']=array();
            }
            array_push($where['create_time'], array('ELT',$end_time));
        }

        $keyword=I('request.keyword','','htmlentities');
        if(!empty($keyword)){
            $keyword_complex=array();
            $keyword_complex['title']  = array('like',"%$keyword%");
            $keyword_complex['number']  = array('like',"%$keyword%");
            $keyword_complex['_logic'] = 'or';
            $where['_complex'] = $keyword_complex;

        }

        $model = M('app_article_view');
        $pagesize = 10;#每页数量
        $offset = $pagesize*($p-1);//计算记录偏移量
        $count = $model->where($where)->count();

        $list  = $model->where($where)->limit($offset.','.$pagesize)->select();

        $page	=	new \Think\Page($count,$pagesize);
        $page = $page->show();
        $this->assign('list',$list);
        $this->assign('page',$page);

        $categories = M('app_article_category')->field('id,name')->where(array('status'=>1))->select();
        $this->assign('categories',$categories);
        $this->assign($_REQUEST);
        $this -> display();
    }

    /*protected function qrcode($url){
        $save_path   =  'Public/qrcode/';  //图片存储的绝对路径
        $web_path    =  '/Public/qrcode/';        //图片在网页上显示的路径
        $qr_data     =  $url;                     //手机扫描后要跳转的网址
        $qr_level    =  'H';                  //默认纠错比例 分为L、M、Q、H四个等级，H代表最高纠错能力
        $qr_size     =  '4';                     //二维码图大小，1－10可选，数字越大图片尺寸越大
        $save_prefix =  md5($url);      //图片名称前缀
        if($filename = createQRcode($save_path,$qr_data,$qr_level,$qr_size,$save_prefix)){
            $pic = $web_path.$filename;
        }
        return $pic;
    }*/

    /**
     * 修改
     */
    public function add(){
        if(IS_POST){
            if(empty($_POST['category_id'])){
                $this->error('类目不能为空');
            }
            if(empty($_POST['title'])){
                $this->error('标题不能为空');
            }
            $category_name=M('app_article_category')->where(array('id'=>$_POST['category_id']))->getField('name');
            if(empty($category_name)){
                $this->error('类目不存在');
            }
            $data=$_POST;
            $data['content']=$_POST['editorValue'];
            unset($_POST['editorValue']);
            $data['create_time']=time();
            $numberBefore=$category_name.date('Ymd');
            $count=M('app_article')->where(array('number'=>array('like',$numberBefore.'%')))->count('id');
            $numberAfter=$count+1;
            $data['number']=$numberBefore.$numberAfter;

            $pattern="/<img.*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
            preg_match_all($pattern,htmlspecialchars_decode($data['content']),$match);
            $data['images']=json_encode($match);
            $data['images_num']=count($match);

            $row=M('app_article')
                ->field('title,number,author,abstract,thumbnail,content,category_id,create_time,url,images,images_num')
                ->add($data);
            if($row){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }else{
            $categories = M('app_article_category')->field('id,name')->where(array('status'=>1))->select();
            $this->assign('categories',$categories);
            $this -> display();
        }
    }

    /**
     * 修改
     */
    public function edit(){
        $id=I('request.id');
        if(IS_POST){
            if(empty($_POST['category_id'])){
                $this->error('类目不能为空');
            }
            if(empty($_POST['title'])){
                $this->error('标题不能为空');
            }
            $category_name=M('app_article_category')->where(array('id'=>$_POST['category_id']))->getField('name');
            if(empty($category_name)){
                $this->error('类目不存在');
            }
            $data=$_POST;
            $data['content']=$_POST['editorValue'];
            unset($_POST['editorValue']);

            $pattern="/<img.*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
            preg_match_all($pattern,htmlspecialchars_decode($data['content']),$match);
            $data['images']=json_encode($match);
            $data['images_num']=count($match);

            $row=M('app_article')->field('title,author,abstract,thumbnail,content,category_id,url,images,images_num')
                ->where(array('id'=>$id))->save($data);
            if($row){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }else{
            $info=M('app_article')->where(array('id'=>$id))->find();
            $categories = M('app_article_category')->field('id,name')->where(array('status'=>1))->select();
            $this->assign('info',$info);
            $this->assign('categories',$categories);
            $this -> display();
        }
    }

    /**
     * 发布
     */
    public function push(){
        $id=I('post.id');
        $type=I('post.type');
        if($type=='yes'){
            $row=M('app_article')->where(array('id'=>$id))->setField('status',1);
        }else{
            $row=M('app_article')->where(array('id'=>$id))->setField('status',0);
        }

        if($row!==false){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 推荐
     */
    public function recommend(){
        $id=I('post.id');
        $type=I('post.type');
        if($type=='yes'){
            $row=M('app_article')->where(array('id'=>$id))->setField('is_recommend',1);
        }else{
            $row=M('app_article')->where(array('id'=>$id))->setField('is_recommend',0);
        }
        if($row!==false){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 置顶
     */
    public function top(){
        $id=I('post.id');
        $type=I('post.type');
        if($type=='yes'){
            $row=M('app_article')->where(array('id'=>$id))->setField('is_top',1);
        }else{
            $row=M('app_article')->where(array('id'=>$id))->setField('is_top',0);
        }

        if($row!==false){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 热门
     */
    public function hot(){
        $id=I('post.id');
        $type=I('post.type');
        if($type=='yes'){
            $row=M('app_article')->where(array('id'=>$id))->setField('is_hot',1);
        }else{
            $row=M('app_article')->where(array('id'=>$id))->setField('is_hot',0);
        }

        if($row!==false){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 删除
     */
    public function delete(){
        $id=I('get.id');
        $row=M('app_article')->where(array('id'=>$id))->setField('delete_time',time());
        if($row!==false){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}