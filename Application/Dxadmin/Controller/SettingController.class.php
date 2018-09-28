<?php


namespace Dxadmin\Controller;
use Dxadmin\Controller\ComController;

class SettingController extends ComController {
    public function setting(){
		
		$vars = M('setting')->select();
		
		$da1=explode('/',$vars['0']['v']); 
		$da2=explode('/',$vars['1']['v']); 
		$this->assign('da1',$da1);
		$this->assign('da2',$da2);
		$this->assign('vars',$vars);
		$this -> display();
    }

    public function update(){

		//$data = $_POST;
		
		$data['sitename'] = $_POST['sitename'];
		$data['title'] = $_POST['title'];
		$data['keywords'] = $_POST['keywords'];
		//$data['description']= isset($_POST['description'])?strip_tags($_POST['description']):'';
		//$data['footer'] = isset($_POST['footer'])?$_POST['footer']:'';
		$data['description']=$_POST['money1'].'/'.$_POST['nums1'];
		$data['footer']=$_POST['money2'].'/'.$_POST['nums2'];
		$Model = M('setting');
		foreach($data as $k=>$v){
			$Model->data(array('v'=>$v))->where("k='{$k}'")->save();
		}
		addlog('修改网站配置。');
		$this->success('恭喜，网站配置成功！');
    }
}