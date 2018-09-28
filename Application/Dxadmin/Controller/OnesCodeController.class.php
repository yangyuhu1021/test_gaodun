<?php
namespace Dxadmin\Controller;

class OnesCodeController extends ComController{
    function index(){
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
            $code_group=$model->query('SELECT * FROM qw_group_code WHERE type="2" and  name LIKE "%'.$like.'%" limit '.$offset.','.$pageSize);
        }else{
            $code_group=$model->query('SELECT * FROM qw_group_code WHERE type="2" limit '.$offset.','.$pageSize);
        }
        $i=0;
        foreach ( $code_group as $key) {
            $data[$i]['name']=$key['name'];
            $data[$i]['id']=$key['id'];
            $one_code=$model->query('SELECT * FROM qw_ones_code WHERE group_id="'.$key['id'].'"');
            $data[$i]['ones']=count($one_code);
            $is_ok=0;
			foreach($one_code as $code){
                if($code['type'] > '0'){
                    $is_ok++;
                }
                $data[$i]['is_ok']=$is_ok;
                $data[$i]['is_number_s']   +=count($model->query('SELECT * FROM qw_user_code WHERE code_id="'.$code['id'].'" and type="2"'));
                $data[$i]['is_number_on']  +=count($model->query('SELECT * FROM qw_user_code WHERE code_id="'.$code['id'].'" and is_on="1" and type="2" '));
                $data[$i]['is_number_one'] +=count($model->query('SELECT * FROM qw_user_code WHERE code_id="'.$code['id'].'" and time >= "'.$timeON.'" and time < "'.$timeOFF.'"  and type="2"'));
                $data[$i]['is_number_ones']+=count($model->query('SELECT * FROM qw_user_code WHERE code_id="'.$code['id'].'" and time >= "'.$timeON.'" and time < "'.$timeOFF.'"  and is_on="1" and type="2" '));
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
        $count=count($data);
        if($count>$pageSize){
            for($i=0;$i<$pageSize;$i++){
                $res[$i]=$data[$i];
            }
        }else{
            for($i=0;$i<$count;$i++){
                $res[$i]=$data[$i];
            }
        }
        $page=new  \Think\Page($count,$pageSize);
        $page=$page->show();
        $url=$model->query('SELECT * FROM qw_wxCode_url WHERE id=1 limit 1');
        $this->assign('url',$url[0]);
        $this->assign('data',$res);
        $this->assign('page',$page);
        $this->display();
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
        $url='https://'.$url[0]['url'].'?id='.$data[0]['id'].'&type=2';
        $this->assign('data',$data[0]);
        $this->assign('url',$url);
        $this->display();
    }
    function url(){
        download(substr($_GET['url'],1));
    }
    //qun
    function ones(){
        $pageSize=25;
        $p = isset($_GET['p'])?intval($_GET['p']):'1';
        $offset = $pageSize*($p-1);
        $id=$_GET['id'];
        $model=new  \Think\Model();
        $res=$model->query('select * from qw_ones_code where group_id="'.$id.'" and del = "1" ');
        foreach ($res as $key){
            if(time() > $key['time']){
                $model->execute('update qw_ones_code set del="0" where id="'.$key['id'].'"');
            }
        }
        $res=$model->query('select * from qw_ones_code where group_id="'.$id.'" and type ="1" and del = "1" order by id desc limit 1');

        $user=$model->query('select * from qw_user_code where code_id="'.$res[0]['id'].'" and is_on = "1" and type="2" ');
        if(count($user) >= $res[0]['key']){
            $model->execute('update qw_ones_code set type="2" where id="'.$res[0]['id'].'"');
            $res=$model->query('select * from qw_ones_code where group_id="'.$id.'" and type ="0" and del = "1" limit 1');
            $model->execute('update qw_ones_code set type="1" where id="'.$res[0]['id'].'"');
        }
        if(IS_POST){
            $_POST['time']=strtotime($_POST['time']);
            if(M('ones_code')->add($_POST)){
                $this->ajaxReturn(array('code'=>true,'msg'=>'新增成功'));
            }else{
                $this->ajaxReturn(array('code'=>false,'msg'=>'网络繁忙，新增失败'));
            }
        }
        $timeON=strtotime(date('Y-m-d',time()));
        $timeOFF=strtotime(date('Y-m-d',time()))+(3600*24);
        if(@$_GET['like'] != ''){
            $like=$_GET['like'];
            $res=$model->query('select * from qw_ones_code where group_id="'.$id.'" and  name LIKE "%'.$like.'%"  limit '.$offset.','.$pageSize);
        }else{
            $res=$model->query('select * from qw_ones_code where group_id="'.$id.'" limit '.$offset.','.$pageSize);
        }
        $count_res=$model->query('select * from qw_ones_code where group_id="'.$id.'"');
        $count=count($count_res);
        $page=new  \Think\Page($count,$pageSize);
        $page=$page->show();
        for($i=0;$i<count($res);$i++){
            $data[$i]['key']=$res[$i]['key'];
            $data[$i]['id']=$res[$i]['id'];
            $data[$i]['name']=$res[$i]['name'];
            $data[$i]['type']=$res[$i]['type'];
            $data[$i]['del']=$res[$i]['del'];
            $user=$model->query('select * from qw_user_code where code_id="'.$res[$i]['id'].'" and is_on = "1" and type="2" ');
            $data[$i]['is_an']=count($user);
            $user=$model->query('select * from qw_user_code where code_id="'.$res[$i]['id'].'" and time >= "'.$timeON.'" and time < "'.$timeOFF.'"and type="2" ');
            $data[$i]['is_today_kan']=count($user);
            $user=$model->query('select * from qw_user_code where code_id="'.$res[$i]['id'].'" and time >= "'.$timeON.'" and time < "'.$timeOFF.'" and is_on = "1" and type="2" ');
            $data[$i]['is_today_an']=count($user);
        }
        for($i=0;$i<count($data);$i++){
            if($data[$i]['type'] == '1'){
                $data[$i]['type']='正在使用';
            }elseif ($data[$i]['type'] == '2'){
                $data[$i]['type']='以使用';
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
        $data=$model->query('SELECT * FROM qw_ones_code WHERE id="'.$_POST['id'].'" limit 1');
        $data[0]['time']=date('Y-m-d',$data[0]['time']);
        $this->ajaxReturn($data[0]);
    }
    function edits_code(){
        $time=strtotime($_POST['time']);
        $model=new \Think\Model();
        $data=$model->execute('update qw_ones_code set name="'.$_POST['name'].'",img="'.$_POST['img'].'",time="'.$time.'",`key`="'.$_POST['key'].'" where id="'.$_POST['id'].'"');
        if($data){
            $this->ajaxReturn(array('code'=>true,'msg'=>'修改成功'));
        }else{
            $this->ajaxReturn(array('code'=>false,'msg'=>'网络繁忙，修改失败'));
        }
    }
    function del(){
        $model=new \Think\Model();
        $data=$model->query('SELECT * FROM qw_ones_code WHERE id="'.$_POST['id'].'" limit 1');
        if($data[0]['type'] == '1'){
            $array=array('code'=>true, 'msg' =>'无法停用正在使用的活码');
        }else{
            if($_POST['key'] == '1'){
                $data=$model->execute('update qw_ones_code set del="0" WHERE id="'.$_POST['id'].'" ');
                if($data){
                    $array=array('code'=>true, 'msg' =>'停用成功');
                }
            }else{
                $data=$model->execute('update qw_ones_code set del="1" WHERE id="'.$_POST['id'].'" ');
                if($data){
                    $array=array('code'=>true, 'msg' =>'启用成功');
                }
            }
        }
        $this->ajaxReturn($array);
    }
}