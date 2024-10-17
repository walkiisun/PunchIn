<?php

class ClockInSystem
{
    public function clockIn(Employee $employee)
    {
        return $employee->clockIn();
    }
}
