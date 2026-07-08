import urllib.request
import json
import re

# Read config.local.php
config_path = r"c:\Users\ADMIN\Desktop\seo-system\config.local.php"
openai_key = ""
gemini_key = ""

with open(config_path, 'r', encoding='utf-8') as f:
    content = f.read()
    m_oa = re.search(r"'OPENAI_API_KEY'\s*=>\s*'([^']+)'", content)
    if m_oa:
        openai_key = m_oa.group(1)
    m_gem = re.search(r"'GEMINI_API_KEY'\s*=>\s*'([^']+)'", content)
    if m_gem:
        gemini_key = m_gem.group(1)

print(f"OpenAI Key: {openai_key[:15]}... ({len(openai_key)} chars)")
print(f"Gemini Key: {gemini_key[:15]}... ({len(gemini_key)} chars)\n")

# Test OpenAI
if openai_key:
    print("Testing OpenAI API...")
    url = "https://api.openai.com/v1/chat/completions"
    headers = {
        "Authorization": f"Bearer {openai_key}",
        "Content-Type": "application/json"
    }
    data = {
        "model": "gpt-4o-mini",
        "messages": [{"role": "user", "content": "Hello"}],
        "max_tokens": 5
    }
    req = urllib.request.Request(url, data=json.dumps(data).encode('utf-8'), headers=headers)
    try:
        with urllib.request.urlopen(req) as resp:
            print("OpenAI Success:", resp.read().decode('utf-8'))
    except Exception as e:
        print("OpenAI Error:", e)
        if hasattr(e, 'read'):
            print("Details:", e.read().decode('utf-8'))

print("-" * 50)

# Test Gemini
if gemini_key:
    print("Testing Gemini API...")
    url = f"https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={gemini_key}"
    headers = {
        "Content-Type": "application/json"
    }
    data = {
        "contents": [{"parts": [{"text": "Hello"}]}]
    }
    req = urllib.request.Request(url, data=json.dumps(data).encode('utf-8'), headers=headers)
    try:
        with urllib.request.urlopen(req) as resp:
            print("Gemini Success:", resp.read().decode('utf-8'))
    except Exception as e:
        print("Gemini Error:", e)
        if hasattr(e, 'read'):
            print("Details:", e.read().decode('utf-8'))
