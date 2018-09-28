<?php


namespace Home\Controller;
use Think\Controller;
class ComController extends Controller {

	public function _initialize(){
		C(setting());
    	$category=S('category');                     
	    if(empty($category)){                           
	        $table=M('category');   
	        $category=$table->select(); 
	       S('category',$category,60);         
	    }  
	    $this->assign('category',$category); 
    }
}