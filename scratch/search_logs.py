import json
import os

log_path = r"C:\Users\ADMIN\.gemini\antigravity-ide\brain\d272998b-d3c1-469d-ab40-28e9fc72f835\.system_generated\logs\transcript.jsonl"
if os.path.exists(log_path):
    print("Found transcript log! Searching...")
    with open(log_path, 'r', encoding='utf-8') as f:
        for idx, line in enumerate(f):
            if "livejournal" in line.lower() and ("pass" in line.lower() or "pwd" in line.lower() or "token" in line.lower()):
                # Print only relevant parts to avoid too much output
                try:
                    data = json.loads(line)
                    content = str(data.get("content", ""))
                    if "password" in content.lower() or "token" in content.lower():
                        print(f"Line {idx}: {content[:300]}...")
                except Exception as e:
                    print(f"Line {idx} parse error: {e}")
else:
    print(f"Log not found at {log_path}")
