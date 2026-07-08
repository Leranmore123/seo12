import sys, time, os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

email = "kanzariyapratik124@gmail.com"
password = "@DISHA12@"

SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
output_dir = os.path.join(SCRIPT_DIR, "site123_debug")
os.makedirs(output_dir, exist_ok=True)

print(f"Testing Site123 for {email}...")

opts = Options()
# Run in non-headless mode locally first to mimic a real user session and capture screens
opts.add_argument('--disable-blink-features=AutomationControlled')
opts.add_experimental_option('excludeSwitches', ['enable-automation'])
opts.add_experimental_option('useAutomationExtension', False)
opts.add_argument('--window-size=1400,900')

service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service, options=opts)
driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")
wait = WebDriverWait(driver, 15)

try:
    print("Navigating to login page...")
    driver.get("https://app.site123.com/manager/login/login.php?l=en")
    time.sleep(6)
    driver.save_screenshot(os.path.join(output_dir, '1_login_page.png'))

    print("Filling credentials...")
    username_el = None
    for sel in ["input[name='username']", "input[id='username']", "input[type='email']"]:
        try:
            username_el = driver.find_element(By.CSS_SELECTOR, sel)
            break
        except:
            continue
            
    if username_el:
        username_el.clear()
        username_el.send_keys(email)
        print("Filled email.")
    else:
        print("Could not find email field.")

    password_el = None
    for sel in ["input[type='password']", "input[name='password']"]:
        try:
            password_el = driver.find_element(By.CSS_SELECTOR, sel)
            break
        except:
            continue

    if password_el:
        password_el.clear()
        password_el.send_keys(password)
        print("Filled password.")
    else:
        print("Could not find password field.")

    driver.save_screenshot(os.path.join(output_dir, '2_credentials_filled.png'))

    # Click login button
    login_btn = None
    for sel in [
        "//button[normalize-space(text())='Login']",
        "button[type='submit']",
        "//input[@value='Login']",
    ]:
        try:
            by = By.XPATH if sel.startswith('//') else By.CSS_SELECTOR
            login_btn = driver.find_element(by, sel)
            break
        except:
            continue

    if login_btn:
        driver.execute_script("arguments[0].click();", login_btn)
        print("Clicked login button.")
    else:
        print("Could not find login button.")

    time.sleep(8)
    driver.save_screenshot(os.path.join(output_dir, '3_after_login_attempt.png'))
    print(f"Current URL: {driver.current_url}")

    # Check page source for errors
    src = driver.page_source.lower()
    if "login" in driver.current_url.lower() or "error" in src:
        err_els = driver.find_elements(By.CSS_SELECTOR, "[class*='error'],[class*='alert'],[class*='warning']")
        err_msg = " | ".join([e.text.strip() for e in err_els if e.text.strip()])
        print(f"Login failed! Error visible on page: {err_msg}")
    else:
        print("Login SUCCESS or redirected to dashboard!")

except Exception as e:
    print(f"Error during test: {e}")
finally:
    driver.quit()
    print("Test finished.")
