<?php
namespace Phonycron;

class EchoOutputHandler implements OutputHandler
{
    public function handle(Job $job, $output)
    {
        echo date('Y-m-d H:i').': '.$job->raw.PHP_EOL;
        $output = trim($output);
        echo $output ? $output.PHP_EOL.PHP_EOL : null;
    }
}
