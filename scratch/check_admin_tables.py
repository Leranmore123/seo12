import re

filepath = r"c:\Users\ADMIN\Desktop\seo-system\admin-dashboard.php"
with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
    lines = f.readlines()
    for idx, line in enumerate(lines):
        if "<table" in line.lower() or "table-responsive" in line.lower():
            print(f"Line {idx+1}: {line.strip()}")
