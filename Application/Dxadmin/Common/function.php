<?php

/**

 *

 * ��������־��¼

 * @param  string $log   ��־���ݡ�

 * @param  string $name ����ѡ���û�����

 *

 **/

function addlog($log,$name=false){

    $Model = M('log');

    if(!$name){

        $user = cookie('user');

        $data['name'] = $user['user'];

    }else{

        $data['name'] = $name;

    }

    $data['t'] = time();

    $data['ip'] = $_SERVER["REMOTE_ADDR"];

    $data['log'] = $log;

    $Model->data($data)->add();

}





/**

 *

 * ��������ȡ�û���Ϣ

 * @param  int $uid      �û�ID��

 * @param  string $name  �����У��磺'uid'��'uid,user'��

 *

 **/

function member($uid,$field=false) {

    $model = M('Member');

    if($field){

        return $model ->field($field)-> where(array('uid'=>$uid)) -> find();

    }else{

        return $model -> where(array('uid'=>$uid)) -> find();

    }

}

//生成二维码
function createQRcode($save_path,$qr_data='PHP QR Code :)',$qr_level='L',$qr_size=4,$save_prefix='qrcode'){
    if(!isset($save_path)) return '';
    //设置生成png图片的路径
    $PNG_TEMP_DIR = & $save_path;
    //导入二维码核心程序
    vendor('PHPQRcode.class#phpqrcode');  //注意这里的大小写哦，不然会出现找不到类，PHPQRcode是文件夹名字，class#phpqrcode就代表class.phpqrcode.php文件名
    //检测并创建生成文件夹
    if (!file_exists($PNG_TEMP_DIR)){
        mkdir($PNG_TEMP_DIR);
    }
    $filename = $PNG_TEMP_DIR.'test.png';
    $errorCorrectionLevel = 'L';
    if (isset($qr_level) && in_array($qr_level, array('L','M','Q','H'))){
        $errorCorrectionLevel = & $qr_level;
    }
    $matrixPointSize = 4;
    if (isset($qr_size)){
        $matrixPointSize = & min(max((int)$qr_size, 1), 10);
    }
    if (isset($qr_data)) {
        if (trim($qr_data) == ''){
            die('data cannot be empty!');
        }
        //生成文件名 文件路径+图片名字前缀+md5(名称)+.png
        $filename = $PNG_TEMP_DIR.$save_prefix.'.png';
        //开始生成
        QRcode::png($qr_data, $filename, $errorCorrectionLevel, $matrixPointSize, 2,true);
    } else {
        //默认生成
        QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2,true);
    }
    if(file_exists($PNG_TEMP_DIR.basename($filename)))
        return basename($filename);
    else
        return FALSE;
}

function download($file_url){
    $new_name='';
    if(!isset($file_url)||trim($file_url)==''){
        return '500';
    }
    if(!file_exists($file_url)){ //检查文件是否存在
        return '404';
    }
    $file_name=basename($file_url);
    $file_type=explode('.',$file_url);
    $file_type=$file_type[count($file_type)-1];
    $file_name=trim($new_name=='')?$file_name:urlencode($new_name).'.'.$file_type;
    $file_type=fopen($file_url,'r'); //打开文件
    //输入文件标签
    header("Content-type: application/octet-stream");
    header("Accept-Ranges: bytes");
    header("Accept-Length: ".filesize($file_url));
    header("Content-Disposition: attachment; filename=".$file_name);
    //输出文件内容
    echo fread($file_type,filesize($file_url));
    fclose($file_type);
}



