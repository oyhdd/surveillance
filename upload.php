<?php

date_default_timezone_set('Asia/Shanghai');
require_once "helper/ImgCompare.php";

$temp_path = 'pics/temp/';//临时目录
$warning_path = 'pics/warning/'.date("Y-m-d")."/";//告警目录

//判断目录是否存在 不存在就创建
if (!is_dir($temp_path)){ //
    mkdir($temp_path, 0777, true);
}

$file_name = date("Y:i:s").'.png';//文件名
$last_file = $temp_path.'last_pic.png';//上次文件
$now_file = $temp_path.'now_pic.png';//当前文件

//将上传的图片保存至pics/temp
$image = $_POST['image'];
if (strstr($image,",")){
    $image = explode(',',$image);
    $image = $image[1];
}
file_put_contents($now_file, base64_decode($image));//返回的是字节数

//0为相同 值越大图片差异越大
$similar = 0;
//比较当前照片与上张照片的相似度
if (is_file($last_file)) {
    try {
        $instance = ImgCompare::init();
        $similar = $instance->checkIsSimilar($last_file, $now_file);
    } catch (\Exception $e) {
        echo json_encode([
            'code' => -1,
            'msg' => "系统错误",
            'data' => [],
        ]);
        die;
    }
}

//告警
$alarm = false;
//相似度超过2，保存图片至pics/warning
if ($similar > 1) {
    if (!is_dir($warning_path)){
        mkdir($warning_path, 0777, true);
    }
    $alarm = true;
    copy($now_file, $warning_path.date('H:i:s').'.png');
}

//将当前文件重命名为上次文件
rename($now_file, $last_file);

echo json_encode([
        'code' => 0,
        'msg' => "成功",
        'data' => ['alarm' => $alarm],
    ]);
