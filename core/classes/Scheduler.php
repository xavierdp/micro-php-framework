<?php

use Cron\CronExpression;

class Scheduler {
    private $_current_time = 0;
    private $_timezone = "Europe/Paris";

    function __construct() {
        $this->_current_time = date("Y-m-d H:i:s");
    }

    // Get the current time
    public function getCurrentTime() {
        return $this->_current_time;
    }

    // Return the defined timezone
    public function getTimezone() {
        return $this->_timezone;
    }

    /**
     * Run a scheduler task if it's time to do it
     * @param callable $action Callback to execute
     * @param string $expression Cron expression to check (see https://packagist.org/packages/dragonmantank/cron-expression)
     */
    public function run(callable $action, string $expression) {

        $cron = new CronExpression($expression);
        $isDue = $cron->isDue($this->getCurrentTime(), $this->getTimezone());

        if($isDue) {
            try {
                $action();
                return true;
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        return false;
    }
    
}