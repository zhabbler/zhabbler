<?php declare(strict_types=1);
namespace Utilities;
use Utilities\Strings;
class Files
{
	public function upload_image(?array $file, bool $json_result = true): array
	{
		$result = ["error" => NULL, "url" => NULL];
		if(!empty($file['name'][0])){
			if($file['size'] > 1073741824){
				$result = ["error" => "File size is too big", "url" => NULL];
			}else if(!exif_imagetype($file['tmp_name'])){
				$result = ["error" => "File is not an image", "url" => NULL];
			}else{
				$image_extension = image_type_to_extension(exif_imagetype($file['tmp_name']), true);
				$image_name = (new Strings())->random_string(128).$image_extension;
				move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/".$image_name);
				$imageTitleForJPG = 'zhabbler_'.(new Strings())->random_string(128).'.jpeg';
				$this->convertImage($_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/$image_name", $_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/$imageTitleForJPG", 50);
				$result = ["error" => null, "url" => "/uploads/$imageTitleForJPG"];
			}
		}
		if($json_result){
			header('Content-Type: application/json');
			die(json_encode($result));
		}
		return $result;
	}

	public function thumbnail_avatar_crop(string $path, string $output): void
	{
		list($width, $height) = getimagesize($path);
		$myImage = imagecreatefromjpeg($path);

		if($width > $height){
		  $y = 0;
		  $x = ($width - $height) / 2;
		  $smallestSide = $height;
		}else{
		  $x = 0;
		  $y = ($height - $width) / 2;
		  $smallestSide = $width;
		}

		$thumbSize = 250;
		$thumb = imagecreatetruecolor($thumbSize, $thumbSize);
		imagecopyresampled($thumb, $myImage, 0, 0, (int)$x, (int)$y, $thumbSize, $thumbSize, $smallestSide, $smallestSide);

		imagejpeg($thumb, $output);
		unlink($path);
	}

	public function upload_video(?array $file, bool $json_result = true): array
	{
		$result = ["error" => NULL, "url" => NULL];
		if(!empty($file['name'][0])){
			$allowed_extensions = ["mp4", "flv", "webm", "mkv", "vob", "ogv", "ogg", "avi", "wmv", "mov", "mpeg", "mpg", "flv", "3gp"];
			$ext = explode('.', $file['name']);
			$ext = strtolower(end($ext));
			if($file['size'] > 3793747637236){
				$result = ["error" => "File size is too big", "url" => NULL];
			}else if(!in_array($ext, $allowed_extensions)){
				$result = ["error" => "File is not a video", "url" => NULL];
			}else{
				$video_name = (new Strings())->random_string(128).'.'.$ext;
				move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/".$video_name);
				$videoTitleForMP4 = 'zhabbler_'.(new Strings())->random_string(128).'.mp4';
				$this->convertVideo($_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/$video_name", $_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/$videoTitleForMP4", 100);
				$result = ["error" => null, "url" => "/uploads/$videoTitleForMP4"];
			}
		}
		if($json_result){
			header('Content-Type: application/json');
			die(json_encode($result));
		}
		return $result;
	}

	public function convertVideo($tempFilePath, $finalFilePath): string
	{
        $cmd = "ffmpeg -i $tempFilePath $finalFilePath 2>&1";
        $outputLog = array();
        exec($cmd, $outputLog, $returnCode);
        unlink($tempFilePath);
        return ($returnCode != 0 ? "error" : "");
    }

	public function convertImage(string $originalImage, string $outputImage, int $quality): void 
	{
	    $exploded = explode('.',$originalImage);
	    $ext = $exploded[count($exploded) - 1]; 

	    if(preg_match('/jpg|jpeg/i',$ext)){
			$imageTmp = @imagecreatefromjpeg($originalImage);
		}else if(preg_match('/png/i',$ext)){
			$imageTmp = @imagecreatefrompng($originalImage);
		}else if(preg_match('/gif/i',$ext)){
			$imageTmp = @imagecreatefromgif($originalImage);
		}else if(preg_match('/bmp/i',$ext)){
			$imageTmp = @imagecreatefrombmp($originalImage);
		}else{
			die;
		}

	    imagejpeg($imageTmp, $outputImage, $quality);
	    imagedestroy($imageTmp);

		unlink($originalImage);
	}
}
