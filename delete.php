<?php

date_default_timezone_set('Asia/Shanghai');
$date = $_POST['date'];

$path = "pics/warning/";
if ($date == 'all') {
    del_dir($path);
} elseif (date("Y-m-d", strtotime($date)) === $date) {
    del_dir($path.$date."/");
}

echo json_encode([
    'code' => 0,
    'msg' => "成功",
    'data' => [],
]);


//删除目录
function del_dir($path){
    //如果是目录则继续
    if(is_dir($path)){
        //扫描一个文件夹内的所有文件夹和文件并返回数组
        $p = scandir($path);
        foreach($p as $val){
            //排除目录中的.和..
            if($val !="." && $val !=".."){
                //如果是目录则递归子目录，继续操作
                if(is_dir($path.$val)){
                    //子目录中操作删除文件夹和文件
                    del_dir($path.$val.'/');
                    //目录清空后删除空文件夹
                    @rmdir($path.$val.'/');
                }else{
                    //如果是文件直接删除
                    unlink($path.$val);
                }
            }
        }
    }
}
