CREATE TABLE soa (
  id      SERIAL NOT NULL PRIMARY KEY,
  origin  VARCHAR(255) NOT NULL,
  ns      VARCHAR(255) NOT NULL,
  mbox    VARCHAR(255) NOT NULL,
  serial  INTEGER NOT NULL default 1,
  refresh INTEGER NOT NULL default 28800,
  retry   INTEGER NOT NULL default 7200,
  expire  INTEGER NOT NULL default 604800,
  minimum INTEGER NOT NULL default 86400,
  ttl     INTEGER NOT NULL default 86400,
  UNIQUE  (origin)
);

CREATE TABLE rr (
  id     SERIAL NOT NULL PRIMARY KEY,
  zone   INTEGER NOT NULL,
  name   VARCHAR(64) NOT NULL,
  type   VARCHAR(5) NOT NULL CHECK (type='A' OR type='AAAA' OR type='CNAME' OR type='HINFO' OR type='MX' OR type='NAPTR' OR type='NS' OR type='PTR' OR type='RP' OR type='SRV' OR type='TXT'),
  data   VARCHAR(128) NOT NULL,
  aux    INTEGER NOT NULL default 0,
  ttl    INTEGER NOT NULL default 86400,
  UNIQUE (zone,name,type,data),
  FOREIGN KEY (zone) REFERENCES soa (id) ON DELETE CASCADE
);
