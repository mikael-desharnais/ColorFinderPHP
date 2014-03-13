<?php 

class Color{
	private $red;
	private $green;
	private $blue;
	public function __construct($red,$green,$blue){
		$this->red = $red;
		$this->green = $green;
		$this->blue = $blue;
	}
	public static function createFromArray($colorArray){
		return new Color($colorArray['red'],$colorArray['green'],$colorArray['blue']);
	}
	public function getHTMLCode(){
		$hex = "#";
		$hex .= str_pad(dechex($this->getRed()), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($this->getGreen()), 2, "0", STR_PAD_LEFT);
		$hex .= str_pad(dechex($this->getBlue()), 2, "0", STR_PAD_LEFT);

		return $hex;
	}
	public function getRed(){
		return $this->red;
	}
	public function getGreen(){
		return $this->green;
	}
	public function getBlue(){
		return $this->blue;
	}
}