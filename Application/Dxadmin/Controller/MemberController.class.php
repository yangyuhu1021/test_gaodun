<?php


namespace Dxadmin\Controller;
use Dxadmin\Controller\ComController;
class MemberController extends ComController {
    public function index(){
		

		$p = isset($_GET['p'])?intval($_GET['p']):'1';
		$field = isset($_GET['field'])?$_GET['field']:'';
		$keyword = isset($_GET['keyword'])?htmlentities($_GET['keyword']):'';
		$where = '';
		
		$prefix = C('DB_PREFIX');
		if($keyword <>''){
			if($field=='user'){
				$where = "user LIKE '%$keyword%'";
			}
			if($field=='phone'){
				$where = "{$prefix}member.phone LIKE '%$keyword%'";
			}
		}
		
		
		$user = M('member');
		$pagesize = 10;#每页数量
		$offset = $pagesize*($p-1);//计算记录偏移量
		$count = $user->where($where)->count();
		
		$list  = $user->where($where)->limit($offset.','.$pagesize)->select();
		//$user->getLastSql();
		$page	=	new \Think\Page($count,$pagesize); 
		$page = $page->show();
        $this->assign('list',$list);	
        $this->assign('page',$page);
		$this -> display();
    }
	
	public function del(){
		
		$uids = isset($_REQUEST['uids'])?$_REQUEST['uids']:false;
		//uid为1的禁止删除
		if($uids==1 or !$uids){
			$this->error('参数错误！');
		}
		if(is_array($uids)) 
		{
			foreach($uids as $k=>$v){
				if($v==1){//uid为1的禁止删除
					unset($uids[$k]);
				}
				$uids[$k] = intval($v);
			}
			if(!$uids){
				$this->error('参数错误！');
				$uids = implode(',',$uids);
			}
		}

		$map['uid']  = array('in',$uids);
		if(M('member')->where($map)->delete()){
			addlog('删除会员UID：'.$uids);
			$this->success('恭喜，用户删除成功！');
		}else{
			$this->error('参数错误！');
		}
	}
	
	public function edit(){
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):false;
		if($uid){	
			//$member = M('member')->where("uid='$uid'")->find();
			$prefix = C('DB_PREFIX');
			$user = M('member');
			$member  = $user->where("uid=".$uid)->find();

		}else{
			$this->error('参数错误！');
		}
		
	
		$this->assign('member',$member);
		$this -> display();
	}
	
	public function update($ajax=''){
		
		$uid = isset($_POST['uid'])?intval($_POST['uid']):false;
		
		
		
		$head = I('post.head','','strip_tags');
		if($head<>'') {
			$data['head'] = $head;
		}
		
		$data['phone'] = $_POST['phone'];
		$data['openid'] = $_POST['openid'];
		$user = $_POST['user'];
		$data['t'] = time();
		if(!$uid){
			if($user==''){
				$this->error('用户名称不能为空！');
			}
			
			if(M('member')->where("user='".$user."' ")->count()){
				$this->error('用户名已被占用！');
			}
			if(M('member')->where("openid='".$data['openid']."' ")->count()){
				$this->error('微信ID已被占用！');
			}
			$data['user'] = $user;
			$data['nums'] = M('setting')->where("k='keywords'")->getfield('v');
			$uid = M('member')->data($data)->add();
			
			addlog('新增会员，会员UID：'.$uid);
		}else{
			M('member')->data($data)->where("uid=$uid")->save();
			addlog('编辑会员信息，会员UID：'.$uid);
		}
		$this->success('操作成功！');
	}
	
	
	public function add(){

		//$usergroup = M('auth_group')->field('id,title')->select();
		//$this->assign('usergroup',$usergroup);
		$this -> display();
	}
}