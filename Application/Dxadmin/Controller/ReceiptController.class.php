<?php


namespace Dxadmin\Controller;
use Dxadmin\Controller\ComController;
class ReceiptController extends ComController {
    public function index(){
		

		$p = isset($_GET['p'])?intval($_GET['p']):'1';
		$field = isset($_GET['field'])?$_GET['field']:'';
		$keyword = isset($_GET['keyword'])?htmlentities($_GET['keyword']):'';
		$where = '';
		
		$prefix = C('DB_PREFIX');
		if($keyword <>''){
			if($field=='invoiceDataCode'){
				$where = "invoiceDataCode LIKE '%$keyword%'";
			}
			if($field=='invoiceNumber'){
				$where = "invoiceNumber LIKE '%$keyword%'";
			}
		}
		$user = M('receipt');
		$pagesize = 10;#每页数量
		$offset = $pagesize*($p-1);//计算记录偏移量
		$count = $user->where($where)->count();
		
		$list  = $user->field("id,invoiceDataCode,invoiceNumber,invoiceTypeName,invoiceTypeCode,billingTime,checkDate,purchaserName,taxpayerBankAccount,salesName,salesTaxpayerBankAccount,totalTaxSum,totalTaxNum,totalAmount,openid")->where($where)->limit($offset.','.$pagesize)->select();
		//$user->getLastSql();
		
		$page	=	new \Think\Page($count,$pagesize); 
		$page = $page->show();
        $this->assign('list',$list);	
        $this->assign('page',$page);
		$this -> display();
    }
	
	public function del(){
		
		$uids = isset($_REQUEST['uids'])?$_REQUEST['uids']:false;
		
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

		$map['id']  = array('in',$uids);
		if(M('receipt')->where($map)->delete()){
			$this->success('恭喜，发票删除成功！');
		}else{
			$this->error('参数错误！');
		}
	}
	
	
}