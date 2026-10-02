-- A unit written in Gujarati needs more than twenty letters.
--
-- "unit" was VARCHAR(20), sized for "pcs" and "sqft". The counter writes "ચોરસ ફૂટ પ્રમાણે"
-- or "નંગ પ્રમાણે", runs past twenty characters, and the whole order is refused with
-- SQLSTATE[22001] — a written-up order lost to a limit nobody could see.
--
-- The application now trims anything longer, so this can no longer break a save. The column
-- is widened as well so a real Gujarati unit is kept whole instead of being cut short.
ALTER TABLE `order_items`         MODIFY COLUMN `unit` VARCHAR(40) NULL;
ALTER TABLE `category_components` MODIFY COLUMN `unit` VARCHAR(40) NOT NULL DEFAULT 'pcs';
ALTER TABLE `items`               MODIFY COLUMN `unit` VARCHAR(40) NOT NULL DEFAULT 'pcs';
