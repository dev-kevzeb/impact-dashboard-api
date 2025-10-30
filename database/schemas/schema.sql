

CREATE SEQUENCE donor_seq;
CREATE SEQUENCE beneficiary_seq;
CREATE SEQUENCE program_state_seq;

/*==============================================================*/
/* Table: Donor                                                 */
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

/*==============================================================*/
/* Table: Beneficiary                                           */
/*==============================================================*/
CREATE TABLE beneficiary (
    id              BIGINT          NOT NULL,
    name            VARCHAR(255)    NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE beneficiary
    ALTER COLUMN    id              SET DEFAULT nextval('beneficiary_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_beneficiary       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_beneficiary_name  UNIQUE(name);

/*==============================================================*/
/* Table: ProgramState                                          */
/*==============================================================*/
CREATE TABLE program_state (
    id              BIGINT          NOT NULL,
    name            VARCHAR(255)    NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE program_state
    ALTER COLUMN    id              SET DEFAULT nextval('program_state_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_program_state       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_program_state_name  UNIQUE(name);
