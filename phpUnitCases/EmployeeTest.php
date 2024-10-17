<?php

use PHPUnit\Framework\TestCase;

//reqs for files
require_once 'Employee.php';
require_once 'ClockInSystem.php';
require_once 'ClockOutSystem.php';
require_once 'Admin.php';

class EmployeeTest extends TestCase
{
    public function testLogin() //test login and denying if false info
    {
        $employee = new Employee('fakeName', 'fakeUser');

        $this->assertFalse($employee->login('wrongpassword'));
        $this->assertFalse($employee->login(''));
        $this->assertTrue($employee->login('123456')); // set password, function doesnt work yet
    }

    public function testClockIn() //test if clock in works
    {
        $employee = new Employee('fakeName2', 'fakerUser2');
        $clockInSystem = new ClockInSystem();

        $this->assertEquals('out', $employee->getStatus());

        $result = $clockInSystem->clockIn($employee);
        $this->assertTrue($result);

        $this->assertEquals('in', $employee->getStatus());


        $this->assertNotNull($employee->getLastClockInTime());
        $this->assertIsInt($employee->getLastClockInTime());
    }
    public function testEmployeeClockOutWithReason()  //test clocking out with specified reason
    {

        $employee = new Employee('fakeName3', 'fakeUser3');
        $this->assertTrue($employee->login('123456'));


        $clockInSystem = new ClockInSystem();
        $clockInSystem->clockIn($employee);


        $clockOutSystem = new ClockOutSystem();

        // clock using specified
        $reason = "personal";
        $result = $clockOutSystem->clockOut($employee, $reason);
        $this->assertTrue($result);

        // Ensure the employee status is updated correctly
        $this->assertEquals('out', $employee->getStatus());

        //clock out with other
        $clockInSystem->clockIn($employee);
        $reason = "other";
        $additionalInput = "Had to take a really long nap";
        $result = $clockOutSystem->clockOut($employee, $reason, $additionalInput);
        $this->assertTrue($result);
    }
    public function testAdminDash()
    {

        $admin = new Admin();
        $this->assertTrue($admin->login('adminUser', 'adminadmin'));


        $allEmployeeData = $admin->viewDashboard();
        $this->assertIsArray($allEmployeeData);

        foreach ($allEmployeeData as $employeeData) {
            $this->assertArrayHasKey('employeeId', $employeeData);
            $this->assertArrayHasKey('hoursWorked', $employeeData);
            $this->assertArrayHasKey('payAmount', $employeeData);
        }
    }


}
