

CREATE SEQUENCE donor_seq;

/*==============================================================*/
/* Table: Donors                                                */
/*==============================================================*/
CREATE TABLE donor (
    id              BIGINT          NOT NULL,
    name            VARCHAR(255)    NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE donor
    ALTER COLUMN    id              SET DEFAULT nextval('donor_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_donor       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_donor_name  UNIQUE(name);
