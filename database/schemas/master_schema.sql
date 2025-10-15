
\echo 'Iniciando creación de esquema Pacific Ecommerce...'

-- Información de la base de datos
SELECT 'Conectado a base de datos: ' || current_database() || ' en servidor PostgreSQL ' || version();

\echo 'Creando entidades...'
\i 01_create_donors.sql

\echo 'Schema completado exitosamente!'
\echo 'Tablas creadas:'

-- Mostrar tablas creadas
SELECT 
    schemaname,
    tablename,
    tableowner
FROM pg_tables 
WHERE schemaname = 'public' 
    AND tablename IN ('donors')
ORDER BY tablename;