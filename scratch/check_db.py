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
        cursor.execute("SELECT id, username, email FROM users")
        rows = cursor.fetchall()
        for row in rows:
            print("User Row:", row)
            
        cursor.execute("SELECT id, user_id, platform, username, LENGTH(password) FROM social_accounts WHERE platform='tumblr'")
        rows = cursor.fetchall()
        for row in rows:
            print("Tumblr Row:", row)
except Exception as e:
    print("Error:", e)
