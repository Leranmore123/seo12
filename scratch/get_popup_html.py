import sys
import os
import time
import requests
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from webdriver_manager.chrome import ChromeDriverManager

username = "LMT_12"
password = "@Pratik12@"

def api_login(user, pwd):
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    })
    challenge_url = "https://www.livejournal.com/interface/flat"
    try:
        r = session.post(challenge_url, data={"mode": "getchallenge"}, timeout=30)
        lines = [line.strip() for line in r.text.split("\n") if line.strip()]
        data = {}
        for i in range(0, len(lines) - 1, 2):
            data[lines[i]] = lines[i+1]
        chal = data.get("challenge")
        
        import hashlib
        pw_hash = hashlib.md5(pwd.encode('utf-8')).hexdigest()
        auth_response = hashlib.md5((chal + pw_hash).encode('utf-8')).hexdigest()
        
        r2 = session.post(challenge_url, data={
            "mode": "sessiongenerate",
            "user": user,
            "auth_method": "challenge",
            "auth_challenge": chal,
            "auth_response": auth_response,
            "clientversion": "Python-Autopost"
        }, timeout=30)
        lines2 = [line.strip() for line in r2.text.split("\n") if line.strip()]
        data2 = {}
        for i in range(0, len(lines2) - 1, 2):
            data2[lines2[i]] = lines2[i+1]
            
        if data2.get("success") == "OK":
            return data2.get("ljsession"), None
        return None, data2.get("errmsg", "Unknown error")
    except Exception as e:
        return None, str(e)

ljsession_val, err = api_login(username, password)
if err:
    print(f"API Login Failed: {err}")
    sys.exit(1)

opts = Options()
opts.add_argument('--headless=new')
opts.add_argument('--no-sandbox')
opts.add_argument('--disable-dev-shm-usage')
opts.add_argument('--window-size=1400,900')

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=opts)

try:
    driver.get("https://m.livejournal.com/")
    time.sleep(3)
    driver.add_cookie({
        "name": "ljsession",
        "value": ljsession_val,
        "domain": ".livejournal.com",
        "path": "/"
    })
    driver.get("https://m.livejournal.com/")
    time.sleep(3)
    driver.get("https://www.livejournal.com/update.bml")
    time.sleep(8)
    
    # Click "Tune in and publish"
    clicked = driver.execute_script("""
        var btns = document.querySelectorAll('button');
        for (var i = 0; i < btns.length; i++) {
            var t = btns[i].innerText || btns[i].textContent || '';
            if (t.toLowerCase().indexOf('publish') !== -1 || t.toLowerCase().indexOf('tune') !== -1) {
                btns[i].click();
                return t;
            }
        }
        return null;
    """)
    print(f"First publish clicked: {clicked}")
    time.sleep(4)
    
    # Get HTML of confirm button
    confirm_btn = driver.find_element(By.CSS_SELECTOR, '.js--submit-post')
    print("\n--- Confirm Button Outer HTML ---")
    print(confirm_btn.get_attribute("outerHTML"))
    
except Exception as e:
    print(f"Error occurred: {e}")
finally:
    driver.quit()
