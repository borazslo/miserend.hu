DROP TABLE `terkep_vars`;

ALTER TABLE `miserend`.`templomok` 
ADD COLUMN `lat` DECIMAL(11,8) NULL AFTER `updated_at`,
ADD COLUMN `lon` DECIMAL(10,8) NULL AFTER `lat`,
ADD COLUMN `geoaddress` VARCHAR(255) NULL AFTER `lon`,
ADD COLUMN `geochecked` VARCHAR(1) NULL AFTER `geoaddress`;


UPDATE templomok
INNER JOIN terkep_geocode ON terkep_geocode.tid = templomok.id
SET templomok.lat = terkep_geocode.lat, templomok.lon = terkep_geocode.lng, 
templomok.geochecked = terkep_geocode.checked,
templomok.geoaddress = terkep_geocode.address2;

DROP TABLE `terkep_geocode`;