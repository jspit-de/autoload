<?php

class Mockjspitautoload extends autoload
{
    protected $files = array();

    public function setFiles(array $files)
    {
        $this->files = $files;
        return true;
    }

    protected function requireFile($file)
    {   
        return in_array($file, $this->files);
    }
}
