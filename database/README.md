# Database Setup Instructions

## For InfinityFree Deployment

### Step 1: Create Database
1. Login to InfinityFree Control Panel
2. Go to **MySQL Databases**
3. Click **Create Database**
4. Note the credentials:
   - **Host:** `sqlXXX.infinityfree.com`
   - **Database Name:** `if0_XXXXXXXX_blotter_system`
   - **Username:** `if0_XXXXXXXX`
   - **Password:** [your password]

### Step 2: Import Database
1. In Control Panel, go to **MySQL Databases**
2. Click **phpMyAdmin** next to your database
3. Select your database from the left sidebar
4. Click the **Import** tab
5. Click **Choose File** and select `schema.sql`
6. Scroll down and click **Go**
7. Wait for import to complete

### Step 3: Verify Import
After import, you should see these tables:
- `blotters`
- `residents`
- `users`

### Step 4: Update .env
Update your `.env` file with the database credentials from Step 1:

```env
DB_HOST=sqlXXX.infinityfree.com
DB_NAME=if0_XXXXXXXX_blotter_system
DB_USERNAME=if0_XXXXXXXX
DB_PASSWORD=your_password
```

---

## For Local Development

### Using Command Line

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS Blotter_System;"

# Import schema
mysql -u root -p Blotter_System < database/schema.sql
```

### Using phpMyAdmin

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create new database: `Blotter_System`
3. Select the database
4. Click **Import** tab
5. Choose `database/schema.sql`
6. Click **Go**

### Verify Local Setup

```bash
# Check if database exists and has tables
mysql -u root -p -e "USE Blotter_System; SHOW TABLES;"
```

---

## Database Schema Overview

### Tables

#### `users`
Stores user authentication information
- `id` - Primary key
- `username` - Login username
- `password` - Hashed password
- `email` - User email (optional)
- `created_at` - Registration timestamp

#### `residents`
Stores resident information
- `id` - Primary key
- `first_name` - Resident's first name
- `middle_name` - Resident's middle name
- `last_name` - Resident's last name
- `suffix` - Name suffix (Jr., Sr., etc.)
- `date_of_birth` - Birth date
- `gender` - Gender
- `civil_status` - Marital status
- `address` - Residential address
- `contact_number` - Phone number
- `email` - Email address
- `created_at` - Record creation timestamp

#### `blotters`
Stores blotter/incident records
- `id` - Primary key
- `case_number` - Unique case identifier
- `complainant_id` - Foreign key to residents
- `respondent_id` - Foreign key to residents
- `incident_type` - Type of incident
- `incident_date` - Date of incident
- `incident_time` - Time of incident
- `incident_location` - Location of incident
- `narrative` - Detailed description
- `status` - Case status (pending/resolved/dismissed)
- `created_by` - User who created record
- `created_at` - Record creation timestamp
- `updated_at` - Last update timestamp

---

## Troubleshooting

### Import Failed
**Error:** "Unknown collation: 'utf8mb4_0900_ai_ci'"
**Solution:** Edit `schema.sql` and change collation to `utf8mb4_unicode_ci` or `utf8mb4_general_ci`

### Connection Refused
**Problem:** Can't connect to database
**Solution:** 
- Check database credentials in `.env`
- Verify database service is running
- For InfinityFree: Use full hostname `sqlXXX.infinityfree.com`

### Tables Not Created
**Problem:** Import succeeded but tables don't exist
**Solution:**
- Check phpMyAdmin error messages
- Verify SQL file is not corrupted
- Re-export from local database

### Character Encoding Issues
**Problem:** Special characters display incorrectly
**Solution:**
- Ensure database uses `utf8mb4` charset
- Set charset in database.php connection
- Update table collation if needed

---

## Backup & Restore

### Backup (Export)

**Local:**
```bash
mysqldump -u root -p Blotter_System > backup_$(date +%Y%m%d).sql
```

**InfinityFree:**
- Use phpMyAdmin → Export tab
- Select all tables
- Choose SQL format
- Click **Go**

### Restore (Import)

**Local:**
```bash
mysql -u root -p Blotter_System < backup_20231214.sql
```

**InfinityFree:**
- Use phpMyAdmin → Import tab
- Choose backup file
- Click **Go**

---

## Migration Notes

When migrating from local to production:

1. **Export clean data:**
   ```bash
   mysqldump -u root -p Blotter_System > production_ready.sql
   ```

2. **Remove test data (optional):**
   - Edit SQL file
   - Remove INSERT statements for test records
   - Keep table structure

3. **Update AUTO_INCREMENT (optional):**
   - Ensure IDs start from 1
   - Useful for clean production start

---

## Default Admin Account

After import, you may need to create an admin account:

1. Register via the registration page
2. Or insert directly into database:

```sql
INSERT INTO users (username, password, email, created_at) 
VALUES ('admin', '[hashed_password]', 'admin@example.com', NOW());
```

**Note:** Password should be hashed using PHP's `password_hash()` function.

---

## Database Maintenance

### Regular Tasks

**Weekly:**
- Backup database
- Check for orphaned records

**Monthly:**
- Optimize tables
- Review and archive old records

**Optimize Tables:**
```sql
OPTIMIZE TABLE users, residents, blotters;
```

**Check Table Status:**
```sql
CHECK TABLE users, residents, blotters;
```

---

For more help, see [DEPLOYMENT.md](DEPLOYMENT.md)
