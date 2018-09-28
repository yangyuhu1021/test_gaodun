<?php

namespace Dxadmin\Controller;

class WechatCodeController extends ComController
{

    function index(){
        $model=new  \Think\Model();
        $user = cookie('user');
        $time=strtotime(date('Y-m-d',time()));
        $data['user']=$user['user'];
        $data['today']=count($model->query('SELECT * FROM qw_user_code where time >= "'.$time.'" and time < "'.($time+(3600*24)).'"'));
        $data['yesterday']=count($model->query('SELECT * FROM qw_user_code where time >= "'.($time-(3600*24)).'" and time < "'.$time.'"'));
        $data['total']=count($model->query('SELECT * FROM qw_user_code '));
        if (IS_POST) {
            $model=new  \Think\Model();
            $time = strtotime(date('Y-m-d', time())) - (3600 * 24 * 6);
            for ($i = 0; $i < 7; $i++) {
                $data['time'][$i] = date('m-d', $time + (3600 * 24 * $i));
                $data['number'][$i] = count($model->query('SELECT * FROM qw_user_code where time >= "'.($time+(3600*24*$i)).'" and time < "'.($time+(3600*24*($i+1))).'"'));
            }

            $this->ajaxReturn($data);
        }
        $this->assign('data',$data);
        $this->display();
    }

    function one(){
        if(IS_POST){
            $model=new \Think\Model();
            $data=$model->query('SELECT * FROM qw_group_code WHERE id="'.$_POST['id'].'" limit 1');
            $this->ajaxReturn($data[0]);
        }
        $pageSize=10;
        $p = isset($_GET['p'])?intval($_GET['p']):'1';
        $offset = $pageSize*($p-1);
        $timeON=strtotime(date('Y-m-d',time()));
        $timeOFF=strtotime(date('Y-m-d',time()))+(3600*24);
        $model=new  \Think\Model();
        if(@$_GET['like'] != ''){
            $like=$_GET['like'];
            $code_group=$model->query('SELECT * FROM qw_group_code WHERE type="1" and  name LIKE "%'.$like.'%" limit '.$offset.','.$pageSize);
        }else{
            $code_group=$model->query('SELECT * FROM qw_group_code WHERE type="1" limit '.$offset.','.$pageSize);
        }
        $count_res=$model->query('SELECT * FROM qw_group_code WHERE type="1"');
        $count=count($count_res);

        $page=new  \Think\Page($count,$pageSize);
        $page=$page->show();
        $i=0;
        foreach ( $code_group as $key) {
            $data[$i]['name']=$key['name'];
            $data[$i]['id']=$key['id'];
            $one_code=$model->query('SELECT * FROM qw_one_code WHERE group_id="'.$key['id'].'"');
            $data[$i]['ones']=count($one_code);
            $is_ok=0;
            foreach($one_code as $code){
                if($code['type'] > '0'){
                    $is_ok++;
                }
                $data[$i]['is_ok']=$is_ok;
                $data[$i]['is_number_s']   +=count($model->query('SELECT * FROM qw_user_code WHERE code_id="'.$code['id'].'" and type="1"'));
                $data[$i]['is_number_on']  +=count($model->query('SELECT * FROM qw_user_code WHERE code_id="'.$code['id'].'" and is_on="1" and type="1" '));
                $data[$i]['is_number_one'] +=count($model->query('SELECT * FROM qw_user_code WHERE code_id="'.$code['id'].'" and time >= "'.$timeON.'" and time < "'.$timeOFF.'"  and type="1"'));
                $data[$i]['is_number_ones']+=count($model->query('SELECT * FROM qw_user_code WHERE code_id="'.$code['id'].'" and time >= "'.$timeON.'" and time < "'.$timeOFF.'"  and is_on="1" and type="1" '));
            }
            $i++;
        }
        for($i=0;$i<count($data);$i++){
            if(empty($data[$i]['is_ok'])){
                $data[$i]['is_ok']='0';
            }
            if(empty($data[$i]['is_number_one'])){
                $data[$i]['is_number_one']='0';
            }
            if(empty($data[$i]['is_number_ones'])){
                $data[$i]['is_number_ones']='0';
            }
            if(empty($data[$i]['is_number_s'])){
                $data[$i]['is_number_s']='0';
            }
            if(empty($data[$i]['is_number_on'])){
                $data[$i]['is_number_on']='0';
            }
        }
        if(count($data)>$pageSize){
            for($i=0;$i<$pageSize;$i++){
                $res[$i]=$data[$i];
            }
        }else{
            for($i=0;$i<count($data);$i++){
                $res[$i]=$data[$i];
            }
        }
        $url=$model->query('SELECT * FROM qw_wxCode_url WHERE id=1 limit 1');
        $this->assign('url',$url[0]);
        $this->assign('data',$res);
        $this->assign('page',$page);
        $this->display();
    }

    function add_code(){
        if(IS_POST){
            if(M('group_code')->add($_POST)){
                $this->ajaxReturn(array('code'=>true,'msg'=>'添加成功'));
            }else{
                $this->ajaxReturn(array('code'=>false,'msg'=>'网络繁忙，添加失败'));
            }
        }
        $model=new \Think\Model();
        $url=$model->query('SELECT * FROM qw_wxCode_url WHERE id=1 limit 1');
        $data=M('group_code')->order('id desc')->find();
        $url='https://'.$url[0]['url'].'?id='.($data['id']+1).'&type='.$_GET['type'];
        $img=$this->qrcode($url);
        $this->assign('type',$_GET['type']);
        $this->assign('imgUrl',$img);
        $this->assign('url',$url);
        $this->display();
    }
    public function qrcode($url){
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
    }

    function edit_code(){
        if(IS_POST){
            $id=$_GET['id'];
            $model=new \Think\Model();
            $res=$model->execute('update qw_group_code set name="'.$_POST['name'].'" ,img="'.$_POST['img'].'",type="'.$_POST['type'].'",`key`="'.$_POST['key'].'",top_code="'.$_POST['top_code'].'",bottom_code="'.$_POST['bottom_code'].'" where id="'.$id.'" ');
            if($res){
                $this->ajaxReturn(array('code'=>true,'msg'=>'修改成功'));
            }else{
                $this->ajaxReturn(array('code'=>false,'msg'=>'网络繁忙，修改失败'));
            }
        }
        $model=new \Think\Model();
        $data=$model->query('SELECT * FROM qw_group_code WHERE id="'.$_GET['id'].'" limit 1');
        $url=$model->query('SELECT * FROM qw_wxCode_url WHERE id=1 limit 1');
        $url='https://'.$url[0]['url'].'?id='.$data[0]['id'].'&type=1';
        $this->assign('data',$data[0]);
        $this->assign('url',$url);
        $this->display();
    }
    function url(){
        download(substr($_GET['url'],1));
    }

}
