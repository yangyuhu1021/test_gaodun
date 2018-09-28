<?php
namespace Dxadmin\Controller;

class OneCodeController extends ComController{

    function index(){
        $pageSize=25;
        $p = isset($_GET['p'])?intval($_GET['p']):'1';
        $offset = $pageSize*($p-1);
        $id=$_GET['id'];
        $model=new  \Think\Model();
        $res=$model->query('select * from qw_one_code where group_id="'.$id.'" and type ="1" and del = "1" order by id desc limit 1');
        $user=$model->query('select * from qw_user_code where code_id="'.$res[0]['id'].'" and is_on = "1" and type="1" ');
        if(count($user) >= $res[0]['key']){
            $model->execute('update qw_one_code set type="2" where id="'.$res[0]['id'].'"');
            $res=$model->query('select * from qw_one_code where group_id="'.$id.'" and type ="0" and del="1" limit 1');
            $model->execute('update qw_one_code set type="1" where id="'.$res[0]['id'].'"');
        }
        if(IS_POST){
            if(M('one_code')->add($_POST)){
                $this->ajaxReturn(array('code'=>true,'msg'=>'新增成功'));
            }else{
                $this->ajaxReturn(array('code'=>false,'msg'=>'网络繁忙，新增失败'));
            }
        }
        $timeON=strtotime(date('Y-m-d',time()));
        $timeOFF=strtotime(date('Y-m-d',time()))+(3600*24);
        if(@$_GET['like'] != ''){
            $like=$_GET['like'];
            $res=$model->query('select * from qw_one_code where group_id="'.$id.'" and  name LIKE "%'.$like.'%"  limit '.$offset.','.$pageSize);
        }else{
            $res=$model->query('select * from qw_one_code where group_id="'.$id.'" limit '.$offset.','.$pageSize);
        }
        $count_res=$model->query('select * from qw_one_code where group_id="'.$id.'"');
        $count=count($count_res);
        $page=new  \Think\Page($count,$pageSize);
        $page=$page->show();
        for($i=0;$i<count($res);$i++){
            $data[$i]['key']=$res[$i]['key'];
            $data[$i]['id']=$res[$i]['id'];
            $data[$i]['name']=$res[$i]['name'];
            $data[$i]['type']=$res[$i]['type'];
            $data[$i]['del']=$res[$i]['del'];
            $user=$model->query('select * from qw_user_code where code_id="'.$res[$i]['id'].'" and is_on = "1" and type="1" ');
            $data[$i]['is_an']=count($user);
            $user=$model->query('select * from qw_user_code where code_id="'.$res[$i]['id'].'" and time >= "'.$timeON.'" and time < "'.$timeOFF.'"and type="1" ');
            $data[$i]['is_today_kan']=count($user);
            $user=$model->query('select * from qw_user_code where code_id="'.$res[$i]['id'].'" and time >= "'.$timeON.'" and time < "'.$timeOFF.'" and is_on = "1" and type="1" ');
            $data[$i]['is_today_an']=count($user);
        }
        for($i=0;$i<count($data);$i++){
            if($data[$i]['type'] == '1'){
                $data[$i]['type']='正在使用';
            }elseif ($data[$i]['type'] == '2'){
                $data[$i]['type']='已使用';
            }else{
                $data[$i]['type']='未使用';
            }
			if($data[$i]['del'] == '0'){
                $data[$i]['type']='已停用';
            }
            if(empty($data[$i]['is_ok'])){
                $data[$i]['is_ok']='0';
            }
            if(empty($data[$i]['is_an'])){
                $data[$i]['is_an']='0';
            }
            if(empty($data[$i]['is_today_kan'])){
                $data[$i]['is_today_kan']='0';
            }
            if(empty($data[$i]['is_today_an'])){
                $data[$i]['is_today_an']='0';
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
        $this->assign('page',$page);
        $this->assign('data',$res);
        $this->assign('id',$id);
        $this->display();
    }

    function upLoad(){
        $imgName=time().strstr($_FILES['file']['name'],'.');
        $tmp = $_FILES['file']['tmp_name'];
        $dir = 'Public/upload';
        $imgUrl = $dir.'/'.$imgName;
        if (!file_exists($dir)){
            mkdir ($dir,0777,true);
        }
        if(move_uploaded_file($tmp,$imgUrl)){
            $this->ajaxReturn('/'.$imgUrl);
        }
    }
    function select_img(){
        $model=new \Think\Model();
        $data=$model->query('SELECT * FROM qw_one_code WHERE id="'.$_POST['id'].'" limit 1');
        $this->ajaxReturn($data[0]);
    }
    function url(){
        download(substr($_GET['url'],1));
    }
    function edit_code(){

        $model=new \Think\Model();
        $data=$model->execute('update qw_one_code set name="'.$_POST['name'].'",img="'.$_POST['img'].'",`key`="'.$_POST['key'].'" where id="'.$_POST['id'].'"');
        if($data){
            $this->ajaxReturn(array('code'=>true,'msg'=>'修改成功'));
        }else{
            $this->ajaxReturn(array('code'=>false,'msg'=>'网络繁忙，修改失败'));
        }
    }
    function del(){
        $model=new \Think\Model();
        $data=$model->query('SELECT * FROM qw_one_code WHERE id="'.$_POST['id'].'" limit 1');
        if($data[0]['type'] == '1'){
            $array=array('code'=>true, 'msg' =>'无法停用正在使用的活码');
        }else{
            if($_POST['key'] == '1'){
                $data=$model->execute('update qw_one_code set del="0" WHERE id="'.$_POST['id'].'" ');
                if($data){
                    $array=array('code'=>true, 'msg' =>'停用成功');
                }
            }else{
                $data=$model->execute('update qw_one_code set del="1" WHERE id="'.$_POST['id'].'" ');
                if($data){
                    $array=array('code'=>true, 'msg' =>'启用成功');
                }
            }
        }
        $this->ajaxReturn($array);
    }
}