import os

root_dir = r"c:\Users\ADMIN\Desktop\seo-system"
for dirpath, _, filenames in os.walk(root_dir):
    for filename in filenames:
        if filename.endswith(".php"):
            filepath = os.path.join(dirpath, filename)
            with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
                if "runPlatformAutoPost" in content or "site123" in content:
                    print(f"File: {os.path.relpath(filepath, root_dir)}")
                    lines = content.splitlines()
                    for idx, line in enumerate(lines):
                        if "runPlatformAutoPost" in line or "site123" in line:
                            print(f"  Line {idx+1}: {line.strip()}")
