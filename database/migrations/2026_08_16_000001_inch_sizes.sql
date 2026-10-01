-- Sizes given in inches.
--
-- A customer says "three foot by two" for a banner and "eighteen by twenty-four" for an
-- acrylic sign, and both are normal in the same shop on the same day. Until now every size
-- had to be typed in feet, so an inch size had to be divided by twelve in the head before
-- it could be entered — which is exactly where a wrong rate comes from.
--
-- 'inch' joins 'sqft' as a way of measuring a line. It changes only how the size is TYPED:
-- the billing unit stays the square foot, because every rate in this shop is per sq.ft.
-- 18in x 24in is worked out as 1.5ft x 2ft = 3 sq.ft, and the card still shows the inches
-- the customer gave.
--
-- The line keeps its own mode, so one order can hold a banner in feet and a sign in inches.
ALTER TABLE `categories`
  MODIFY COLUMN `calc_mode` ENUM('simple','sqft','inch','mixed') NOT NULL DEFAULT 'simple';

ALTER TABLE `category_components`
  MODIFY COLUMN `calc_mode` ENUM('simple','sqft','inch') NOT NULL DEFAULT 'simple';

ALTER TABLE `order_items`
  MODIFY COLUMN `calc_mode` ENUM('simple','sqft','inch') NOT NULL DEFAULT 'simple';
