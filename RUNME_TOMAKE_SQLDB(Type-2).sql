-- MySQL Script generated by MySQL Workbench
-- Tue Oct 15 12:14:51 2024
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`Shift`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Shift` (
  `Valid_Clock_IN_Time` TIMESTAMP(3) NOT NULL COMMENT 'Table for building work positions; separated from Work_time_instance to distinguish from work times and shifts tied to positions themselves for flexibility ',
  `Valid_Clock_OUT_Time` TIMESTAMP(3) NOT NULL,
  `Timeslot` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`Timeslot`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Position`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Position` (
  `Position_ID` VARCHAR(45) NOT NULL,
  `Wage` INT NOT NULL,
  `Position_Name` VARCHAR(45) NOT NULL,
  `Shift_Timeslot` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`Position_ID`, `Shift_Timeslot`),
  INDEX `fk_Position_Shift1_idx` (`Shift_Timeslot` ASC) VISIBLE,
  CONSTRAINT `fk_Position_Shift1`
    FOREIGN KEY (`Shift_Timeslot`)
    REFERENCES `mydb`.`Shift` (`Timeslot`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`AdminUser`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`AdminUser` (
  `idAdminUser` INT NOT NULL,
  `Username` VARCHAR(45) NOT NULL,
  `Password` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`idAdminUser`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`EmployeeUser`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`EmployeeUser` (
  `User_ID` INT NOT NULL COMMENT 'MUST HAVE REQUIREMENT SET BY PROF',
  `First_Name` VARCHAR(45) NOT NULL,
  `Last_Name` VARCHAR(45) NOT NULL,
  `Password` VARCHAR(45) NOT NULL,
  `Username` VARCHAR(45) NOT NULL,
  `Position_Position_ID` VARCHAR(45) NOT NULL,
  `Account_Creation_Approval_Flag` TINYINT(0) NOT NULL DEFAULT 0 COMMENT 'Flag for account creation approval',
  PRIMARY KEY (`User_ID`),
  INDEX `fk_Employee_Position1_idx` (`Position_Position_ID` ASC) VISIBLE,
  INDEX `fk_EmployeeUser_AdminUser1_idx` (`Account_Creation_Approval_Flag` ASC) VISIBLE,
  CONSTRAINT `fk_Employee_Position1`
    FOREIGN KEY (`Position_Position_ID`)
    REFERENCES `mydb`.`Position` (`Position_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_EmployeeUser_AdminUser1`
    FOREIGN KEY (`Account_Creation_Approval_Flag`)
    REFERENCES `mydb`.`AdminUser` (`idAdminUser`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Work_Time_Instance`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Work_Time_Instance` (
  `Work_Time_ID` INT NOT NULL COMMENT 'Table for allowing which work times valid',
  `User_ID` INT NOT NULL,
  `Clock_IN_TimeStamp` TIMESTAMP(3) NOT NULL,
  `Clock_OUT_Timestamp` TIMESTAMP(3) NOT NULL,
  `Time_Tablecol` VARCHAR(45) NULL,
  PRIMARY KEY (`Work_Time_ID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Early_Leave_Types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Early_Leave_Types` (
  `idEarly_Leave` INT NOT NULL,
  `Early_Leave_Reason` TEXT(1000) NOT NULL,
  `if_other_reason` TINYINT NOT NULL DEFAULT 0 COMMENT 'Flag for selecting Other reason; will help in popping up textbox',
  `Other_Reason_text` TEXT(1000) NULL,
  PRIMARY KEY (`idEarly_Leave`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`ClockingIN`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`ClockingIN` (
  `Employee_User_ID` INT NOT NULL COMMENT 'Table for keeping track of who’s clocked in and out',
  `Time_Table_Work_Time_ID` INT NOT NULL,
  `Clock_IN_STATUS` TINYINT NOT NULL DEFAULT 0,
  `Early_Leave_Status` TINYINT NOT NULL COMMENT 'Flag for early leave',
  `ClockingINcol` VARCHAR(45) NULL,
  `Early_Leave_id` INT NULL COMMENT 'ID of what kind of early reason.',
  PRIMARY KEY (`Employee_User_ID`, `Time_Table_Work_Time_ID`),
  INDEX `fk_Employee_has_Time_Table_Time_Table1_idx` (`Time_Table_Work_Time_ID` ASC) VISIBLE,
  INDEX `fk_ClockingIN_Early_Leave1_idx` (`Early_Leave_id` ASC) VISIBLE,
  INDEX `fk_Employee_has_Time_Table_Employee1_idx` (`Employee_User_ID` ASC, `Early_Leave_Status` ASC) VISIBLE,
  CONSTRAINT `fk_Employee_has_Time_Table_Employee1`
    FOREIGN KEY (`Employee_User_ID` , `Early_Leave_Status`)
    REFERENCES `mydb`.`EmployeeUser` (`User_ID` , `Account_Creation_Approval_Flag`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Employee_has_Time_Table_Time_Table1`
    FOREIGN KEY (`Time_Table_Work_Time_ID`)
    REFERENCES `mydb`.`Work_Time_Instance` (`Work_Time_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ClockingIN_Early_Leave1`
    FOREIGN KEY (`Early_Leave_id`)
    REFERENCES `mydb`.`Early_Leave_Types` (`idEarly_Leave`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Early_Leave_Notices`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Early_Leave_Notices` (
)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
