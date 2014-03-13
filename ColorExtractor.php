<?php
include_once("Color.php");

abstract class ColorExtractor{
	private $image;
	private $imageSize;
		
	public static function createFromURL($class,$imageURL){
		$image = imagecreatefromjpeg($imageURL);
		$size = getimagesize($imageURL);
		return new $class($image, $size);
	}
	public function __construct($image,$imageSize){
		$this->image = $image;
		$this->imageSize = $imageSize;
	}

	protected function getImageData(){
		$numberOfPixels = $this->imageSize;
		$factorX = 1;
		$factorY = 1;
		$result = array();
		for($x=0;$x<$this->imageSize[0];$x+=$factorX){
			for($y=0;$y<$this->imageSize[1];$y+=$factorY){
				$colorAtReadable = imagecolorsforindex($this->image,imagecolorat($this->image,$x,$y));
				if ($colorAtReadable['alpha']<223){
					$key = $colorAtReadable['red'].",".$colorAtReadable['green'].",".$colorAtReadable['blue'];
					if (!array_key_exists($key,$result)){
						unset($colorAtReadable['alpha']);
						$rgb = $colorAtReadable;
						$rgb['count']=1;
						$rgb['weight']=$this->calculateColorWeight(Color::createFromArray($colorAtReadable));
							
						if ($rgb['weight']<0){
							$rgb['weight']=0;
						}
						$result[$key]=$rgb;
					}else{
						$result[$key]['count']++;
					}
				}
			}
		}
		return $result;
	}

	protected static function getMostProminentRGBImpl($data,$degrade,$rgbMatch){
		$count = 0;
		$result = array();
		foreach($data as $pixelKey=>$pixel){
			$totalWeight = $pixel['weight']*$pixel['count'];
			$count++;
			if (self::doesRgbMatch($rgbMatch,$pixel)){
				$pixelGroupKey = ($pixel['red']>>$degrade).",".($pixel['green']>>$degrade).",".($pixel['blue']>>$degrade);
				if (array_key_exists($pixelGroupKey, $result)){
					$result[$pixelGroupKey]+=$totalWeight;
				}else {
					$result[$pixelGroupKey]=$totalWeight;
				}
			}
		}
		$maxKey = array_search(max($result),$result);
		$colors = explode(',',$maxKey);
		return new Color($colors[0],$colors[1],$colors[2]);
	}
	protected static function doesRgbMatch($rgb1,$rgb2){
		if (empty($rgb1)||empty($rgb2)){
			return true;
		}
	
		$red = $rgb2['red'] >> $rgb1['degrade'];
		$green = $rgb2['green'] >> $rgb1['degrade'];
		$blue = $rgb2['blue'] >> $rgb1['degrade'];
	
		return $rgb1->getRed()==$red && $rgb1->getGreen()==$green && $rgb1->getBlue()==$blue;
	}

	public function extract(){
		$data = $this->getImageData();
		$rgb = self::getMostProminentRGBImpl($data,6,null);
		$rgb = self::getMostProminentRGBImpl($data,4,null);
		$rgb = self::getMostProminentRGBImpl($data,2,null);
		$rgb = self::getMostProminentRGBImpl($data,0,null);
		return $rgb;
	}
	public abstract function calculateColorWeight($color);
}
class HueColorExtractor extends ColorExtractor{
	public function calculateColorWeight($color){
		return (abs($color->getRed()-$color->getGreen())*abs($color->getRed()-$color->getGreen()) + abs($color->getRed()-$color->getBlue())*abs($color->getRed()-$color->getBlue()) + abs($color->getGreen()-$color->getBlue())*abs($color->getGreen()-$color->getBlue()))/65535*50+1;
	}
}
class BrightColorExtractor extends ColorExtractor{
	public function calculateColorWeight($color){
		return ($color->getRed()*$color->getRed()+$color->getGreen()*$color->getGreen()+$color->getBlue()*$color->getBlue())/65535*20+1;
	}
}
class BrightExcludeWhiteColorExtractor extends ColorExtractor{
	public function calculateColorWeight($color){
		if ($color->getRed()>245 && $color->getGreen()>245 && $color->getBlue()>245) return 0;
		return ($color->getRed()*$color->getRed()+$color->getGreen()*$color->getGreen()+$color->getBlue()*$color->getBlue())/65535*20+1;
	}
}
class DarkColorExtractor extends ColorExtractor{
	public function calculateColorWeight($color){
		return 768-$color->getRed()-$color->getGreen()-$color->getBlue()+1;
	}
}
class NonWhiteColorExtractor extends ColorExtractor{
	public function calculateColorWeight($color){
		return ($color->getRed()>245 && $color->getGreen()>245 && $color->getBlue()>245) ? 0 : 1;
	}
}
class NonBlackColorExtractor extends ColorExtractor{
	public function calculateColorWeight($color){
		return ($color->getRed()<10 && $color->getGreen()<10 && $color->getBlue()<10) ? 0 : 1;
	}
}