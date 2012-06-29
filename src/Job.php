<?php
namespace Phonycron;

class Job
{
    const LAST_DAY_OF_MONTH = '-1';
    
    public $user;
    public $command;
    
    public $minutes;
    public $hours;
    public $daysOfMonth;
    public $months;
    public $daysOfWeek;
    
    public $hash;
    
    public $raw;
    
    public function runsAt($time)
    {
        if (!in_array(date('i', $time), $this->minutes))
            return false;
        if (!in_array(date('H', $time), $this->hours)) 
            return false;
        if ($this->daysOfMonth != self::LAST_DAY_OF_MONTH && !in_array(date('j', $time), $this->daysOfMonth)) 
            return false;
        if ($this->daysOfMonth == self::LAST_DAY_OF_MONTH && date('j', $time) != date('t', $time))
            return false;
        if (!in_array(date('w', $time), $this->daysOfWeek))
            return false;
        if (!in_array(date('n', $time), $this->months))
            return false;
        return true;
    }
    
    /**
     * This doesn't work properly - even if there are only x minutes in the day when
     * the cron will run, they will not be regular.
     */
    public function maxRunsPerDay()
    {
        return count($this->minutes) * count($this->hours);
    }
    
    public static function findMinRunsPerDay($jobList)
    {
        $max = null;
        foreach ($jobList as $job) {
            $runs = $job->maxRunsPerDay();
            if ($runs > $max) $max = $runs;
        }
        return $max;
    }
}
