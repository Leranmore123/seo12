import sys
import os
import time
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.by import By
from webdriver_manager.chrome import ChromeDriverManager

username = "LMT_12"
password = "@Pratik12@"

opts = Options()
opts.add_argument('--headless=new')
opts.add_argument('--no-sandbox')
opts.add_argument('--disable-dev-shm-usage')
opts.add_argument('--window-size=1400,900')

driver = webdriver.Chrome(service=Service(ChromeDriverManager().install()), options=opts)

try:
    print("Navigating to login page...")
    driver.get("https://www.livejournal.com/login.bml?returnto=https://m.livejournal.com")
    time.sleep(3)
    
    # Simple login
    print("Attempting login...")
    user_el = driver.find_element(By.CSS_SELECTOR, "input[name='user']")
    pass_el = driver.find_element(By.CSS_SELECTOR, "input[name='password']")
    user_el.send_keys(username)
    pass_el.send_keys(password)
    
    # Submit button
    submit_btn = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
    submit_btn.click()
    time.sleep(5)
    
    print("Navigating to mobile post editor...")
    driver.get("https://m.livejournal.com/post")
    time.sleep(5)
    
    print(f"Current URL: {driver.current_url}")
    
    # Find inputs and log them
    print("\n--- Listing form inputs ---")
    inputs = driver.find_elements(By.CSS_SELECTOR, "input, textarea, button")
    for idx, el in enumerate(inputs):
        try:
            print(f"{idx}: Tag={el.tag_name}, ID={el.get_attribute('id')}, Name={el.get_attribute('name')}, Class={el.get_attribute('class')}, Text={el.text.strip()}")
        except:
            pass
            
except Exception as e:
    print(f"Error occurred: {e}")
finally:
    try:
        os.makedirs("scratch", exist_ok=True)
        driver.save_screenshot("scratch/lj_mobile_editor.png")
        print("Screenshot saved to scratch/lj_mobile_editor.png")
    except:
        pass
    driver.quit()
