
CREATE TABLE users
(
  id            INTEGER       NOT NULL AUTO_INCREMENT,
  email         VARCHAR(64)   NOT NULL,
  password      VARCHAR(32)   NOT NULL,
  first_name    VARCHAR(16)   NOT NULL DEFAULT '',
  last_name     VARCHAR(16)   NOT NULL DEFAULT '',
  location      VARCHAR(64)   NOT NULL DEFAULT '',

  currency      CHAR(4)       NOT NULL DEFAULT '$',
  preference    VARCHAR(255)  NOT NULL DEFAULT '',

  last_ip       CHAR(16)      DEFAULT NULL,
  last_login    DATETIME      DEFAULT NULL,
  created_on    DATETIME      NOT NULL,
  
  attempts      INTEGER       UNSIGNED DEFAULT '0',
  last_attempt  DATETIME      DEFAULT NULL,

  PRIMARY KEY  (id),
  UNIQUE KEY UK_USERS_EMAIL (email)
) ENGINE=InnoDB;

CREATE TABLE filters
(
  id            INTEGER     NOT NULL AUTO_INCREMENT,
  user_id       INTEGER     NOT NULL,
  filter        VARCHAR(256) NOT NULL,

  PRIMARY KEY  (id),
  UNIQUE KEY UK_FILTERS_FILTER (filter),

  CONSTRAINT FK_FILTERS_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE referrals
(
  id            INTEGER     NOT NULL AUTO_INCREMENT,
  user_id       INTEGER     NOT NULL,
  email         VARCHAR(64) NOT NULL,
  referred_on   DATETIME    NOT NULL,

  PRIMARY KEY  (id),
  UNIQUE KEY UK_REFERRALS_EMAIL (email),

  CONSTRAINT FK_REFERRALS_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE accounts
(
  id            INTEGER NOT NULL AUTO_INCREMENT,
  user_id       INTEGER NOT NULL,
  name          VARCHAR (64) DEFAULT '',

  PRIMARY KEY  (id),

  KEY FK_ACCOUNTS_USERS_ID (user_id),
  UNIQUE KEY UK_ACCOUNTS_USER_ID_NAME (user_id, name),

  CONSTRAINT FK_ACCOUNTS_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE vendors
(
  id            INTEGER NOT NULL AUTO_INCREMENT,
  user_id       INTEGER NOT NULL,
  name          VARCHAR (64) DEFAULT '',

  PRIMARY KEY  (id),

  KEY FK_VENDORS_USERS_ID (user_id),
  UNIQUE KEY UK_VENDORS_USER_ID_NAME (user_id, name),

  CONSTRAINT FK_VENDORS_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE vendors_map
(
  id            INTEGER NOT NULL AUTO_INCREMENT,
  user_id       INTEGER NOT NULL,
  vendor_id     INTEGER NOT NULL,
  map           VARCHAR (64) DEFAULT NULL,

  PRIMARY KEY  (id),

  KEY FK_VENDORS_MAP_USERS_ID (user_id),
  KEY FK_VENDORS_MAP_VENDOR_ID (vendor_id),
  UNIQUE KEY UK_VENDORS_MAP_VENDORS_ID_MAP (vendor_id, map),

  CONSTRAINT FK_VENDORS_MAP_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT FK_VENDORS_MAP_VENDORS_ID FOREIGN KEY (vendor_id) REFERENCES vendors (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE categories
(
  id             INTEGER       NOT NULL AUTO_INCREMENT,
  user_id        INTEGER       NOT NULL,
  name           VARCHAR (128) DEFAULT '',

  budget_amount  DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  budget_visible CHAR(1)       NOT NULL DEFAULT 0,

  PRIMARY KEY  (id),

  KEY FK_CATEGORIES_USERS_ID (user_id),
  UNIQUE KEY UK_CATEGORIES_USER_ID_NAME (user_id, name),

  CONSTRAINT FK_CATEGORIES_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE goals
(
  id          INTEGER       NOT NULL AUTO_INCREMENT,
  user_id     INTEGER       NOT NULL,
  category_id INTEGER       NOT NULL,
  name        CHAR(32)      NOT NULL DEFAULT '',
  date_start  DATE          NOT NULL,
  date_mature DATE          NOT NULL,
  amount      DECIMAL(10,2) DEFAULT NULL,
  notes       VARCHAR(128)  DEFAULT NULL,

  PRIMARY KEY (id),

  KEY FK_GOALS_USERS_ID (user_id),
  UNIQUE KEY UK_GOALS_USER_ID_NAME (user_id, name),

  CONSTRAINT FK_GOALS_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT FK_GOALS_CATEGORIES_ID FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;


CREATE TABLE transactions
(
  id            INTEGER NOT NULL AUTO_INCREMENT,
  user_id       INTEGER NOT NULL,
  vendor_id     INTEGER DEFAULT NULL,
  account_id    INTEGER DEFAULT NULL,
  category_id   INTEGER DEFAULT NULL,

  date          DATE    NOT NULL,
  debit         CHAR(1) NOT NULL DEFAULT 1,
  amount        DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  fixed         CHAR(1) NOT NULL DEFAULT 0,
  notes         VARCHAR(128)  DEFAULT NULL,
  receipt       MEDIUMBLOB,

  entered_on    DATETIME NOT NULL,

  PRIMARY KEY  (id),

  KEY FK_TRANSACTIONS_USERS_ID (user_id),
  KEY FK_TRANSACTIONS_VENDORS_ID (vendor_id),
  KEY FK_TRANSACTIONS_ACCOUNTS_ID (account_id),
  KEY FK_TRANSACTIONS_CATEGORIES_ID (category_id),

  CONSTRAINT FK_TRANSACTIONS_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT FK_TRANSACTIONS_VENDORS_ID FOREIGN KEY (vendor_id) REFERENCES vendors (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT FK_TRANSACTIONS_ACCOUNTS_ID FOREIGN KEY (account_id) REFERENCES accounts (id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT FK_TRANSACTIONS_CATEGORIES_ID FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;



CREATE TABLE contributors
(
  id            INTEGER       NOT NULL AUTO_INCREMENT,
  email         VARCHAR(64)   NOT NULL,
  password      VARCHAR(32)   NOT NULL,
  first_name    VARCHAR(16)   NOT NULL DEFAULT '',
  last_name     VARCHAR(16)   NOT NULL DEFAULT '',
  role          VARCHAR(64)   NOT NULL DEFAULT '',
  photo         CHAR(1)       NOT NULL DEFAULT 0,
  description   TEXT,
  display_order DECIMAL(10,2) NOT NULL DEFAULT '9999',

  PRIMARY KEY (id),
  UNIQUE KEY UK_USERS_EMAIL (email)
) ENGINE=InnoDB;

CREATE TABLE testimonials
(
  id          INTEGER NOT NULL AUTO_INCREMENT,
  user_id     INTEGER NOT NULL,
  testimonial TEXT,

  PRIMARY KEY (id),
  CONSTRAINT FK_TESTIMONIALS_USERS_ID FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;