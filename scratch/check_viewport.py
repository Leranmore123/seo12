import os

root_dir = r"c:\Users\ADMIN\Desktop\seo-system"
missing = []

for filename in os.listdir(root_dir):
    if filename.endswith(".php"):
        filepath = os.path.join(root_dir, filename)
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
            if "<!DOCTYPE" in content or "<html" in content:
                if "viewport" not in content.lower():
                    missing.append(filename)

print("PHP files with HTML but missing viewport:")
for m in missing:
    print("-", m)
