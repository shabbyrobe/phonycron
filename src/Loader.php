<?php
namespace Phonycron;

class Loader
{
    public function load($class)
    {
        if (strpos($class, 'Phonycron\\')===0) {
            require(__DIR__.'/'.str_replace('\\', '/', 
                str_replace('..', '', substr($class, 10))).'.php');
            return true;
        }
    }
    
    public static function register()
    {
        spl_autoload_register(array(new static, 'load'));
    }
}
