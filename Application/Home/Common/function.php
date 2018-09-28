<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2018/8/8
 * Time: 16:15
 */

function jssdk(){
    $model=new  \Think\Model();
    $config=$model->query('select * from qw_wxConfig where id=1 limit 1');
    vendor('Wxshare.class#jssdk');
    $jssdk = new  JSSDK($config[0]['AppId'], $config[0]['AppSecret']);
    $signPackage = $jssdk->GetSignPackage();
    return $signPackage;
}

function str($str){
	if(strpos($str,'省') !== false){
        $str=strstr($str,'省',true);
	}
    if(strpos($str,'自') !== false){
        $str=strstr($str,'自',true);
    }
	if(strpos($str,'特') !== false){
		$str=strstr($str,'特',true);
	}
	if(strpos($str,'维') !== false){
		$str=strstr($str,'维',true);
	}
    if(strpos($str,'回') !== false){
        $str=strstr($str,'回',true);
    }
    if(strpos($str,'壮') !== false){
        $str=strstr($str,'壮',true);
    }
	return $str;
}