<?php declare(strict_types=1);
namespace Utilities;
use Utilities\Strings;
class Files
{
	public function upload_image(?array $file, bool $json_result = true): array
	{
		$result = ["error" => NULL, "url" => NULL];
		if(!empty($file['name'][0])){
			if($file['size'] > 5242880){
				$result = ["error" => "File size is too big", "url" => NULL];
			}else if(!exif_imagetype($file['tmp_name'])){
				$result = ["error" => "File is not an image", "url" => NULL];
			}else{
				$image_extension = image_type_to_extension(exif_imagetype($file['tmp_name']), true);
				$image_name = (new Strings())->random_string(128).$image_extension;
				move_uploaded_file($file['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/".$image_name);
				$imageTitleForJPG = 'zhabbler_'.(new Strings())->random_string(128).'.jpeg';
				$this->convertImage($_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/$image_name", $_SERVER['DOCUMENT_ROOT']."/Web/public/uploads/$imageTitleForJPG", 100);
				$result = ["error" => null, "url" => "/uploads/$imageTitleForJPG"];
			}
		}
		if($json_result){
			header('Content-Type: application/json');
			die(json_encode($result));
		}
		return $result;
	}

	public function convertImage(string $originalImage, string $outputImage, int $quality){
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