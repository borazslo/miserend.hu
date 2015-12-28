/* distance->distances */
/* eszrevetelek->remarks */
/* kepek->photos */

DELETE FROM distances WHERE `from` NOT IN  (SELECT id FROM templomok);
DELETE FROM distances WHERE `to` NOT IN  (SELECT id FROM templomok);

ALTER TABLE kepek ENGINE=InnoDB;
DELETE FROM kepek WHERE `tid` NOT IN  (SELECT id FROM templomok);

ALTER TABLE eszrevetelek ENGINE=InnoDB;
DELETE FROM eszrevetelek WHERE hol_id NOT IN  (SELECT id FROM templomok);

/* YOU NEED TO MANUALLY CHANGE THE TABLES */
/* BASED ON THE mysql_sample.sql */
/* SORRY */

ALTER TABLE `miserend`.`distances` 
ADD CONSTRAINT `FK_to`
  FOREIGN KEY (`to`)
  REFERENCES `miserend`.`templomok` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION,
ADD CONSTRAINT `FK_from`
  FOREIGN KEY (`from`)
  REFERENCES `miserend`.`templomok` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE `miserend`.`remarks` 
ADD CONSTRAINT `FK_church_id`
  FOREIGN KEY (`church_id`)
  REFERENCES `miserend`.`templomok` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;

ALTER TABLE `miserend`.`photos` 
ADD CONSTRAINT `FKchurch`
  FOREIGN KEY (`church_id`)
  REFERENCES `miserend`.`templomok` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;