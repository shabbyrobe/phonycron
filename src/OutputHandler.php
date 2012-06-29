<?php
namespace Phonycron;

interface OutputHandler
{
    public function handle(Job $job, $output);
}
