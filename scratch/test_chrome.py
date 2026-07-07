import sys
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

print("Starting chrome test...")
opts = Options()
opts.add_argument('--headless=new')
opts.add_argument('--disable-gpu')
opts.add_argument('--no-sandbox')
opts.add_argument('--disable-dev-shm-usage')
opts.add_argument('--disable-setuid-sandbox')
opts.add_argument('--disable-namespace-sandbox')

service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service, options=opts)
try:
    print("Opening example.com...")
    driver.get("https://example.com")
    print("Success! Page title:", driver.title)
    
    print("Opening tumblr login...")
    driver.get("https://www.tumblr.com/login")
    print("Success! Tumblr page title:", driver.title)
except Exception as e:
    print("Failed:", e)
finally:
    driver.quit()
