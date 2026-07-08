import os

root_dir = r"c:\Users\ADMIN\Desktop\seo-system"
for dirpath, _, filenames in os.walk(root_dir):
    for filename in filenames:
        if filename.endswith(".php"):
            filepath = os.path.join(dirpath, filename)
            with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
                if "seleniumSite123" in content:
                    lines = content.splitlines()
                    for idx, line in enumerate(lines):
                        if "seleniumSite123" in line:
                            print(f"File: {os.path.relpath(filepath, root_dir)} | Line {idx+1}: {line.strip()}")
