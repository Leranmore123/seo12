import sys
import os
import time
import urllib.request
import urllib.parse
import json
import xml.etree.ElementTree as ET
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager

username = "skyranksolution"
password = "@Pratik12@"

def api_login(user, pwd):
    # Challenge-response API login (same as livejournal_post.py)
    try:
        import urllib.request
        import urllib.parse
        import hashlib
        
        # Step 1: get challenge
        req = urllib.request.Request("https://www.livejournal.com/interface/flat", 
            data=b"mode=getchallenge",
            headers={"User-Agent": "Mozilla/5.0"}
        )
        with urllib.request.urlopen(req) as resp:
            text = resp.read().decode('utf-8')
        lines = [line.strip() for line in text.split("\n") if line.strip()]
        data = {}
        for i in range(0, len(lines) - 1, 2):
            data[lines[i]] = lines[i+1]
        chal = data.get("challenge")
        
        if not chal:
            return None, "No challenge returned"
            
        # Step 2: solve challenge
        pw_hash = hashlib.md5(pwd.encode('utf-8')).hexdigest()
        auth_response = hashlib.md5((chal + pw_hash).encode('utf-8')).hexdigest()
        
        body = urllib.parse.urlencode({
            "mode": "sessiongenerate",
            "user": user,
            "auth_method": "challenge",
            "auth_challenge": chal,
            "auth_response": auth_response
        }).encode('utf-8')
        
        req2 = urllib.request.Request("https://www.livejournal.com/interface/flat", 
            data=body,
            headers={"User-Agent": "Mozilla/5.0"}
        )
        with urllib.request.urlopen(req2) as resp2:
            text2 = resp2.read().decode('utf-8')
        lines2 = [line.strip() for line in text2.split("\n") if line.strip()]
        data2 = {}
        for i in range(0, len(lines2) - 1, 2):
            data2[lines2[i]] = lines2[i+1]
            
        ljsession = data2.get("ljsession")
        if ljsession:
            return ljsession, None
        return None, f"Session generation failed: {data2}"
    except Exception as e:
        return None, str(e)

ljsession_val, err = api_login(username, password)
if err:
    print(f"API login failed: {err}")
    sys.exit(1)

print(f"API login success! Token: {ljsession_val[:10]}...")

opts = Options()
opts.add_argument('--headless=new')
opts.add_argument('--no-sandbox')
opts.add_argument('--disable-dev-shm-usage')
opts.add_argument('--window-size=1400,900')

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=opts)

try:
    print("Navigating to robots.txt to establish domain...")
    driver.get("https://www.livejournal.com/robots.txt")
    time.sleep(2)
    
    print("Injecting session cookie...")
    driver.add_cookie({
        "name": "ljsession",
        "value": ljsession_val,
        "domain": ".livejournal.com",
        "path": "/"
    })
    
    print("Navigating to mobile editor...")
    driver.get("https://m.livejournal.com/post")
    time.sleep(4)
    
    print(f"Current URL: {driver.current_url}")
    
    # Check if page is redirected to login (means session cookie didn't work)
    if "login" in driver.current_url.lower():
        print("Redirected to login. Session cookie was not accepted on mobile.")
        driver.save_screenshot("scratch/lj_mobile_cookie_fail.png")
        sys.exit(1)
        
    print("\n--- Listing mobile editor elements ---")
    inputs = driver.find_elements(By.CSS_SELECTOR, "input, textarea, button, form")
    for idx, el in enumerate(inputs):
        try:
            print(f"{idx}: Tag={el.tag_name}, ID={el.get_attribute('id')}, Name={el.get_attribute('name')}, Class={el.get_attribute('class')}, Text={el.text.strip()}")
        except:
            pass
            
    # Save a screenshot to verify
    os.makedirs("scratch", exist_ok=True)
    driver.save_screenshot("scratch/lj_mobile_editor.png")
    print("Screenshot saved to scratch/lj_mobile_editor.png")
    
except Exception as e:
    print(f"Error occurred: {e}")
finally:
    driver.quit()
