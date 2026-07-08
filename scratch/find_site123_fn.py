filepath = r"c:\Users\ADMIN\Desktop\seo-system\auto-poster.php"
with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
    content = f.read()
    lines = content.splitlines()
    for idx, line in enumerate(lines):
        if "seleniumSite123" in line:
            print(f"Line {idx+1}: {line}")
