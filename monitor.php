<?php
require_once "helper\ImgCompare.php";

$pic_name = $_FILES['webcam']['name'];
$tmp = $_FILES['webcam']['tmp_name'];

$temp_path = 'pics/temp';
$warning_path = 'pics/warning';

if (!file_exists($temp_path)) {
	mkdir($temp_path,0777,true);
}
if (!file_exists($warning_path)) {
	mkdir($warning_path,0777,true);
}

$last_file = $temp_path.'/last_'.$pic_name;//上张照片
$new_file = $warning_path.'/new_'.$pic_name;//当前照片

move_uploaded_file($tmp,$new_file);

$similar = 0;
//比较当前照片与上张照片的相似度
if (is_file($last_file)) {
    try {
        $instance = ImgCompare::init();
        $similar = $instance->checkIsSimilar($last_file, $new_file);//0为相同 值越大图片差异越大
    } catch (\Exception $e) {
        echo json_encode([
            'code' => -1,
            'msg' => "系统错误",
            'data' => [],
        ]);
        die;
    }
}

$alarm = false;
//相似度超过0，保存图片
if ($similar > 2) {
    $alarm = true;
    copy($new_file, 'pics/warning/'.date('YmdHis').$pic_name);
}
rename($new_file, $last_file);

echo json_encode([
        'code' => 0,
        'msg' => "成功",
        'data' => ['alarm' => $alarm],
    ]);
