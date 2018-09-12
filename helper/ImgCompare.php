<?php

//根据返回的值0为相同 值越大图片差异越大
class ImgCompare {

    private static $_instance = null;

    public static  $rate = 1;

    public static function init() {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function doCompare($file) {
        if(!function_exists('imagecreatetruecolor')) {
            throw new \Exception('GD Library must be load if you want to use the class ImgCompare');
        }

        $is_string = false;

        if(is_string($file)) {
            $file = array($file);
            $is_string = true;
        }

        $result = array();
        foreach ($file as $f) {
            $result[] = $this->hash($f);
        }

        return $is_string ? $result[0] : $result;
    }

    public function checkIsSimilar($img_hash_1,$img_hash_2) {
        if (file_exists($img_hash_1) && file_exists($img_hash_2)) {
            $img_hash_1 = self::doCompare($img_hash_1);
            $img_hash_2 = self::doCompare($img_hash_2);
        }
        if(strlen($img_hash_1) !== strlen($img_hash_2)) {
            return "图片错误";
        }

        $count = 0;
        $len = strlen($img_hash_1);
        for ($i=0;$i<$len;$i++) {
            if($img_hash_1{$i} !== $img_hash_2{$i}) {
                // 计算 有多少位是不一样的
                $count ++;
            }
        }
        // 得到指纹以后，就可以对比不同的图片，看看64位中有多少位是不一样的。在理论上，这等同于计算"汉明距离"（Hamming distance）。
        // 如果不相同的数据位不超过5*误差，就说明两张图片很相似；如果大于10*误差，就说明这是两张不同的图片。
        return $count;
    }

    public function hash($file) {
        if (!file_exists($file)) {
            return "图片不存在";
        }

        $height = 8*self::$rate;
        $width = 8*self::$rate;

        $img = imagecreatetruecolor($width, $height);

        list($w,$h) = getimagesize($file);
        $source = self::createImg($file);

        // 重采样拷贝部分图像并调整大小
        // 将一幅图像中的一块正方形区域拷贝到另一个图像中，平滑地插入像素值，因此，尤其是，减小了图像的大小而仍然保持了极大的清晰度
        // 如果源和目标的宽度和高度不同，则会进行相应的图像收缩和拉伸。坐标指的是左上角
        // 本函数可用来在同一幅图内部拷贝（如果 dst_image 和 src_image 相同的话）区域，但如果区域交迭的话则结果不可预知。
        imagecopyresampled($img, $source, 0, 0, 0, 0, $width, $height, $w, $h);
        $value = self::getHashValue($img);
        imagedestroy($img);

        return $value;
    }

    public function getHashValue($img) {
        $width = imagesx($img);
        $height = imagesy($img);

        $total = 0;
        $array = array();

        // 将缩小后的图片，转为64级灰度。也就是说，所有像素点总共只有64种颜色。
        for ($y =0;$y<$height;$y++) {
            for ($x=0;$x<$width;$x++) {
                // 获取 指定的图形中指定坐标像素的颜色索引值
                // 将缩小的图像转为64级灰度
                $gray = ( imagecolorat($img, $x, $y) >> 8 ) & 0xFF;
                if (!is_array($array[$y])) {
                    $array[$y] = array();
                }

                $array[$y][$x] = $gray;
                $total += $gray;
            }
        }

        // 获取灰度平均值
        $average = intval($total/(64*self::$rate*self::$rate));
        $result = '';

        for ($y=0;$y<$height;$y++) {
            for ($x=0;$x<$width;$x++) {
                // 将每个像素的灰度，与平均值进行比较。大于或等于平均值，记为1；小于平均值，记为0
                if ($array[$y][$x] >= $average) {
                    $result .= '1';
                } else {
                    $result .= '0';
                }
            }
        }

        return $result;
    }

    public function createImg($file) {
        $ext = self::getFileExt($file);
        if ($ext === 'jpeg') $ext = 'jpg';
        $img = null;
        switch ($ext){
            case 'png' : $img = imagecreatefrompng($file);
                break;
            case 'jpg' : $img = imagecreatefromjpeg($file);
                break;
            case 'gif' : $img = imagecreatefromgif($file);
                break;
            default:
                break;
        }
        return $img;
    }

    public function getFileExt($file){
        $infos = explode('.', $file);
        $ext = strtolower($infos[count($infos) - 1]);

        if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif'))) {
            throw new \Exception("file extension must in 'jpg','jpeg','png','gif' ");
            exit;
        }

        return $ext;
    }
}