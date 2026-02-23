

CREATE SEQUENCE donor_seq;
CREATE SEQUENCE beneficiary_seq;
CREATE SEQUENCE program_state_seq;
CREATE SEQUENCE sdg_seq;
CREATE SEQUENCE agency_seq;
CREATE SEQUENCE currency_seq;
CREATE SEQUENCE country_seq;
CREATE SEQUENCE kpa_seq;
CREATE SEQUENCE country_kpa_seq;
CREATE SEQUENCE strategic_output_seq;
CREATE SEQUENCE measure_seq;
CREATE SEQUENCE indicator_type_seq;
CREATE SEQUENCE indicator_seq;
CREATE SEQUENCE contact_seq;
CREATE SEQUENCE program_seq;
CREATE SEQUENCE program_sdg_seq;
CREATE SEQUENCE program_donor_seq;
CREATE SEQUENCE project_state_seq;
CREATE SEQUENCE project_seq;
CREATE SEQUENCE project_agency_seq;
CREATE SEQUENCE project_indicator_seq;
CREATE SEQUENCE role_seq;
CREATE SEQUENCE user_state_seq;
CREATE SEQUENCE user_seq;
CREATE SEQUENCE user_role_seq;
CREATE SEQUENCE country_kpa_user_seq;
CREATE SEQUENCE program_user_seq;


