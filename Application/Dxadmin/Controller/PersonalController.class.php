<?php


namespace Dxadmin\Controller;
use Dxadmin\Controller\ComController;

class PersonalController extends ComController {

	public function profile(){
		
		$member = M('admin_user')->where('uid='.$this->USER['uid'])->find();
		$this->assign('nav',array('Personal','profile',''));//导航
		$this->assign('member',$member);
		$this -> display();
	}
	
	public function update(){
		
		$uid = $this->USER['uid'];
		$password = isset($_POST['password'])?trim($_POST['password']):false;
		if($password) {
			$data['password'] = password($password);
		}

		$head = I('post.head','','strip_tags');
		if($head<>'') {
			$data['head'] = $head;
		}

		$data['sex'] = isset($_POST['sex'])?intval($_POST['sex']):0;
		$data['birthday'] = isset($_POST['birthday'])?strtotime($_POST['birthday']):0;
		$data['phone'] = isset($_POST['phone'])?trim($_POST['phone']):'';
		$data['qq'] = isset($_POST['qq'])?trim($_POST['qq']):'';
		$data['email'] = isset($_POST['email'])?trim($_POST['email']):'';
		$isadmin = isset($_POST['isadmin'])?$_POST['isadmin']:'';
		if($uid <> 1) {#禁止最高管理员设为普通会员。
			$data['isadmin'] = $isadmin=='on'?1:0;
		}
		$Model = M('admin_user');
		$Model->data($data)->where("uid=$uid")->save();
		addlog('修改个人资料');
		$this->success('操作成功！');

		
	}
}