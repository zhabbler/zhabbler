<?php declare(strict_types=1);
namespace Utilities;
use DOMDocument;

class Strings
{
    public function is_empty(string $string): bool
    {
        return (!empty($string) && !ctype_space($string) && mb_strlen(trim($string)) > 0 ? false : true);
    }

    public function convert(string $string): string
    {
        return trim(stripslashes(htmlspecialchars($string)));
    }

    public function random_string(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function check_srcs_of_media(string $postID, string $content): string
    {
        $media_srcs = (new Strings())->get_imgs_video_src($content);
        $not_exists = [];
        foreach($media_srcs as $media_src){
            if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/Web/public'.parse_url($media_src)['path'])){
                $not_exists[] = $media_src;
            }
        }
        $html_content = strip_tags($content, ["p", "h1", "h2", "h3", "h4", "h5", "h6", "img", "video", "span", "a", "b", "i", "u", "br", "iframe", "audio"]);
        preg_replace('/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', "$1 $3", $html_content);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($html_content, 'HTML-ENTITIES', "UTF-8"));
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        foreach($dom->getElementsByTagName('img') as $img){
            if($img->hasAttributes()){
                $img->setAttribute('loading', 'lazy');
                foreach($img->attributes as $attr){
                    if($attr->nodeName == 'src'){
                        if(in_array($attr->nodeValue, $not_exists)){
                            $attr->nodeValue = '/static/images/image_corrupted.png';
                        }else if(str_starts_with($attr->nodeValue, BASE_URL) && $attr->nodeValue != '/static/images/image_corrupted.png'){
                            $binary_data = file_get_contents($attr->nodeValue);
                            $im = imagecreatefromstring($binary_data);
                            $img->setAttribute('width', "540");
                            $img->setAttribute('height', (string)(540/(imagesx($im)/imagesy($im))));
                            $gradient = ["linear-gradient(132deg, #3b1e89, #f793ff)", "linear-gradient(132deg, #1e8982, #93ffcd)", "linear-gradient(132deg, #891e1e, #ff9393)", "linear-gradient(132deg, #1e4b89, #93d2ff)"];
                            $img->setAttribute('style', "background: ".$gradient[rand(0,4)]);
                        }
                    }
                }
            }else{
                $img->parentNode->removeChild($img);
            }
        }
        foreach($dom->getElementsByTagName('iframe') as $iframe){
            if($iframe->hasAttributes()){
                foreach($iframe->attributes as $attr){
                    if($attr->nodeName == 'data-originalsrc'){
                        $original_source = $attr->nodeValue;
                        $whitelist_websites = ["www.youtube.com", "youtube.com", "soundcloud.com"];
                        if(in_array(parse_url($original_source)['host'], $whitelist_websites)){
                            $iframe->setAttribute("loading", "lazy");
                            if(parse_url($original_source)['host'] == 'soundcloud.com' || parse_url($original_source)['host'] == 'www.soundcloud.com'){
                                $iframe->setAttribute("src", "https://w.soundcloud.com/player/?url=".$original_source."&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true");
                            }else{
                                $iframe->setAttribute("src", preg_replace("/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i","https://www.youtube.com/embed/$1",$original_source));
                            }
                        }else{
                            $iframe->parentNode->removeChild($iframe);
                        }
                    }
                }
            }else{
                $iframe->parentNode->removeChild($iframe);
            }
        }
        foreach($dom->getElementsByTagName('video') as $video){
            if($video->hasAttributes()){
                foreach($video->attributes as $attr){
                    if($attr->nodeName == 'src' && in_array($attr->nodeValue, $not_exists)){
                        $attr->nodeValue = '/static/images/video_corrupted.mp4';
                    }
                }
            }else{
                $video->parentNode->removeChild($video);
            }
        }
        $result_html = (!empty($html_content) ? preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML($dom->documentElement)) : "");
        if(count($not_exists) > 0){
            $GLOBALS['db']->query("UPDATE zhabs SET zhabContent = ? WHERE zhabURLID = ?", $result_html, $postID);
        }
        return $result_html;
    }

    public function get_imgs_video_src(string $html_content): array
    {
        $html_content = strip_tags($html_content, ['img', 'video', 'audio']);
        preg_replace('/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', "$1 $3", $html_content);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($html_content, 'HTML-ENTITIES', "UTF-8"));
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $srcs = [];
        foreach($dom->getElementsByTagName('img') as $img){
            if($img->hasAttributes()){
                foreach($img->attributes as $attr){
                    if($attr->nodeName == 'src'){
                        array_push($srcs, $attr->nodeValue);
                    }
                }
            }
        }
        foreach($dom->getElementsByTagName('video') as $video){
            if($video->hasAttributes()){
                foreach($video->attributes as $attr){
                    if($attr->nodeName == 'src'){
                        array_push($srcs, $attr->nodeValue);
                    }
                }
            }
        }
        foreach($dom->getElementsByTagName('audio') as $audio){
            if($audio->hasAttributes()){
                foreach($audio->attributes as $attr){
                    if($attr->nodeName == 'src'){
                        array_push($srcs, $attr->nodeValue);
                    }
                }
            }
        }
        return $srcs;
    }

    public function get_closest_color(string $hex): int
    {
        $hex = str_replace('#', '', $hex);

        $c_r = hexdec(substr($hex, 0, 2));
        $c_g = hexdec(substr($hex, 2, 2));
        $c_b = hexdec(substr($hex, 4, 2));

        return intval((($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000);
    }

    public function get_img_src(string $html_content): string
    {
        $html_content = strip_tags($html_content, ['img', 'video']);
        preg_replace('/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', "$1 $3", $html_content);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($html_content, 'HTML-ENTITIES', "UTF-8"));
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $img = $dom->getElementsByTagName('img')[0];
        if($img && $img->hasAttributes()){
            foreach($img->attributes as $attr){
                if($attr->nodeName == 'src'){
                    return $attr->nodeValue;
                }
            }
        }else{
            return "";
        }
    }
    
    public function prepare_post_text(string $string): string
    {
        $string = strip_tags($string, ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'b', 'i', 'u', 'a', 'strong', 'span', 'img', 'video', 'iframe', 'audio']);
        preg_replace('/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', "$1 $3", $string);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8"));
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        if(count($dom->getElementsByTagName('img')) > 15 || count($dom->getElementsByTagName('video')) > 10 || count($dom->getElementsByTagName('iframe')) > 10 || count($dom->getElementsByTagName('audio')) > 10){
            die();
        }
        foreach($dom->getElementsByTagName('i') as $element){
            if($element->hasAttributes()){
                $element->remove();
            }
        }
        foreach(iterator_to_array($dom->getElementsByTagName('*')) as $element){
            $trimmed_value = trim($element->nodeValue);
            if(!in_array($element->nodeName, ['img', 'video', 'iframe', 'audio']) && $element->childNodes->length === 0 || $element->firstChild->nodeName === '#text' && $element->childNodes->length === 1 && empty($trimmed_value)){
                $element->parentNode->removeChild($element);
            }else if($element->nodeName == 'iframe'){
                if($element->hasAttributes()){
                    $original_source = '';
                    foreach($element->attributes as $attr){
                        $name = $attr->nodeName;
                        $value = $attr->nodeValue;
                        if($name == 'data-originalsrc'){
                            $original_source = $value;
                        }
                    }
                    while($element->attributes->length){
                        $element->removeAttribute($element->attributes->item(0)->name);
                    }
                    $whitelist_websites = ["www.soundcloud.com", "soundcloud.com", "www.youtube.com", "youtube.com"];
                    if(in_array(parse_url($original_source)['host'], $whitelist_websites)){
                        $element->setAttribute("data-originalsrc", $original_source);
                        $element->setAttribute("allowfullscreen", "true");
                    }else{
                        $element->parentNode->removeChild($element);
                    }
                }else{
                    $element->parentNode->removeChild($element);
                }
            }else if($element->nodeName == 'audio'){
                if($element->hasAttributes()){
                    $attrs = ["src" => "", "data-cover" => "", "data-name" => ""];
                    foreach($element->attributes as $attr){
                        $name = $attr->nodeName;
                        $value = $attr->nodeValue;
                        if(in_array($name, ["src", "data-cover", "data-name"])){
                            $attrs[$name] = $value;
                        }
                    }
                    while($element->attributes->length){
                        $element->removeAttribute($element->attributes->item(0)->name);
                    }
                    foreach($attrs as $attr => $value){
                        if($attr == 'data-cover' || $attr == 'src'){        
                            if(str_starts_with($value, "/uploads/") && file_exists($_SERVER['DOCUMENT_ROOT']."/Web/public$value")){
                                $element->setAttribute($attr, substr(BASE_URL, 0, -1).$value);
                            }else if(!str_starts_with($value, "/uploads/") && $attr == 'data-cover'){
                                $element->setAttribute($attr, "");
                            }else{
                                $element->parentNode->removeChild($element);
                            }
                        }else if($attr == 'data-name'){
                            if($this->is_empty($value) && mb_strlen($value) <= 72){
                                $element->parentNode->removeChild($element);
                            }else{
                                $value = $this->convert($value);
                                $element->setAttribute($attr, $value);
                            }
                        }
                    }
                }else{
                    $element->parentNode->removeChild($element);
                }
            }else{
                if($element->hasAttributes()){
                    foreach($element->attributes as $attr){
                        $name = $attr->nodeName;
                        $value = $attr->nodeValue;
                        if($name == 'data-ignore'){
                            $element->parentNode->removeChild($element);
                        }else{
                            if(!in_array($name, ["class", "href", "src", "style"])){
                                $element->removeAttribute($name);
                            }
                            if($name == "class" && !in_array($value, ["content_quote", "content_dialogue"])){
                                $element->removeAttribute($name);
                            }
                            if($name == "href" && str_contains($value, "javascript:")){
                                $element->removeAttribute($name);
                            }
                            if($name == "style"){
                                $value = preg_replace('/\s+/','',$value);
                                if($value != 'color:rgb(0,0,0);' && $value != 'color:rgb(255,66,66);' && $value != 'color:rgb(255,136,0);' && $value != 'color:rgb(255,247,0);' && $value != 'color:rgb(0,159,0);' && $value != 'color:rgb(0,157,255);' && $value != 'color:rgb(0,38,255);' && $value != 'color:rgb(153,0,255);'){
                                    $element->removeAttribute($name);
                                }
                            }
                            if($name == "src"){
                                $element->setAttribute('src', substr(BASE_URL, 0, -1).$value);
                            }
                        }
                    }
                }
                $element->removeAttribute("contenteditable");
            }
        }
        return (!empty($string) ? preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML($dom->documentElement)) : "");
    }
}