/*==============================================================*/
/* Table: Role                                                  */
/*==============================================================*/
CREATE TABLE role (
    id              BIGINT          NOT NULL,
    name            VARCHAR(50)     NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE role
    ALTER COLUMN    id              SET DEFAULT nextval('role_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_role       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_role_name  UNIQUE(name);

/*==============================================================*/
/* Table: UserState                                             */
/*==============================================================*/
CREATE TABLE user_state (
    id              BIGINT          NOT NULL,
    name            VARCHAR(50)     NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE user_state
    ALTER COLUMN    id              SET DEFAULT nextval('user_state_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_user_state       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_user_state_name  UNIQUE(name);

/*==============================================================*/
/* Table: User                                                  */
/*==============================================================*/
CREATE TABLE "user" (
    id                  BIGINT          NOT NULL,
    name                VARCHAR(255)    NOT NULL,
    email               VARCHAR(255)    NOT NULL,
    email_verified_at   TIMESTAMP       NULL,
    password            VARCHAR(255)    NOT NULL,
    remember_token      VARCHAR(100)    NULL,
    user_state_id       BIGINT          NOT NULL,
    created_at          TIMESTAMP       NOT NULL,
    updated_at          TIMESTAMP       NOT NULL
);

ALTER TABLE "user"
    ALTER COLUMN    id              SET DEFAULT nextval('user_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_user         PRIMARY KEY(id),
    ADD CONSTRAINT  uq_user_email   UNIQUE(email),
    ADD CONSTRAINT  fk_user_user_state FOREIGN KEY(user_state_id) REFERENCES user_state(id);

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
    first_name      VARCHAR(255)    NOT NULL,
    last_name       VARCHAR(255)    NOT NULL,
    title           VARCHAR(255)    NOT NULL,
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
    ADD CONSTRAINT  uq_program_name UNIQUE(name),
    ADD CONSTRAINT  fk_program_contact         FOREIGN KEY (contact_id) REFERENCES contact(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_program_program_state   FOREIGN KEY (program_state_id) REFERENCES program_state(id) ON DELETE RESTRICT;

CREATE INDEX idx_program_name ON program(name);
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

/*==============================================================*/
/* Table: CountryKpa_User (Pivot for UserRole assignments)      */
/*==============================================================*/
CREATE TABLE country_kpa_user (
    id              BIGINT          NOT NULL,
    country_kpa_id  BIGINT          NOT NULL,
    user_role_id    BIGINT          NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE country_kpa_user
    ALTER COLUMN    id              SET DEFAULT nextval('country_kpa_user_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_country_kpa_user  PRIMARY KEY(id),
    ADD CONSTRAINT  uq_country_kpa_user_role_combination  UNIQUE(country_kpa_id, user_role_id),
    ADD CONSTRAINT  fk_country_kpa_user_country_kpa FOREIGN KEY (country_kpa_id) REFERENCES country_kpa(id) ON DELETE CASCADE,
    ADD CONSTRAINT  fk_country_kpa_user_user_role FOREIGN KEY (user_role_id) REFERENCES user_role(id) ON DELETE CASCADE;

/*==============================================================*/
/* Table: ProgramUser (Pivot for Program-CountryKpaUser)        */
/*==============================================================*/
CREATE TABLE program_user (
    id                      BIGINT          NOT NULL,
    program_id              BIGINT          NOT NULL,
    country_kpa_user_id     BIGINT          NOT NULL,
    created_at              TIMESTAMP       NOT NULL,
    updated_at              TIMESTAMP       NOT NULL
);

ALTER TABLE program_user
    ALTER COLUMN    id              SET DEFAULT nextval('program_user_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_program_user  PRIMARY KEY(id),
    ADD CONSTRAINT  uq_program_country_kpa_user  UNIQUE(program_id, country_kpa_user_id),
    ADD CONSTRAINT  fk_program_user_program FOREIGN KEY (program_id) REFERENCES program(id) ON DELETE CASCADE,
    ADD CONSTRAINT  fk_program_user_country_kpa_user FOREIGN KEY (country_kpa_user_id) REFERENCES country_kpa_user(id) ON DELETE CASCADE;

/*==============================================================*/
/* Table: User_Role (Pivot for User-Role assignments)           */
/*==============================================================*/
CREATE TABLE user_role (
    id              BIGINT          NOT NULL,
    user_id         BIGINT          NOT NULL,
    role_id         BIGINT          NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE user_role
    ALTER COLUMN    id              SET DEFAULT nextval('user_role_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_user_role  PRIMARY KEY(id),
    ADD CONSTRAINT  uq_user_role_combination  UNIQUE(user_id, role_id),
    ADD CONSTRAINT  fk_user_role_user FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE,
    ADD CONSTRAINT  fk_user_role_role FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE;

/*==============================================================*/
/* Table: KPA (Key Priority Area)                               */
/*==============================================================*/
CREATE TABLE kpa (
    id                  BIGINT          NOT NULL,
    name                VARCHAR(100)    NOT NULL,
    implementation      NUMERIC(5, 2)   NOT NULL DEFAULT 0,
    created_at          TIMESTAMP       NOT NULL,
    updated_at          TIMESTAMP       NOT NULL
);

ALTER TABLE kpa
    ALTER COLUMN    id              SET DEFAULT nextval('kpa_seq'),
    ALTER COLUMN    implementation  SET DEFAULT 0,
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_kpa          PRIMARY KEY(id),
    ADD CONSTRAINT  uq_kpa_name     UNIQUE(name);

/*==============================================================*/
/* Table: CountryKpa (Pivot for Country-KPA)                    */
/*==============================================================*/
CREATE TABLE country_kpa (
    id              BIGINT          NOT NULL,
    id_country      BIGINT          NOT NULL,
    id_kpa          BIGINT          NOT NULL
);

ALTER TABLE country_kpa
    ALTER COLUMN    id              SET DEFAULT nextval('country_kpa_seq'),
    ADD CONSTRAINT  pk_country_kpa  PRIMARY KEY(id),
    ADD CONSTRAINT  uq_country_kpa  UNIQUE(id_country, id_kpa),
    ADD CONSTRAINT  fk_country_kpa_country FOREIGN KEY (id_country) REFERENCES country(id) ON DELETE CASCADE,
    ADD CONSTRAINT  fk_country_kpa_kpa     FOREIGN KEY (id_kpa) REFERENCES kpa(id) ON DELETE CASCADE;

/*==============================================================*/
/* Table: StrategicOutput                                       */
/*==============================================================*/
CREATE TABLE strategic_output (
    id              BIGINT          NOT NULL,
    name            VARCHAR(255)    NOT NULL,
    id_ck           BIGINT          NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE strategic_output
    ALTER COLUMN    id              SET DEFAULT nextval('strategic_output_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_strategic_output PRIMARY KEY(id),
    ADD CONSTRAINT  fk_strategic_output_country_kpa FOREIGN KEY (id_ck) REFERENCES country_kpa(id) ON DELETE CASCADE;

/*==============================================================*/
/* Table: Measure                                               */
/*==============================================================*/
CREATE TABLE measure (
    id                      BIGINT          NOT NULL,
    name                    VARCHAR(100)    NOT NULL,
    strategic_output_id     BIGINT          NOT NULL,
    created_at              TIMESTAMP       NOT NULL,
    updated_at              TIMESTAMP       NOT NULL
);

ALTER TABLE measure
    ALTER COLUMN    id              SET DEFAULT nextval('measure_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_measure      PRIMARY KEY(id),
    ADD CONSTRAINT  uq_strategic_output_measure UNIQUE(strategic_output_id, name),
    ADD CONSTRAINT  fk_measure_strategic_output FOREIGN KEY (strategic_output_id) REFERENCES strategic_output(id) ON DELETE CASCADE;

/*==============================================================*/
/* Table: IndicatorType                                         */
/*==============================================================*/
CREATE TABLE indicator_type (
    id              BIGINT          NOT NULL,
    name            VARCHAR(100)    NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE indicator_type
    ALTER COLUMN    id              SET DEFAULT nextval('indicator_type_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_indicator_type       PRIMARY KEY(id),
    ADD CONSTRAINT  uq_indicator_type_name  UNIQUE(name);

/*==============================================================*/
/* Table: Indicator                                             */
/*==============================================================*/
CREATE TABLE indicator (
    id              BIGINT          NOT NULL,
    name            VARCHAR(100)    NOT NULL,
    target          NUMERIC         NOT NULL,
    type_id         BIGINT          NOT NULL,
    measure_id      BIGINT          NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE indicator
    ALTER COLUMN    id              SET DEFAULT nextval('indicator_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_indicator    PRIMARY KEY(id),
    ADD CONSTRAINT  uq_measure_indicator UNIQUE(measure_id, name),
    ADD CONSTRAINT  fk_indicator_type    FOREIGN KEY (type_id) REFERENCES indicator_type(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_indicator_measure FOREIGN KEY (measure_id) REFERENCES measure(id) ON DELETE SET NULL;

/*==============================================================*/
/* Table: ProjectState                                          */
/*==============================================================*/
CREATE TABLE project_state (
    id              BIGINT          NOT NULL,
    state           VARCHAR(100)    NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE project_state
    ALTER COLUMN    id              SET DEFAULT nextval('project_state_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_project_state        PRIMARY KEY(id),
    ADD CONSTRAINT  uq_project_state_state  UNIQUE(state);

/*==============================================================*/
/* Table: Project                                               */
/*==============================================================*/
CREATE TABLE project (
    id                  BIGINT          NOT NULL,
    name                VARCHAR(255)    NOT NULL,
    description         VARCHAR(2000)   NOT NULL,
    project_url         VARCHAR(255)    NULL,
    start_date          DATE            NOT NULL,
    end_date            DATE            NOT NULL,
    progress            NUMERIC(5, 2)   NOT NULL DEFAULT 0,
    comments            VARCHAR(1000)   NULL,
    project_budget      NUMERIC(15, 2)  NOT NULL,
    contact_id          BIGINT          NULL,
    beneficiary_id      BIGINT          NULL,
    project_state_id    BIGINT          NULL,
    program_id          BIGINT          NOT NULL,
    created_at          TIMESTAMP       NOT NULL,
    updated_at          TIMESTAMP       NOT NULL
);

ALTER TABLE project
    ALTER COLUMN    id              SET DEFAULT nextval('project_seq'),
    ALTER COLUMN    progress        SET DEFAULT 0,
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_project      PRIMARY KEY(id),
    ADD CONSTRAINT  fk_project_contact         FOREIGN KEY (contact_id) REFERENCES contact(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_project_beneficiary     FOREIGN KEY (beneficiary_id) REFERENCES beneficiary(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_project_project_state   FOREIGN KEY (project_state_id) REFERENCES project_state(id) ON DELETE RESTRICT,
    ADD CONSTRAINT  fk_project_program         FOREIGN KEY (program_id) REFERENCES program(id) ON DELETE RESTRICT;

/*==============================================================*/
/* Table: ProjectAgency (Pivot for Project-Agency)              */
/*==============================================================*/
CREATE TABLE project_agency (
    id              BIGINT          NOT NULL,
    project_id      BIGINT          NOT NULL,
    agency_id       BIGINT          NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE project_agency
    ALTER COLUMN    id              SET DEFAULT nextval('project_agency_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_project_agency  PRIMARY KEY(id),
    ADD CONSTRAINT  uq_project_agency  UNIQUE(project_id, agency_id),
    ADD CONSTRAINT  fk_project_agency_project FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE CASCADE,
    ADD CONSTRAINT  fk_project_agency_agency  FOREIGN KEY (agency_id) REFERENCES agency(id) ON DELETE CASCADE;

/*==============================================================*/
/* Table: ProjectIndicator (Pivot for Project-Indicator)        */
/*==============================================================*/
CREATE TABLE project_indicator (
    id              BIGINT          NOT NULL,
    project_id      BIGINT          NOT NULL,
    indicator_id    BIGINT          NOT NULL,
    created_at      TIMESTAMP       NOT NULL,
    updated_at      TIMESTAMP       NOT NULL
);

ALTER TABLE project_indicator
    ALTER COLUMN    id              SET DEFAULT nextval('project_indicator_seq'),
    ALTER COLUMN    created_at      SET DEFAULT CURRENT_TIMESTAMP,
    ALTER COLUMN    updated_at      SET DEFAULT CURRENT_TIMESTAMP,
    ADD CONSTRAINT  pk_project_indicator  PRIMARY KEY(id),
    ADD CONSTRAINT  uq_project_indicator  UNIQUE(project_id, indicator_id),
    ADD CONSTRAINT  fk_project_indicator_project   FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE CASCADE,
    ADD CONSTRAINT  fk_project_indicator_indicator FOREIGN KEY (indicator_id) REFERENCES indicator(id) ON DELETE CASCADE;
