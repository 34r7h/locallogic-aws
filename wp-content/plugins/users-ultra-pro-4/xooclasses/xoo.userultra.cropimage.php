<?php
class XooCropImage{
 //code here
 	var $imgSrc,$myImage,$cropHeight,$cropWidth,$x,$y,$thumb; 
 
	
	function __construct( )
	{
	  
	}
	
	function setDimensions ($x, $y, $width, $height)
	{
	   $this->cropWidth   = $width; 
	   $this->cropHeight  = $height; 						 
						 
	//getting the top left coordinate
	   $this->x = $x;
	   $this->y = $y;
	
	
	
	}
	
	function setImage($image)
	{
	
	//Your Image
	   $this->imgSrc = $image; 
						 
	//getting the image dimensions
	   list($width, $height) = getimagesize($this->imgSrc); 
	   $extorig=substr ($this->imgSrc, -3); 
	   
	    $ext=strtolower($extorig);
		 switch($ext)
		  {
		   case 'png' : $this->myImage = imagecreatefrompng($this->imgSrc);
		   break;
		   case 'jpg' : $this->myImage = imagecreatefromjpeg($this->imgSrc);
		   break;
		   case 'jpeg' : $this->myImage = imagecreatefromjpeg($this->imgSrc);
		   break;
		   case 'gif' : $this->myImage = imagecreatefromgif($this->imgSrc);
		   break;
		  }
				  
						 
	//create image from the jpeg
	  // $this->myImage = imagecreatefromjpeg($this->imgSrc) or die("Error: Cannot find image!"); 
				
		   if($width > $height) $biggestSide = $width; //find biggest length
		   else $biggestSide = $height; 
						 
	//The crop size will be half that of the largest side 
	  // $cropPercent = .5; // This will zoom in to 50% zoom (crop)
	   
	   
	   //The crop size will be half that of the largest side 
//	   $cropPercent = .5; // This will zoom in to 50% zoom (crop)
//	   $this->cropWidth   = $biggestSide*$cropPercent; 
//	   $this->cropHeight  = $biggestSide*$cropPercent; 
////						 
////						 
////	//getting the top left coordinate
//	   $this->x = ($width-$this->cropWidth)/2;
//	   $this->y = ($height-$this->cropHeight)/2;


	  
				 
	} 
	
	function createThumb()
	{
						
	  $thumbSizeW = $this->cropWidth; // will create a 250 x 250 thumb
	  $thumbSizeH = $this->cropHeight; // will create a 250 x 250 thumb
	  $this->thumb = imagecreatetruecolor($thumbSizeW, $thumbSizeH); 
	
	  imagecopyresampled($this->thumb, $this->myImage, 0, 0,$this->x, $this->y, $thumbSizeW, $thumbSizeH, $this->cropWidth, $this->cropHeight); 
	  
	  
	  
	} 
	
	function renderImage($dest)
	{
		$info = pathinfo($dest);
        $ext = $info['extension'];
		$ext=strtolower($ext);
		
	   
	 switch($ext)
                  {
                   case 'png' : $img = imagepng($this->thumb ,$dest,9);
                   break;
                   case 'jpg' : $img = imagejpeg($this->thumb ,$dest,100);
                   break;
                   case 'jpeg' : $img = imagejpeg($this->thumb ,$dest,100);
                   break;
                   case 'gif' : $img = imagegif($this->thumb,"$newImage");
                   break;
                  }
				  
				  		 
	  //header('Content-type: image/jpeg');	  
	  //imagejpeg($this->thumb);
	  imagedestroy($this->thumb); 
	}
	
}  
$key = "imagecrop";
$this->{$key} = new XooCropImage();