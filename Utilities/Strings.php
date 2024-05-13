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

    public function prepare_post_text(string $string): string
    {
        $prepared = "";
        $string = strip_tags($string, ['br', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'b', 'i', 'u', 'a', 'strong', 'span', 'img']);
        preg_replace('/(<.+?)(?<=\s)on[a-z]+\s*=\s*(?:([\'"])(?!\2).+?\2|(?:\S+?\(.*?\)(?=[\s>])))(.*?>)/i', "$1 $3", $string);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(mb_convert_encoding($string, 'HTML-ENTITIES', "UTF-8"));
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        foreach($dom->getElementsByTagName('i') as $element){
            if($element->hasAttributes()){
                $element->remove();
            }
        }
        foreach($dom->getElementsByTagName('*') as $element){
            $attributes = '';
            if(!in_array($element->nodeName, ['body', 'html', 'img', 'br'])){
                if($element->hasAttributes()){
                    foreach($element->attributes as $attr){
                        if($attr->nodeName == "class" && $element->nodeName == 'p' && ($attr->nodeValue == "content_quote" || $attr->nodeValue == "content_dialogue")){
                            $attributes .= "{$attr->nodeName}='{$attr->nodeValue}'";
                        }
                        if($attr->nodeName == "href" && !str_contains($attr->nodeValue, "javascript:")){
                            $attributes .= "{$attr->nodeName}='{$attr->nodeValue}'";
                        }
                        if($attr->nodeName == "style"){
                            if(in_array($attr->nodeName, ['color:rgb(0,0,0);', 'color:rgb(255,66,66);', 'color:rgb(255,136,0);', 'color:rgb(255,247,0);', 'color:rgb(0,159,0);', 'color:rgb(0,157,255);', 'color:rgb(0,38,255);', 'color:rgb(153,0,255);'])){
                                $attributes .= "{$attr->nodeName}='".preg_replace('/\s+/','',$attr->nodeValue)."'";
                            }
                        }
                    }
                }
                if(!$this->is_empty($this->convert($element->nodeValue))){
                    $prepared .= "<{$element->nodeName} {$attributes}>{$this->convert($element->nodeValue)}</{$element->nodeName}>";
                }
            }else if($element->nodeName == 'br'){
                $prepared .= '<br>';
            }else if($element->nodeName == 'img'){
                if(file_exists($_SERVER['DOCUMENT_ROOT'].'/Web/public'.$element->attributes['src']->nodeValue)){
                    $prepared .= '<img src="'.$element->attributes['src']->nodeValue.'">';
                }
            }
        }
    }
}