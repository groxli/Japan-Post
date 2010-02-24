SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `todofuken`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `todofuken` (
  `todofuken_id` INT NOT NULL AUTO_INCREMENT ,
  `kanji` VARCHAR(100) NOT NULL ,
  `reading` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`todofuken_id`) ,
  UNIQUE INDEX `todofukenk_id_UNIQUE` (`todofuken_id` ASC) ,
  UNIQUE INDEX `kanji_UNIQUE` (`kanji` ASC) ,
  UNIQUE INDEX `reading_UNIQUE` (`reading` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `postal_address`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `postal_address` (
  `address_id` INT NOT NULL AUTO_INCREMENT ,
  `todofuken_id` INT NOT NULL ,
  `postal_3` INT NOT NULL ,
  `postal_7` INT NOT NULL ,
  `addr_1` VARCHAR(100) NOT NULL ,
  `addr_1_reading` VARCHAR(100) NOT NULL ,
  `addr_2` VARCHAR(100) NOT NULL ,
  `addr_2_reading` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`address_id`) ,
  UNIQUE INDEX `address_id_UNIQUE` (`address_id` ASC) ,
  INDEX `fk_todofuken_id` (`todofuken_id` ASC) ,
  CONSTRAINT `fk_todofuken_id`
    FOREIGN KEY (`todofuken_id` )
    REFERENCES `todofuken` (`todofuken_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `kanji`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `kanji` (
  `kanji_id` INT NOT NULL AUTO_INCREMENT ,
  `kanji_num` INT NOT NULL ,
  `new_glyph` VARCHAR(2) NOT NULL ,
  `old_glyph` VARCHAR(2) NULL ,
  `num_strokes` INT NOT NULL ,
  `grade` INT NOT NULL ,
  `is_secondary` TINYINT(1) NULL ,
  `is_jinmeiyo` TINYINT(1) NULL ,
  PRIMARY KEY (`kanji_id`) ,
  UNIQUE INDEX `kanji_id_UNIQUE` (`kanji_id` ASC) ,
  UNIQUE INDEX `kanji_num_UNIQUE` (`kanji_num` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `address_glyph`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `address_glyph` (
  `address_id` INT NOT NULL ,
  `glyph` VARCHAR(15) NOT NULL ,
  `kanji_id` INT NULL ,
  `zip_3` INT NOT NULL ,
  `zip_7` INT NOT NULL ,
  `is_todofuken` TINYINT(1) NULL ,
  INDEX `fk_kanji_e_id` (`kanji_id` ASC) ,
  INDEX `fk_p_addr_id` (`address_id` ASC) ,
  CONSTRAINT `fk_kanji_e_id`
    FOREIGN KEY (`kanji_id` )
    REFERENCES `kanji` (`kanji_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_p_addr_id`
    FOREIGN KEY (`address_id` )
    REFERENCES `postal_address` (`address_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `address_population`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `address_population` (
  `address_id` INT NOT NULL ,
  `from_date` DATE NULL ,
  `until_date` DATE NULL ,
  `population_count` INT NOT NULL ,
  PRIMARY KEY (`address_id`) ,
  INDEX `fk_population_addr_id` (`address_id` ASC) ,
  CONSTRAINT `fk_population_addr_id`
    FOREIGN KEY (`address_id` )
    REFERENCES `postal_address` (`address_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- procedure addAddressGlyph
-- -----------------------------------------------------

DELIMITER $$
DELIMITER //
USE `yusei`$$
CREATE PROCEDURE addAddressGlyph(IN _addressID INT, IN _glyph CHAR(1))

BEGIN

  INSERT INTO address_glyph (address_id, glyph) VALUES (_addressID, _glyph);

END 
//
DELIMITER ;
;
$$

-- -----------------------------------------------------
-- procedure getTodofukenID
-- -----------------------------------------------------
DELIMITER //
USE `yusei`$$


CREATE PROCEDURE getTodofukenID(OUT _returnID INT, IN _todofuken VARCHAR(100))

BEGIN

  SELECT todofuken_id  INTO _returnID FROM todofuken WHERE kanji = _todofuken;
  
END 
//
DELIMITER ;
;
$$

-- -----------------------------------------------------
-- procedure addKanji
-- -----------------------------------------------------
DELIMITER //
USE `yusei`$$


CREATE PROCEDURE addKanji(IN _kanjiNum INT, IN _newGlyph CHAR(1), IN _oldGlyph CHAR(1), IN _numStrokes INT, IN _grade INT, IN _isSecondary BOOL, IN _isJinmeiyo BOOL)

BEGIN

  INSERT INTO kanji (kanji_num, new_glyph, old_glyph, num_strokes, grade, is_secondary, is_jinmeiyo) VALUES (_kanjiNum, _newGlyph, _oldGlyph, _numStrokes, _grade, _isJinmeiyo);

END 
//
DELIMITER ;
;
$$

-- -----------------------------------------------------
-- procedure addPostalAddress
-- -----------------------------------------------------
DELIMITER //
USE `yusei`$$


CREATE PROCEDURE addPostalAddress(IN _todofuken VARCHAR(100), IN _postal3 INT, IN _postal7 INT, IN _addr1 VARCHAR(100), IN _addr1r VARCHAR(100), IN _addr2 VARCHAR(100), IN _addr2r VARCHAR(100))

BEGIN

  DECLARE tID INT;
  DECLARE addr1Length INT;
  DECLARE addr2length INT;
  DECLARE tKanjiID INT;
  DECLARE tAddrID INT;
  DECLARE counter1 INT DEFAULT 0;
  DECLARE counter2 INT DEFAULT 0;
  DECLARE tChar CHAR(1);

  SELECT todofuken_id  INTO tID FROM todofuken WHERE kanji = _todofuken;
  SELECT CHAR_LENGTH(_addr1) INTO addr1Length;
  SELECT CHAR_LENGTH(_addr2) INTO addr2length;

  

  INSERT INTO postal_address (todofuken_id, postal_3, postal_7, addr_1, addr_1_reading, addr_2, addr_2_reading) VALUES (tID, _postal3, _postal7, _addr1, _addr1r, _addr2, _addr2r);
  
  SELECT LAST_INSERT_ID() INTO tAddrID;

  addr1_char_loop: REPEAT
    SELECT SUBSTRING(_addr1, counter1, 1) INTO tChar;
    SELECT kanji_id INTO tKanjiID FROM kanji WHERE new_glyph = tChar;
    INSERT INTO address_glyph (address_id, glyph, kanji_id) VALUES (tAddrID , tChar, tKanjiID);
    SET counter1 = counter1 + 1;
    SET tKanjiID = NULL;
    SET tChar = NULL;
  UNTIL counter1 >= addr1Length END REPEAT;

END 
//
DELIMITER ;
;
$$

DELIMITER ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
