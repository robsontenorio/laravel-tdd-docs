<?php

namespace Tenorio\Laravel\Tdd\Docs;

use \Illuminate\Support\Facades\File;

class DocGenerator
{
    protected $file;
    protected $class;

    public function __construct($file)
    {
        $this->file = $file->getRealPath();
        $this->class = $this->getClassName();
    }

    public function getClassName()
    {
        $file = File::get($this->file);
        $class = basename($this->file, '.php');
        $namespace = preg_match('/namespace (.+?);/i', $file, $matches);
        $class = $matches[1] . '\\' . $class;

        return $class;
    }

    public function build()
    {
        return (object)  $this->feature();
    }    

    public function feature()
    {
        $class = new \ReflectionClass($this->class);

        $doc = $class->getDocComment();
        $feature = $this->parseFeature($doc);                

        return $feature;
    }

    public function scenarios()
    {
        $methods = get_class_methods($this->class);

        $scenarios = [];

        foreach ($methods as $method_name) {
            if (strstr($method_name, 'test')) {
                $x = new \ReflectionMethod($this->class, $method_name);
                
                $title = $this->parseScenario($method_name);
                $steps = $this->parseSetps($x->getStartLine(), $x->getEndLine());  
                
                if (count($steps) == 0)
                {
                    $title = ' [NO STEPS] '.$title;
                }
                
                $scenarios[] = (object) ['title' => $title, 'method' => $method_name, 'steps' => $steps];                
            }
        }

        return collect($scenarios);
    }

    public function getFunctionContent($start_line, $end_line)
    {
        $source = file($this->file);
        $body = implode('', array_slice($source, $start_line, $end_line - $start_line));
        return $body;
    }

    public function parseSetps($start_line, $end_line)
    {
        $tokens = token_get_all(file_get_contents($this->file));        
        $steps = [];
        foreach ($tokens as $token) {
            if ($token[0] == T_DOC_COMMENT && ($token[2] > $start_line && $token[2] < $end_line)) {
                $text = $this->parseStep($token[1]);
                $line = $token[2];
                $steps[] = (object) ['text' => $text, 'line' => $line];
            }
        }

        return collect($steps);
    }

    private function parseStep($text)
    {
        $text = $this->removeDocNotation($text);
        // $text = $this->highlightFirstWords($text);

        return $text;
    }

    private function parseFeature($text)
    {
        $text = $this->removeDocNotation($text);
        
        preg_match('/(@feature)(\s)(.*)/', $text, $matches);

        if (count($matches) == 0)
        {
            throw new \Exception("You must include <strong>@feature</strong> anotation on your test class ({$this->class}).");
        }

        $title = $matches[3];
        $title_row = $matches[0];


        preg_match('/(@tag)(\s)(.*)/', $text, $matches);
        
        $tag = null;
        $tag_row = null;

        if (count($matches))
        {
            $tag = $matches[3];
            $tag_row = $matches[0];                                
        }
        

        $description = str_replace($title_row, '', $text);        
        $description = str_replace($tag_row, '', $description);
        $description = preg_replace("/(\s\s\n)/", "", $description);             
        // $description = $this->highlightFirstWords($description);
        
        $feature['id'] = str_replace('\\','', $this->getClassName());
        $feature['title'] = $title;        
        $feature['tag'] = $tag;            
        $feature['class'] = $this->getClassName();                
        $feature['description'] = nl2br($description);
        $feature['scenarios'] = $this->scenarios();        

        return (object) $feature;
    }

    private function parseScenario($text)
    {
        $text = preg_replace('/\btest?/', ' ', $text);
        $text = str_replace('_', ' ', $text);

        return $text;
    }

    private function highlightFirstWords($text)
    {
        preg_match_all('/(\n\s\S+ )/', $text, $matches);
        
         foreach($matches[0] as $m){
             $text = str_replace($m, "<strong>{$m}</strong>", $text);
         }

        return $text;
    }

    private function removeDocNotation($text)
    {
        return preg_replace('(\*\/|\*|\/\*)', ' ',$text);
    }
}
