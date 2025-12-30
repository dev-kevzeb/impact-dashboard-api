-- ============================================
-- DROP SCHEMA SCRIPT FOR PACIFIC ECOMMERCE
-- ============================================
-- Drops all tables and sequences in correct order (respecting foreign key dependencies)
-- Use this script to clean/reset the database completely
-- Created: December 30, 2025
-- ============================================

-- ============================================
-- DROP PIVOT/DEPENDENT TABLES FIRST
-- ============================================

DROP TABLE IF EXISTS project_indicator CASCADE;
DROP TABLE IF EXISTS project_agency CASCADE;
DROP TABLE IF EXISTS program_user CASCADE;
DROP TABLE IF EXISTS country_kpa_user CASCADE;
DROP TABLE IF EXISTS user_role CASCADE;
DROP TABLE IF EXISTS program_donor CASCADE;
DROP TABLE IF EXISTS program_sdg CASCADE;

-- ============================================
-- DROP MAIN ENTITY TABLES (with dependencies)
-- ============================================

DROP TABLE IF EXISTS project CASCADE;
DROP TABLE IF EXISTS program CASCADE;
DROP TABLE IF EXISTS contact CASCADE;
DROP TABLE IF EXISTS indicator CASCADE;
DROP TABLE IF EXISTS measure CASCADE;
DROP TABLE IF EXISTS strategic_output CASCADE;
DROP TABLE IF EXISTS country_kpa CASCADE;
DROP TABLE IF EXISTS "user" CASCADE;

-- ============================================
-- DROP BASE/CATALOG TABLES
-- ============================================

DROP TABLE IF EXISTS project_state CASCADE;
DROP TABLE IF EXISTS indicator_type CASCADE;
DROP TABLE IF EXISTS kpa CASCADE;
DROP TABLE IF EXISTS country CASCADE;
DROP TABLE IF EXISTS agency CASCADE;
DROP TABLE IF EXISTS sdg CASCADE;
DROP TABLE IF EXISTS beneficiary CASCADE;
DROP TABLE IF EXISTS donor CASCADE;
DROP TABLE IF EXISTS program_state CASCADE;
DROP TABLE IF EXISTS currency CASCADE;
DROP TABLE IF EXISTS user_state CASCADE;
DROP TABLE IF EXISTS role CASCADE;

-- ============================================
-- DROP ALL SEQUENCES
-- ============================================

DROP SEQUENCE IF EXISTS role_seq CASCADE;
DROP SEQUENCE IF EXISTS user_state_seq CASCADE;
DROP SEQUENCE IF EXISTS user_seq CASCADE;
DROP SEQUENCE IF EXISTS currency_seq CASCADE;
DROP SEQUENCE IF EXISTS donor_seq CASCADE;
DROP SEQUENCE IF EXISTS beneficiary_seq CASCADE;
DROP SEQUENCE IF EXISTS program_state_seq CASCADE;
DROP SEQUENCE IF EXISTS sdg_seq CASCADE;
DROP SEQUENCE IF EXISTS agency_seq CASCADE;
DROP SEQUENCE IF EXISTS country_seq CASCADE;
DROP SEQUENCE IF EXISTS kpa_seq CASCADE;
DROP SEQUENCE IF EXISTS country_kpa_seq CASCADE;
DROP SEQUENCE IF EXISTS strategic_output_seq CASCADE;
DROP SEQUENCE IF EXISTS measure_seq CASCADE;
DROP SEQUENCE IF EXISTS indicator_type_seq CASCADE;
DROP SEQUENCE IF EXISTS indicator_seq CASCADE;
DROP SEQUENCE IF EXISTS contact_seq CASCADE;
DROP SEQUENCE IF EXISTS program_seq CASCADE;
DROP SEQUENCE IF EXISTS project_state_seq CASCADE;
DROP SEQUENCE IF EXISTS project_seq CASCADE;
DROP SEQUENCE IF EXISTS project_agency_seq CASCADE;
DROP SEQUENCE IF EXISTS project_indicator_seq CASCADE;
DROP SEQUENCE IF EXISTS user_role_seq CASCADE;
DROP SEQUENCE IF EXISTS country_kpa_user_seq CASCADE;
DROP SEQUENCE IF EXISTS program_user_seq CASCADE;
DROP SEQUENCE IF EXISTS program_sdg_seq CASCADE;
DROP SEQUENCE IF EXISTS program_donor_seq CASCADE;

-- ============================================
-- VERIFICATION MESSAGE
-- ============================================
-- All tables and sequences have been dropped successfully
-- Database is now clean and ready for fresh schema installation
