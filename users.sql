CREATE TABLE IF NOT EXISTS users (
  id          INT             NOT NULL UNIQUE AUTO_INCREMENT,
  name        VARCHAR(64)     NOT NULL UNIQUE,
  hash        CHAR(40)        NOT NULL,
  hashnonce   CHAR(40)        NOT NULL,
  cookienonce CHAR(40)        NOT NULL,
  first       VARCHAR(64),
  last        VARCHAR(64),
  title       VARCHAR(64),
  admin       BIT(1),
  PRIMARY KEY (id)
);