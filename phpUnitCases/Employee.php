<?php

class Employee
{
    private $name;
    private $userName;
    public $status;
    private $lastClockInTime;
    private $password = '123456';

    public function __construct($name, $userName)
    {
        $this->name = $name;
        $this->userName = $userName;
        $this->status = 'out';
    }

    public function login($password)
    {
        return $password === $this->password;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getLastClockInTime()
    {
        return $this->lastClockInTime;
    }

    public function clockIn()
    {
        $this->status = 'in';
        $this->lastClockInTime = time();
        return true;
    }

}
