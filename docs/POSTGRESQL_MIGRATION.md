# PostgreSQL Migration Guide

## Overview

This project has been successfully migrated from MySQL 8.0 to PostgreSQL 16. This guide explains the changes made and how to use the updated system.

## What Changed

### 1. Database Configuration
- **Default Connection**: Changed from `mysql` to `pgsql`
- **Default Port**: Changed from `3306` to `5432`
- **Default Host**: Changed from `mysql` to `postgres`
- **Configuration File**: `config/database.php` updated

### 2. Docker Compose
- MySQL service replaced with PostgreSQL 16 (Alpine)
- Volume name changed: `mysql_data` → `postgres_data`
- Health check uses `pg_isready` instead of `mysqladmin ping`
- Init script updated: `docker/postgres/init.sql`

### 3. Environment Variables
Updated `.env`, `.env.example`, and `.env.prod`:
```
DB_CONNECTION=pgsql
DB_HOST=postgres          # (or your-db-host.render.com for Render)
DB_PORT=5432
DB_DATABASE=quiz
DB_USERNAME=quiz_user
DB_PASSWORD=your_password
```

### 4. PHP Extensions
Added PostgreSQL support:
- `pdo_pgsql`: PostgreSQL database driver
- `postgresql-client`: CLI tools for database operations

### 5. Documentation
Updated all deployment and troubleshooting guides to reference PostgreSQL.

## Migration Steps for Existing Data

If you had data in MySQL, follow these steps to migrate:

### Step 1: Export MySQL Data

```bash
# If MySQL is still running
docker-compose exec -T mysql mysqldump -u root -p${DB_ROOT_PASSWORD:-root} ${DB_DATABASE:-quiz} > mysql_backup.sql
```

### Step 2: Convert MySQL Dump to PostgreSQL Format

Use an online converter or the `mysql2pgsql` tool. For most Laravel projects, the Laravel schema builder handles this automatically.

### Step 3: Rebuild with PostgreSQL

```bash
# Stop old containers
docker-compose down

# Update .env to use PostgreSQL (if not already done)
# Edit .env and ensure DB_CONNECTION=pgsql

# Start fresh with PostgreSQL
docker-compose up -d --build

# Wait for PostgreSQL to be ready
docker-compose logs -f postgres

# Run migrations (this recreates tables)
docker-compose exec -T php php artisan migrate --force
```

### Step 4: Restore Data (Optional)

If you need to restore data:

```bash
# Create a temporary PostgreSQL dump import script
cat > import_data.sql << 'EOF'
-- Your converted SQL here
EOF

# Import
docker-compose exec -T postgres psql -U quiz_user -d quiz < import_data.sql
```

## Key Differences: MySQL vs PostgreSQL

| Feature | MySQL | PostgreSQL |
|---------|-------|-----------|
| Port | 3306 | 5432 |
| Health Check | `mysqladmin ping` | `pg_isready` |
| Dump | `mysqldump` | `pg_dump` |
| Restore | `mysql` CLI | `psql` CLI |
| ENUM Support | Native | Native (compatible) |
| JSON Support | Native | Native (better performance) |
| Full-Text Search | Limited | Excellent |
| Transactions | Yes | ACID compliant |

## Advantages of PostgreSQL for Render Deployment

1. **Native Render Support**: Render provides managed PostgreSQL databases
2. **Better Performance**: Superior query optimization for complex queries
3. **ACID Compliance**: Stronger data integrity guarantees
4. **JSON Operators**: Better JSON querying capabilities
5. **Free Tier**: Render offers free PostgreSQL databases
6. **Scalability**: PostgreSQL handles scale-out better

## Testing the Migration

### Verify Database Connection

```bash
# Check if PostgreSQL is running
docker-compose ps

# Test connection
docker-compose exec -T postgres psql -U quiz_user -d quiz -c "SELECT 1;"

# Should output: 1
```

### Test Health Endpoint

```bash
curl http://localhost:8000/api/health
curl http://localhost:8000/api/status
```

### Run Tests

```bash
docker-compose exec -T php php artisan test
docker-compose exec -T php ./vendor/bin/pest
```

## Backup and Restore with PostgreSQL

### Backup

```bash
docker-compose exec -T postgres pg_dump -U quiz_user -d quiz > quiz_backup.sql
```

### Restore

```bash
docker-compose exec -T postgres psql -U quiz_user -d quiz < quiz_backup.sql
```

### Backup with Compression

```bash
docker-compose exec -T postgres pg_dump -U quiz_user -d quiz | gzip > quiz_backup.sql.gz

# Restore from compressed backup
gunzip < quiz_backup.sql.gz | docker-compose exec -T postgres psql -U quiz_user -d quiz
```

## Deployment to Render

When deploying to Render:

1. Create a PostgreSQL database on Render
2. Copy the connection string from Render dashboard
3. Add to environment variables:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=your-database-url.render.com
   DB_PORT=5432
   DB_DATABASE=quiz
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```
4. Deploy your web service
5. Migrations run automatically during build
6. Verify with `/api/health` endpoint

## Rollback to MySQL (If Needed)

To revert to MySQL:

1. Restore `config/database.php` to use `mysql` connection
2. Update docker-compose to use MySQL service
3. Restore MySQL data from backups
4. Rebuild containers

**Note**: We recommend staying with PostgreSQL for production deployments.

## Performance Tips for PostgreSQL

1. **Enable Connection Pooling**: Use PgBouncer for better resource usage
2. **Monitor Query Performance**: Use `EXPLAIN ANALYZE` for slow queries
3. **Proper Indexing**: Leverage PostgreSQL's advanced indexing (B-tree, BRIN, GiST)
4. **Statistics**: Keep table statistics updated with `ANALYZE`
5. **Caching**: Use Redis for application-level caching

## Common Issues and Solutions

### Issue: `FATAL: remaining connection slots are reserved`

**Solution**: Increase max connections or enable connection pooling

### Issue: `ERROR: type "enum" does not exist`

**Solution**: Ensure migrations are run with proper error handling

### Issue: Slow queries after migration

**Solution**: Run `ANALYZE` on tables:
```bash
docker-compose exec -T postgres psql -U quiz_user -d quiz -c "ANALYZE;"
```

## Support and Documentation

- [PostgreSQL Official Docs](https://www.postgresql.org/docs/)
- [Laravel PostgreSQL Support](https://laravel.com/docs/11.x/database)
- [Render PostgreSQL Docs](https://render.com/docs/postgresql)
