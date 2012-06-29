<?php
namespace Phonycron;

class Parser
{    
    public $macros = array(
        '@yearly'  => '0 0 1 1 *',
        '@monthly' => '0 0 1 * *',
        '@weekly'  => '0 0 * * 0',
        '@daily'   => '0 0 * * *',
        '@hourly'  => '0 * * * *',
    );
    
    public $hasUsers = false;
    
    public $allowEmptyJob = false;
    
    private function splitByLine($raw)
    {
        return preg_split("/[\r\n]+/", trim($raw), null, PREG_SPLIT_NO_EMPTY);
    }
    
    public function parseFile($file)
    {
        return $this->parse(file_get_contents($file));
    }
    
    public function parse($raw)
    {
        if (!is_array($raw))
            $items = $this->splitByLine($raw);
        else
            $items = $raw;
    
        $fieldCount = $this->hasUsers ? 7 : 6;
    
        $jobs = array();
        foreach ($items as $line=>$i) {
            ++$line;
            $i = trim($i);
            
            if (empty($i) || $i[0] == '#') continue;
            if ($i[0] == '@') {
                $space = strpos($i, ' ');
                if ($space === false)
                    throw new ParseException("Unparseable macro line $i");
                $macro = substr($i, 0, $space);
                if (!isset($this->macros[$macro]))
                    throw new ParseException("Unrecognised macro $macro");
                $i = str_replace($macro, $this->macros[$macro], $i);
            }
    
            $split = preg_split('/\s+/', $i, $fieldCount);
            $splitCount = count($split);
            $min = $fieldCount - ($this->allowEmptyJob ? 1 : 0);
            $max = $fieldCount;
            if ($splitCount < $min || $splitCount > $max)
                throw new ParseException("Invalid cron job at line {$line}");
        
            $job = new Job;
            $job->raw = trim($i);
            
            $commandIndex = $this->hasUsers ? 6 : 5;
            if (isset($split[$commandIndex])) {
                $job->command = $this->hasUsers ? $split[6] : $split[5];
            }
            if ($this->hasUsers) {
                $job->user = $split[5];
            }
            
            $job->minutes = $this->parseNumeric($split[0], 0, 59);
            if ($job->minutes === false)
                throw new ParseException("Unparseable minutes on line $line");
            
            $job->hours = $this->parseNumeric($split[1], 0, 23);
            if ($job->hours === false)
                throw new ParseException("Unparseable hour on line $line");
            
            $job->daysOfMonth = $this->parseDayOfMonth($split[2]);
            if ($job->daysOfMonth === false)
                throw new ParseException("Unparseable day of month on line $line");
            
            $job->months = $this->parseMonth($split[3]);
            if ($job->months === false)
                throw new ParseException("Unparseable month on line $line");
            
            $job->daysOfWeek = $this->parseDayOfWeek($split[4]);
            if ($job->daysOfWeek === false)
                throw new ParseException("Unparseable day of week on line $line");
            
            if ($job->daysOfMonth === null && $job->daysOfWeek === null) {
                throw new ParseException("Value omitted for both day of month and day of week on line $line");
            }
            
            $job->hash = md5($i);
            $jobs[] = $job;
        }
    
        return $jobs;
    }
    
    public function parseNumeric($raw, $min, $max)
    {
        $raw = trim($raw);
        if (!$raw === "")
            return false;
        
        if (!preg_match('@^(\*|\d[\d,\-]*)(?:/(\d+))?$@', $raw, $match))
            return false;
        
        $items = null;
        $innerMin = $min;
        $innerMax = $max;
        
        if (strpos($match[1], ',')) {
            $exploded = explode(',', $match[1]);
            $items = array();
            foreach ($exploded as $i) {
                if (strpos($i, '-')!==false) {
                    $items = array_merge($items, $this->parseNumeric($i, $min, $max));
                }
                else {
                    $items[] = $i;
                }
            }
        }
        elseif (strpos($match[1], '-')) {
            if (!preg_match('@^(\d+)-(\d+)$@', $match[1], $rangeMatch))
                throw new ParseException("Could not extract range values");
            $innerMin = $rangeMatch[1];
            $innerMax = $rangeMatch[2];
            if ($innerMin > $innerMax)
                throw new ParseException("Range minimum $innerMin was greater than maximum $innerMax");
            
            $items = range($innerMin, $innerMax);
        }
        elseif ($match[1] != '*') {
            $innerMin = $match[1]; 
            $innerMax = isset($match[2]) ? $max : $match[1];
            $items = range($innerMin, $innerMax);
        }
        else {
            $items = range($min, $max);
        }
        
        if (!$items) {
            return false;
        }
        elseif (!ctype_digit(implode('', $items))) {
            throw new ParseException("Unknown characters in input");
        }
        
        if (isset($match[2])) {
            $mod = $match[2];
            $items = array_filter($items, function ($item) use ($mod, $innerMin) {
                return ($item - $innerMin) % $mod == 0;
            });
        }
        
        foreach ($items as &$i) {
            if (!is_numeric($i))
                throw new ParseException("Item $i was not numeric");
            
            $i = (int)$i;
            if ($i < $min)
                throw new ParseException("Range minimum was less than $min");
            if ($i > $max)
                throw new ParseException("Range minimum was greater than $max");
        }
        
        return array_values(array_unique($items));
    }
    
    public function parseDayOfMonth($raw)
    {
        // null equals "no value". only day of month OR day of week can be null
        if ($raw == '?')
            return null;
        elseif ($raw == 'L')
            return Job::LAST_DAY_OF_MONTH;
        if (strpos($raw, 'W')!==false)
            throw new \Exception("W modifier not supported yet. Too hard, not needed ATM.");
        
        return $this->parseNumeric($raw, 1, 31);
    }
    
    public function parseMonth($raw)
    {
        static $months = array(
            'jan'=>1, 'feb'=>2, 'mar'=>3, 'apr'=>4, 'may'=>5, 'jun'=>6, 
            'jul'=>7, 'aug'=>8, 'sep'=>9, 'oct'=>10, 'nov'=>11, 'dec'=>12,
        );
        $trans = strtr(strtolower($raw), $months);
        return $this->parseNumeric($trans, 1, 12);
    }
    
    public function parseDayOfWeek($raw)
    {
        static $days = array(
            // special case - if sunday is the right side of a date range, set
            // it to be 7 instead of 0 so range($min, $max) works properly
            '-sun'=>'-7',
            
            'sun'=>0, 'mon'=>1, 'tue'=>2, 'wed'=>3, 'thu'=>4, 'fri'=>5, 'sat'=>6,
        );
        if ($raw == '?') {
            $items = range(0, 6);
        }
        else {
            $items = $this->parseNumeric(strtr(strtolower($raw), $days), 0, 7);
            if (($key=array_search(7, $items))!==false) {
                unset($items[$key]);
                if (!in_array(0, $items)) {
                    array_unshift($items, 0);
                }
            }
        }
        return $items;
    }
}

class ParseException extends \Exception {}
