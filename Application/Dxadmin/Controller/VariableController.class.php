<?php

namespace Dxadmin\Controller;
use Dxadmin\Controller\ComController;

class VariableController extends ComController {
	
	public function index(){
		
		$vars = M('setting')->where('type=1')->select();
		$this->assign('vars',$vars);
		$this -> display();
	}

	public function add(){

		$this -> display();
	}

	public function edit($k=null){
		
		$var = M('setting')->where("k='$k'")->find();
		if(!$var){
			$this->error('参数错误！');
		}
		$this->assign('var',$var);
		$this -> display();
	}

	public function del(){
		
		$k = I('get.k');
		if($k<>''){
			if(M('setting')->where("type=1 and k='{$k}'")->delete()){
				addlog('删除自定义变量，ID：'.$k);
				$this->success('恭喜，删除成功！');
			}else{
				$this->error('参数错误！');
			}
		}else{
			$this->error('参数错误！');
		}
	}

	public function update(){

		$data['k'] = I('post.k');
		$varname = I('post.varname');
		if($data['k']==''){
			$this->error('变量名不能为空。');
		}
		if(M('setting')->where("k='{$data['k']}'")->count() && $varname==''){
			$this->error('变量名称已经存在。');
		}
		$data['v'] = I('post.v');
		$data['name'] = I('post.name');
		$data['type']  = 1;//自定义
		if($varname==''){
			M('setting')->data($data)->add();
			addlog('新增自定义变量：'.$data['k']);
		}else{
			M('setting')->data($data)->where("k='{$varname}'")->save();
			addlog('新增自定义变量：'.$data['k']);
		}
		
		$this->success('恭喜，操作成功！',U('index'));
	}
}