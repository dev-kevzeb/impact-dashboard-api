

CREATE SEQUENCE donor_seq;
CREATE SEQUENCE beneficiary_seq;
CREATE SEQUENCE program_state_seq;
CREATE SEQUENCE sdg_seq;
CREATE SEQUENCE agency_seq;
CREATE SEQUENCE currency_seq;
CREATE SEQUENCE country_seq;
CREATE SEQUENCE contact_seq;
CREATE SEQUENCE program_seq;
CREATE SEQUENCE program_sdg_seq;
CREATE SEQUENCE program_donor_seq;
CREATE SEQUENCE user_role_seq;

/*==============================================================*/
/* Table: UserRole                                              */
/*==============================================================*/
CREATE TABLE user_role (
    id              BIGINT          NOT NULL,
    name            VARCHAR(50)     NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE user_role
    ALTER COLUMN    id              SET DEFAULT nextval('user_role_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_user_role       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_user_role_name  UNIQUE(name);

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

/*==============================================================*/
/* Table: Country                                               */
/*==============================================================*/
CREATE TABLE country (
    id              BIGINT          NOT NULL,
    name            VARCHAR(100)    NOT NULL,
    currency_id     BIGINT          NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE country
    ALTER COLUMN    id              SET DEFAULT nextval('country_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_country        PRIMARY KEY(id),
    ADD CONSTRAINT  uq_country_name   UNIQUE(name),
    ADD CONSTRAINT  fk_country_currency FOREIGN KEY (currency_id) REFERENCES currency(id);

/*==============================================================*/
/* Table: Contact                                               */
/*==============================================================*/
CREATE TABLE contact (
    id              BIGINT          NOT NULL,
    name            VARCHAR(255)    NOT NULL,
    email           VARCHAR(255)    NOT NULL,
    phone           VARCHAR(20)     NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE contact
    ALTER COLUMN    id              SET DEFAULT nextval('contact_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_contact       PRIMARY KEY(id);

/*==============================================================*/
/* Table: Program                                               */
/*==============================================================*/
CREATE TABLE program (
    id                  BIGINT          NOT NULL,
    name                VARCHAR(255)    NOT NULL,
    description         TEXT            NOT NULL,
    banner_img          VARCHAR(500)    NULL,
    program_url         VARCHAR(500)    NULL,
    contact_id          BIGINT          NOT NULL,
    program_state_id    BIGINT          NOT NULL,
    created_at          TIMESTAMP       NOT NULL,
    updated_at          TIMESTAMP       NOT NULL
);

ALTER TABLE program
    ALTER COLUMN    id              SET DEFAULT nextval('program_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_program      PRIMARY KEY(id),
    ADD CONSTRAINT  fk_program_contact         FOREIGN KEY (contact_id) REFERENCES contact(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_program_beneficiary     FOREIGN KEY (beneficiary_id) REFERENCES beneficiary(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_program_program_state   FOREIGN KEY (program_state_id) REFERENCES program_state(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_program_country         FOREIGN KEY (country_id) REFERENCES country(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_program_agency          FOREIGN KEY (agency_id) REFERENCES agency(id) ON DELETE RESTRICT;

CREATE INDEX idx_program_name ON program(name);
CREATE INDEX idx_program_country ON program(country_id);
CREATE INDEX idx_program_agency ON program(agency_id);
CREATE INDEX idx_program_state ON program(program_state_id);

/*==============================================================*/
/* Table: Program_SDG (Pivot)                                   */
/*==============================================================*/
CREATE TABLE program_sdg (
    id              BIGINT          NOT NULL,
    program_id      BIGINT          NOT NULL,
    sdg_id          BIGINT          NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE program_sdg
    ALTER COLUMN    id              SET DEFAULT nextval('program_sdg_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_program_sdg  PRIMARY KEY(id),
    ADD CONSTRAINT  uq_program_sdg  UNIQUE(program_id, sdg_id),
    ADD CONSTRAINT  fk_program_sdg_program FOREIGN KEY (program_id) REFERENCES program(id) ON DELETE CASCADE,
    ADD CONSTRAINT  fk_program_sdg_sdg     FOREIGN KEY (sdg_id) REFERENCES sdg(id) ON DELETE CASCADE;

/*==============================================================*/
/* Table: Program_Donor (Pivot)                                 */
/*==============================================================*/
CREATE TABLE program_donor (
    id              BIGINT          NOT NULL,
    program_id      BIGINT          NOT NULL,
    donor_id        BIGINT          NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE program_donor
    ALTER COLUMN    id              SET DEFAULT nextval('program_donor_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_program_donor  PRIMARY KEY(id),
    ADD CONSTRAINT  uq_program_donor  UNIQUE(program_id, donor_id),
    ADD CONSTRAINT  fk_program_donor_program FOREIGN KEY (program_id) REFERENCES program(id) ON DELETE CASCADE,
    ADD CONSTRAINT  fk_program_donor_donor   FOREIGN KEY (donor_id) REFERENCES donor(id) ON DELETE CASCADE;

