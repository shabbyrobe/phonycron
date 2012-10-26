<?php
namespace Phonycron;

class FileOutputHandler implements OutputHandler
{
    public $file;

    public function __construct($file)
    {
        if (!is_writable(dirname($file)))
            throw new \InvalidArgumentException("Directory for cron file was not writable");
        
        $this->file = $file;
    }

    public function handle(Job $job, $output)
    {
        $output = trim($output);
        
        $h = fopen($this->file, 'a');
        fwrite($h, date('Y-m-d H:i:s').': '.$job->raw.PHP_EOL);
        
        if ($output)
            fwrite($h, $output.PHP_EOL.PHP_EOL);
        
        fclose($h);
    }
}
