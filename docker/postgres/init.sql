-- PostgreSQL initial database setup
-- This script runs when PostgreSQL starts

-- Set default encoding
ALTER DATABASE quiz SET client_encoding = 'UTF8';
ALTER DATABASE quiz SET default_transaction_isolation = 'read committed';
ALTER DATABASE quiz SET default_transaction_deferrable = off;
ALTER DATABASE quiz SET default_transaction_read_only = off;
ALTER DATABASE quiz SET statement_timeout = 0;
ALTER DATABASE quiz SET idle_in_transaction_session_timeout = 0;
ALTER DATABASE quiz SET lock_timeout = 0;
ALTER DATABASE quiz SET timezone = 'UTC';

-- Create schema if not exists
CREATE SCHEMA IF NOT EXISTS public;
GRANT ALL PRIVILEGES ON SCHEMA public TO quiz_user;
