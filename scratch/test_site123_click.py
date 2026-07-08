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

opts = Options()
opts.add_argument('--disable-blink-features=AutomationControlled')
opts.add_experimental_option('excludeSwitches', ['enable-automation'])
opts.add_experimental_option('useAutomationExtension', False)
opts.add_argument('--window-size=1400,900')

service = Service(ChromeDriverManager().install())
driver = webdriver.Chrome(service=service, options=opts)
driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")
wait = WebDriverWait(driver, 15)

try:
    print("Logging in to Site123...")
    driver.get("https://app.site123.com/manager/login/login.php?l=en")
    time.sleep(4)
    
    # Fill email
    driver.find_element(By.CSS_SELECTOR, "input[name='username']").send_keys(email)
    # Fill password
    driver.find_element(By.CSS_SELECTOR, "input[type='password']").send_keys(password)
    # Submit
    driver.find_element(By.CSS_SELECTOR, "button[type='submit']").click()
    
    time.sleep(8)
    print("Page URL:", driver.current_url)

    # Let's inspect the page
    print("Iframes found on page:")
    iframes = driver.find_elements(By.TAG_NAME, "iframe")
    for idx, iframe in enumerate(iframes):
        print(f"- Iframe {idx}: ID={iframe.get_attribute('id')}, Class={iframe.get_attribute('class')}, Name={iframe.get_attribute('name')}")
        
    # Check if there is an iframe and switch to it
    if iframes:
        driver.switch_to.frame(iframes[0])
        print("Switched to first iframe.")
        
    # Dump all links with class or text on the page
    print("Links on page:")
    links = driver.find_elements(By.TAG_NAME, "a")
    for link in links[:30]: # Print first 30 links
        try:
            text = link.text.strip()
            href = link.get_attribute('href')
            cls = link.get_attribute('class')
            print(f"- Text: '{text}', Href: '{href[:60]}', Class: '{cls}'")
        except:
            pass

    # Dump all buttons
    print("Buttons on page:")
    buttons = driver.find_elements(By.TAG_NAME, "button")
    for btn in buttons[:20]:
        try:
            text = btn.text.strip()
            cls = btn.get_attribute('class')
            print(f"- Button: '{text}', Class: '{cls}'")
        except:
            pass

    # Let's try to click on the website card to open it
    # We want to edit the website, so let's find the edit button
    edit_buttons = driver.find_elements(By.XPATH, "//*[contains(text(), 'Edit') or contains(text(), 'Edit Website') or contains(text(), 'Dashboard')]")
    print(f"Edit/Dashboard elements found: {len(edit_buttons)}")
    for idx, eb in enumerate(edit_buttons):
        try:
            print(f"- Element {idx}: Tag={eb.tag_name}, Class={eb.get_attribute('class')}, Text='{eb.text.strip()}'")
        except:
            pass

except Exception as e:
    print("Error:", e)
finally:
    driver.quit()
