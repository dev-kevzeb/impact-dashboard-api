

CREATE SEQUENCE donor_seq;
CREATE SEQUENCE beneficiary_seq;
CREATE SEQUENCE program_state_seq;
CREATE SEQUENCE sdg_seq;
CREATE SEQUENCE agency_seq;
CREATE SEQUENCE currency_seq;

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

/*==============================================================*/
/* Table: SDG                                                   */
/*==============================================================*/
CREATE TABLE sdg (
    id              BIGINT          NOT NULL,
    image           VARCHAR(255)    NOT NULL,
    filename        VARCHAR(255)    NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE sdg
    ALTER COLUMN    id              SET DEFAULT nextval('sdg_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_sdg          PRIMARY KEY(id),
    ADD CONSTRAINT  uq_sdg_image    UNIQUE(image),
    ADD CONSTRAINT  uq_sdg_filename UNIQUE(filename);

/*==============================================================*/
/* Table: Agency                                                */
/*==============================================================*/
CREATE TABLE agency (
    id              BIGINT          NOT NULL,
    name            VARCHAR(100)    NOT NULL,
    url             VARCHAR(255)    NOT NULL,
    is_approved     BOOLEAN         NOT NULL DEFAULT FALSE,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE agency
    ALTER COLUMN    id              SET DEFAULT nextval('agency_seq'),
    ALTER COLUMN    is_approved     SET DEFAULT FALSE,
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_agency       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_agency_name  UNIQUE(name);

/*==============================================================*/
/* Table: Currency                                              */
/*==============================================================*/
CREATE TABLE currency (
    id              BIGINT          NOT NULL,
    code            VARCHAR(3)      NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE currency
    ALTER COLUMN    id              SET DEFAULT nextval('currency_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_currency       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_currency_code  UNIQUE(code);
