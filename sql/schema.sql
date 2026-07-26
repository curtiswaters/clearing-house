-- The Clearing House — businesses table
-- Run this once against your cPanel MySQL database before using api/listings.php.

CREATE TABLE IF NOT EXISTS businesses (
  id          VARCHAR(64)  NOT NULL PRIMARY KEY,
  name        VARCHAR(255) NOT NULL,
  category    VARCHAR(32)  NOT NULL,
  phone       VARCHAR(32)  NOT NULL,
  city        VARCHAR(64)  NOT NULL,
  website     VARCHAR(255) NULL,
  oneliner    VARCHAR(255) NOT NULL,
  description TEXT         NOT NULL,
  verified    TINYINT(1)   NOT NULL DEFAULT 0,
  featured    TINYINT(1)   NOT NULL DEFAULT 0,
  category_sponsor TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If you created this table before the `website` column existed, run instead:
-- ALTER TABLE businesses ADD COLUMN website VARCHAR(255) NULL AFTER city;

-- If you created this table before the pricing model below existed, run:
-- ALTER TABLE businesses ADD COLUMN verified TINYINT(1) NOT NULL DEFAULT 0 AFTER description;
-- ALTER TABLE businesses ADD COLUMN category_sponsor TINYINT(1) NOT NULL DEFAULT 0 AFTER featured;
