<?php

class ClockOutSystem
{
    public function clockOut(Employee $employee, $reason, $additionalInput = null)
    {
        // Check if the employee is currently clocked in
        if ($employee->getStatus() !== 'in') {
            return false; // Cannot clock out if not currently clocked in
        }


        $employee->status = 'out';
        return true;
    }
}
