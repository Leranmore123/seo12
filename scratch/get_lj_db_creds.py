import pymysql
import json

try:
    conn = pymysql.connect(
        host="127.0.0.1",
        port=3307,
        user="root",
        password="",
        database="seo_system",
        cursorclass=pymysql.cursors.DictCursor
    )
    with conn.cursor() as cursor:
        cursor.execute("SELECT username, password FROM social_accounts WHERE platform = 'livejournal'")
        accounts = cursor.fetchall()
        import base64
        for acc in accounts:
            try:
                pw_decoded = base64.b64decode(acc['password']).decode('utf-8')
            except Exception:
                pw_decoded = acc['password']
            print(f"Username: {acc['username']}, Password: {pw_decoded}")
except Exception as e:
    print(f"Database error: {e}")
