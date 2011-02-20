CREATE TABLE dns_records (
    entry_id bigint NOT NULL,
    zone character varying(255) NOT NULL,
    host character varying(255) NOT NULL,
    type character varying(6) NOT NULL,
    data character varying NOT NULL,
    ttl integer NOT NULL,
    mx_priority integer,
    refresh integer,
    retry integer,
    expire integer,
    minimum integer,
    serial character varying(10),
    resp_person character varying(255),
    primary_ns character varying(255)
);

CREATE SEQUENCE dns_records_entry_id_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;
ALTER TABLE dns_records ALTER COLUMN entry_id SET DEFAULT nextval('dns_records_entry_id_seq'::regclass);
ALTER TABLE ONLY dns_records ADD CONSTRAINT dns_records_pkey PRIMARY KEY (entry_id);
CREATE INDEX dns_records_host_index ON dns_records USING btree (host);
CREATE INDEX dns_records_type_index ON dns_records USING btree (type);
CREATE INDEX dns_records_zone_index ON dns_records USING btree (zone);
