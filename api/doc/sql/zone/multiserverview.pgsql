CREATE TABLE mv_idmap (
    myid bigint NOT NULL,
    viewname character varying,
    viewid character varying
);

CREATE TABLE mv_records (
    id bigint NOT NULL,
    zone character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(6) NOT NULL,
    content character varying(255) NOT NULL,
    ttl integer NOT NULL,
    prio integer
);

CREATE SEQUENCE mv_records_id_seq START WITH 1 INCREMENT BY 1 NO MAXVALUE NO MINVALUE CACHE 1;
ALTER TABLE mv_records ALTER COLUMN id SET DEFAULT nextval('mv_records_id_seq'::regclass);
ALTER TABLE ONLY mv_records ADD CONSTRAINT mv_records_pkey PRIMARY KEY (id);
ALTER TABLE ONLY mv_records ADD CONSTRAINT mv_records_zone_key UNIQUE (zone, name, type, content, ttl, prio);
CREATE INDEX mv_records_domain_key ON mv_records USING btree (zone);
CREATE INDEX mv_records_myid_key ON mv_idmap USING btree (myid);
CREATE INDEX mv_records_myid_viewname_key ON mv_idmap USING btree (myid, viewname);
ALTER TABLE ONLY mv_idmap ADD CONSTRAINT mv_idmap_myid_fkey FOREIGN KEY (myid) REFERENCES mv_records(id);

