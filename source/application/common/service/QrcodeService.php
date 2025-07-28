<?php 
namespace app\common\service;

class QrcodeService {
    
    public $engine = 'phpQrcode'; 
    
    public $outPut = 'uploads/qrcode/'; // 输出文件夹
    
    public function create($text){
        // dump($text);die;
        if ($this->engine =='phpQrcode'){
            $rout1 = $this->createQrcodeByQr($text);
            $rout2 = $this->makeImgWithStr($text,30);
            $this->CompositeImage([$rout1,$rout2],$rout1);
            return $rout1;
        }
        if ($this->engine=='barcode'){
             //条形码
            $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorJPG(); #创建SVG类型条形码
            $barcode = $generatorSVG->getBarcode($text, $generatorSVG::TYPE_CODE_128,$widthFactor =3, $totalHeight = 100);
            // 从数据创建图像资源
            $rout1 = imagecreatefromstring($barcode);

            $rout2 = $this->makeImgWithStr($text,30);
            $this->CompositeImage([$rout1,$rout2],$rout1);
            return $rout1;
        }
    }
    
    public function createBarcode($text,$type){
        // dump($text);die;
        if ($type =='10'){
            $rout1 = $this->createQrcodeByQr($text);
            $rout2 = $this->makeImgWithStr($text,20);
            $this->CompositeImage([$rout1,$rout2],$rout1);
            return $rout1;
        }
        if ($type=='20'){
             //条形码
            $width = 400;
            $height = 200;
            $image = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);
            $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorJPG(); #创建SVG类型条形码
            $barcode = $generatorSVG->getBarcode($text, $generatorSVG::TYPE_CODE_128,$widthFactor =2, $totalHeight = 80);
            // 从数据创建图像资源
            $mergeImageResource = imagecreatefromstring($barcode);
            // 获取第二张图片的宽度和高度
            $width3 = imagesx($mergeImageResource);
            $height3 = imagesy($mergeImageResource);
            imagecopymerge($image,$mergeImageResource,($width - $width3) /2,30, 0, 0,$width3,$height3,100);
            
            $fontPath = 'assets/common/fonts/verdanab.ttf';
            $fontColor = imagecolorallocate($image, 0, 0, 0);
            $fontSize = 40;
            $descSize = 20;
            $addcSize = 16;
            //绘制单号到图片上
            $expresstextBox = imagettfbbox($fontSize, 0, $fontPath,$text);
            $expresstextWidth = $expresstextBox[2] - $expresstextBox[0];
            $expresstextHeight = $expresstextBox[7] - $expresstextBox[1];
            imagettftext($image, $addcSize,0,200-($width - $expresstextWidth)/2,140, $fontColor, $fontPath,$text);
            
            $outfile = $this->outPut.date("YmdHis").rand(000000,999999).'.jpg';
    		imagejpeg($image, $outfile);
            imagedestroy($image);
            return $outfile;
        }
    }
    
    public function createprint($storeConfig,$package,$url){
        // 创建一个宽度为800px、高度为800px的白色背景图像
        // dump($package);die;
        $width = 800;
        $height = 800;
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
         
        // 设置字体文件的路径（确保你的服务器上有这个字体文件）
        $fontPath = 'assets/common/fonts/verdanab.ttf';
        
        // 设置字体颜色和大小
        $fontColor = imagecolorallocate($image, 0, 0, 0);
        $fontSize = 40;
        $descSize = 20;
        $epxressSize = 20;
        $addcSize = 16;
        // 计算文本的尺寸
        $textBox = imagettfbbox($fontSize, 0, $fontPath, $storeConfig['title']);
       
        $textWidth = $textBox[2] - $textBox[0];
        $textHeight = $textBox[7] - $textBox[1];

        // 绘制文本到图像
        imagettftext($image, $fontSize, 0, ($width - $textWidth) / 2, 80, $fontColor, $fontPath, $storeConfig['title']);
        
        //计算描述内容
        $descBox = imagettfbbox($descSize, 0, $fontPath, $storeConfig['desc']);
        $descWidth = $descBox[2] - $descBox[0];
        $descHeight = $descBox[7] - $descBox[1];
        
        
        imagettftext($image, $descSize, 0, ($width - $descWidth) / 2, 130, $fontColor, $fontPath, $storeConfig['desc']);
        
        //融合二维码
        $erweima = $this->createQrcodeByQrPrint($url);

        // 创建源图像资源
        $sourceImage = imagecreatefrompng($erweima);
     
        // 获取第二张图片的宽度和高度
        $width2 = imagesx($sourceImage);
        $height2 = imagesy($sourceImage);
        // dump($erweima);die;
        imagecopymerge($image,$sourceImage,550,400, 0, 0,$width2,$height2,100);
        
        //条形码
        $generatorSVG = new \Picqer\Barcode\BarcodeGeneratorJPG(); #创建SVG类型条形码
        $barcode = $generatorSVG->getBarcode($package['express_num'], $generatorSVG::TYPE_CODE_128,$widthFactor =3, $totalHeight = 100);
        // 从数据创建图像资源
        $mergeImageResource = imagecreatefromstring($barcode);
    
        // 获取第二张图片的宽度和高度
        $width3 = imagesx($mergeImageResource);
        $height3 = imagesy($mergeImageResource);
        imagecopymerge($image,$mergeImageResource,($width - $width3) /2,200, 0, 0,$width3,$height3,100);
        //绘制单号到图片上
        $expresstextBox = imagettfbbox($epxressSize, 0, $fontPath,$package['express_num']);
        $expresstextWidth = $expresstextBox[2] - $expresstextBox[0];
        $expresstextHeight = $expresstextBox[7] - $expresstextBox[1];
        // 绘制文本到图像
        imagettftext($image, $epxressSize, 0, ($width - $expresstextWidth) / 2, 350, $fontColor, $fontPath,$package['express_num']);
        // 绘制线条
        $black = imagecolorallocate($image, 0, 0, 0);
        imageline($image,0, 380, 800, 380, $black);
        imageline($image,0, 381, 800, 381, $black);
        imageline($image,0, 382, 800, 382, $black);
        
        $text1 = "Код клиента:".$package['member_id'];
        $text3 = "Вес:".$package['weight'];
        $text8 = $package['entering_warehouse_time'];
        if(isset($package['address'])){
            $text2 = "Страна:".$package['address']['country'];
            $text4 = "Адрес клиента:".$package['address']['province'];
            $text5 = $package['address']['city'];
            $text6 = $package['address']['region'];
            $text7 = $package['address']['detail'];
            imagettftext($image, $addcSize, 0, 30, 500, $fontColor, $fontPath,$text2);
            imagettftext($image, $addcSize, 0, 30, 540, $fontColor, $fontPath,$text4);
            imagettftext($image, $addcSize, 0, 100, 580, $fontColor, $fontPath,$text5);
            imagettftext($image, $addcSize, 0, 100, 620, $fontColor, $fontPath,$text6);
            imagettftext($image, $addcSize, 0, 100, 660, $fontColor, $fontPath,$text7);
        }
        // dump($text2);die;
         // 绘制文本到图像
        imagettftext($image, $addcSize, 0, 30, 420, $fontColor, $fontPath,$text1);
        imagettftext($image, $addcSize, 0, 30, 460, $fontColor, $fontPath,$text3);
        imagettftext($image, $addcSize, 0, 30, 740, $fontColor, $fontPath,$text8);
        
        $outfile = $this->outPut.date("YmdHis").rand(000000,999999).'.jpg';
 
		imagejpeg($image, $outfile);
        // 释放内存
        imagepng($image);
        imagedestroy($image);
        return $outfile;
    }
    
    public function createQrcodeByQrPrint($text){
        // dump($text);die;
        require_once APP_PATH.'/common/library/phpqrcode/phpqrcode.php';
        $data = $text;//内容
        $level = 'L';// 纠错级别：L、M、Q、H
        $size = 8;//元素尺寸
        $margin = 1;//边距
        $outfile = $this->outPut.date("YmdHis").rand(000000,999999).'.png';

        $saveandprint = false;// true直接输出屏幕  false 保存到文件中
        $back_color = 0xFFFFFF;//白色底色
        $fore_color = 0x000000;//黑色二维码色 若传参数要hexdec处理，如 $fore_color = str_replace('#','0x',$fore_color); $fore_color = hexdec('0xCCCCCC');
        
        $QRcode = new \QRcode();
        
        //生成png图片
        $QRcode->png($data, $outfile, $level, $size, $margin, false, $back_color, $fore_color);
        return $outfile;
    }
    
    public function createQrcodeByQr($text){
        // dump($text);die;
        require_once APP_PATH.'/common/library/phpqrcode/phpqrcode.php';
        $data = $text;//内容
        $level = 'L';// 纠错级别：L、M、Q、H
        $size = 10;//元素尺寸
        $margin = 1;//边距
        $outfile = $this->outPut.date("YmdHis").rand(000000,999999).'.png';

        $saveandprint = false;// true直接输出屏幕  false 保存到文件中
        $back_color = 0xFFFFFF;//白色底色
        $fore_color = 0x000000;//黑色二维码色 若传参数要hexdec处理，如 $fore_color = str_replace('#','0x',$fore_color); $fore_color = hexdec('0xCCCCCC');
        
        $QRcode = new \QRcode();
        
        //生成png图片
        $QRcode->png($data, $outfile, $level, $size, $margin, false, $back_color, $fore_color);
//         $product_sn1 = substr($text,0,2);
//         $product_sn = $product_sn1.'-'.$product_sn2;
        return $outfile;
    }
    
    //文字生成图片
	public function makeImgWithStr($text, $font_size=20,$font = 'assets/common/fonts/verdanab.ttf')
	{
	   // dump($text);
		//图片尺寸
		$im = imagecreatetruecolor(444, 70);
		//背景色
		$white = imagecolorallocate($im, 255, 255, 255);
		//字体颜色
		$black = imagecolorallocate($im, 0, 0, 0);
        // $product_sn1 = substr($text,0,2);
        // $product_sn2 = substr($text,2,2);
        $product_sn = $text;
		imagefilledrectangle($im, 0, 0, 444, 300, $white);
		$txt_max_width = intval(0.8 * 444);
		$content = "";
		for ($i = 0; $i < mb_strlen($product_sn); $i++) {
			$letter[] = mb_substr($product_sn, $i, 1);
		}
		foreach ($letter as $l) {
			$test_str = $content . " " . $l;
			$test_box = imagettfbbox($font_size, 0, $font, $test_str);
			// 判断拼接后的字符串是否超过预设的宽度。超出宽度添加换行
			if (($test_box[2] > $txt_max_width) && ($content !== "")) {
				$content .= "\n";
			}
			$content .= $l;
		}

		$txt_width = $test_box[2] - $test_box[0];

		$y = 70 * 0.6; // 文字从何处的高度开始
		$x = (230 - $txt_width) / 4; //文字居中
		// echo $x;die;
		//文字写入
		imagettftext($im, $font_size, 0, $x, $y, $black, $font, $content); //写 TTF 文字到图中
		//图片保存
		$outfile = $this->outPut.date("YmdHis").rand(000000,999999).'.jpg';
		imagejpeg($im, $outfile);
		return $outfile;
	}
	
	/**
	 * 合并图片,拼接合并
	 * @param array $image_path 需要合成的图片数组
	 * @param $save_path 合成后图片保存路径
	 * @param string $axis 合成方向
	 * @param string $save_type 合成后图片保存类型
	 * @return bool|array
	 */
	public function CompositeImage(array $image_path, $save_path, $axis = 'y', $save_type = 'png')
	{
		if (count($image_path) < 2) {
			return false;
		}
		//定义一个图片对象数组
		$image_obj = [];
		//获取图片信息
		$width = 0;
		$height = 0;
// 		dump($image_path);die;
		foreach ($image_path as $k => $v) {
			$pic_info = getimagesize($v);
			list($mime, $type) = explode('/', $pic_info['mime']);
			//获取宽高度
			$width += $pic_info[0];
			$height += $pic_info[1];
			if ($type == 'jpeg') {
				$image_obj[] = imagecreatefromjpeg($v);
			} elseif ($type == 'png') {
				$image_obj[] = imagecreatefrompng($v);
			} else {
				$image_obj[] = imagecreatefromgif($v);
			}
		}
		//按轴生成画布方向
		if ($axis == 'x') {
			//TODO X轴无缝合成时请保证所有图片高度相同
			$img = imageCreatetruecolor($width, imagesy($image_obj[0]));
		} else {
			//TODO Y轴无缝合成时请保证所有图片宽度相同
			$img = imageCreatetruecolor(imagesx($image_obj[0]), $height);
		}
		//创建画布颜色
		$color = imagecolorallocate($img, 255, 255, 255);
		imagefill($image_obj[0], 0, 0, $color);
		//创建画布
		imageColorTransparent($img, $color);
		imagecopyresampled($img, $image_obj[0], 0, 0, 0, 0, imagesx($image_obj[0]), imagesy($image_obj[0]), imagesx($image_obj[0]), imagesy($image_obj[0]));
		$yx = imagesx($image_obj[0]);
		$x = 0;
		$yy = imagesy($image_obj[0]);
		$y = 0;
		//循环生成图片
		for ($i = 1; $i <= count($image_obj) - 1; $i++) {
			if ($axis == 'x') {
				$x = $x + $yx;
				imagecopymerge($img, $image_obj[$i], $x, 0, 0, 0, imagesx($image_obj[$i]), imagesy($image_obj[$i]), 100);
			} else {
				$y = $y + $yy;
				imagecopymerge($img, $image_obj[$i], 0, $y, 0, 0, imagesx($image_obj[$i]), imagesy($image_obj[$i]), 100);
			}
		}
		//设置合成后图片保存类型
		if ($save_type == 'png') {
			imagepng($img, $save_path);
		} elseif ($save_type == 'jpg' || $save_type == 'jpeg') {
			imagejpeg($img, $save_path);
		} else {
			imagegif($img, $save_path);
		}
		return true;


	}

    
}