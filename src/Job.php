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
    
    /**
     * Put any extra data in here.
     */
    public $metadata = array();
    
    public function runsAt($time)
    {
        $time = Functions::ensureDateTime($time);

        if (!in_array($time->format('i'), $this->minutes)) {
            return false;
        }
        if (!in_array($time->format('H'), $this->hours))  {
            return false;
        }
        if ($this->daysOfMonth != self::LAST_DAY_OF_MONTH && !in_array($time->format('j'), $this->daysOfMonth))  {
            return false;
        }
        if ($this->daysOfMonth == self::LAST_DAY_OF_MONTH && $time->format('j') != $time->format('t')) {
            return false;
        }
        if (!in_array($time->format('w'), $this->daysOfWeek)) {
            return false;
        }
        if (!in_array($time->format('n'), $this->months)) {
            return false;
        }
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
            if ($runs > $max) {
                $max = $runs;
            }
        }
        return $max;
    }
}
