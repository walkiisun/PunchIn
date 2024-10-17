<?php

class Admin
{
    private $username;
    private $password = 'adminadmin'; //set password for testing

    public function login($username, $password)
    {

        return $username === 'adminUser' && $password === $this->password;
    }

    public function viewDashboard()
    {

        return [
            [
                'employeeId' => 1,
                'hoursWorked' => 40,
                'payAmount' => 800,
            ],
            [
                'employeeId' => 2,
                'hoursWorked' => 35,
                'payAmount' => 700,
            ],

        ];
    }
}
