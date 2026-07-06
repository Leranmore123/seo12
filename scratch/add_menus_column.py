import MySQLdb

try:
    connection = MySQLdb.connect(
        host='127.0.0.1',
        port=3307,
        user='root',
        passwd='',
        db='seo_system'
    )
    print("Database connected successfully!")
    with connection.cursor() as cursor:
        # Check if column already exists
        cursor.execute("SHOW COLUMNS FROM users LIKE 'allowed_menus'")
        result = cursor.fetchone()
        if result:
            print("Column 'allowed_menus' already exists.")
        else:
            cursor.execute("ALTER TABLE users ADD COLUMN allowed_menus TEXT DEFAULT NULL")
            connection.commit()
            print("Column 'allowed_menus' added successfully to 'users' table!")
except Exception as e:
    print("Error:", e)
