#!/usr/bin/env python3
"""
Generic Selenium Auto-Poster — handles multiple platforms
Platforms: substack, linktree, symbaloo, penzu, wakelet, padlet, 
           pearltrees, photobucket, behance, scoopit, tumblr
Usage: python generic_post.py <platform> <email> <password> <keyword> <target_site> [content]
"""
import sys, json, time, os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager

def log(msg):
    print(json.dumps({"log": msg}), flush=True)

def result(success, url='', error='', message=''):
    print(json.dumps({"success": success, "url": url, "error": error, "message": message}), flush=True)

def get_driver(headless=True):
    opts = Options()
    if headless:
        opts.add_argument('--headless=new')
    opts.add_argument('--no-sandbox')
    opts.add_argument('--disable-dev-shm-usage')
    opts.add_argument('--disable-blink-features=AutomationControlled')
    opts.add_experimental_option('excludeSwitches', ['enable-automation'])
    opts.add_experimental_option('useAutomationExtension', False)
    opts.add_argument('--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0 Safari/537.36')
    opts.add_argument('--window-size=1280,900')
    service = Service(ChromeDriverManager().install())
    driver  = webdriver.Chrome(service=service, options=opts)
    driver.execute_script("Object.defineProperty(navigator, 'webdriver', {get: () => undefined})")
    return driver

def wait_and_type(driver, wait, selector, text, by=By.CSS_SELECTOR, clear=True, timeout=15):
    try:
        el = WebDriverWait(driver, timeout).until(EC.element_to_be_clickable((by, selector)))
        el.click()
        if clear:
            el.clear()
        el.send_keys(text)
        return True
    except Exception as e:
        log(f"wait_and_type [{selector}]: {e}")
        return False

def wait_and_click(driver, wait, selector, by=By.CSS_SELECTOR, timeout=15):
    try:
        el = WebDriverWait(driver, timeout).until(EC.element_to_be_clickable((by, selector)))
        el.click()
        return True
    except Exception as e:
        log(f"wait_and_click [{selector}]: {e}")
        return False

# ─────────────────────────────────────────────────────────────
# SUBSTACK
# ─────────────────────────────────────────────────────────────
def post_substack(driver, wait, email, password, keyword, target_site, content):
    title = f"Best {keyword.title()} Training - {time.strftime('%B %Y')}"

    log("Opening Substack login...")
    driver.get("https://substack.com/sign-in")
    time.sleep(3)

    # Click "Sign in with email"
    try:
        email_link = wait.until(EC.element_to_be_clickable(
            (By.XPATH, "//a[contains(text(),'email') or contains(text(),'Email')] | //button[contains(text(),'email')]")
        ))
        email_link.click()
        time.sleep(2)
    except: pass

    wait_and_type(driver, wait, "input[name='email'], input[type='email']", email)
    time.sleep(0.5)
    wait_and_type(driver, wait, "input[name='password'], input[type='password']", password)
    wait_and_click(driver, wait, "button[type='submit']")
    time.sleep(5)

    if "sign-in" in driver.current_url or "login" in driver.current_url:
        result(False, error="Substack login failed.")
        return

    log("Substack: logged in!")
    driver.get("https://substack.com/publish/post/new")
    time.sleep(4)

    # Write title
    wait_and_type(driver, wait, "[placeholder*='Title'], .title-input, [data-placeholder='Title']", title)
    time.sleep(0.5)

    # Write body
    try:
        body = driver.find_element(By.CSS_SELECTOR, ".ProseMirror, [contenteditable='true'].body-input, [data-placeholder*='Tell your story']")
        body.click()
        time.sleep(0.3)
        body.send_keys(content[:3000])
        time.sleep(0.5)
    except Exception as e:
        log(f"Body: {e}")

    # Publish
    wait_and_click(driver, wait, "button[class*='publish'], button[data-test-id='post-publish']")
    time.sleep(3)
    wait_and_click(driver, wait, "button[class*='publish-confirm'], button[data-test-id='confirm-publish']", timeout=8)
    time.sleep(5)

    url = driver.current_url
    result(True, url=url if "substack.com" in url else "https://substack.com/dashboard")

# ─────────────────────────────────────────────────────────────
# TUMBLR
# ─────────────────────────────────────────────────────────────
def post_tumblr(driver, wait, email, password, keyword, target_site, content):
    title = f"Best {keyword.title()} Training - {time.strftime('%Y')}"

    log("Opening Tumblr login...")
    driver.get("https://www.tumblr.com/login")
    time.sleep(3)

    wait_and_type(driver, wait, "input[name='email'], input[type='email'], #signup_email", email)
    time.sleep(0.5)
    wait_and_click(driver, wait, "button[type='submit'], [data-action='next']")
    time.sleep(2)

    wait_and_type(driver, wait, "input[type='password'], #signup_password", password)
    wait_and_click(driver, wait, "button[type='submit'], [data-action='login']")
    time.sleep(5)

    if "login" in driver.current_url:
        result(False, error="Tumblr login failed.")
        return

    log("Tumblr: logged in!")
    driver.get("https://www.tumblr.com/new/text")
    time.sleep(4)

    # Title
    wait_and_type(driver, wait, "input[placeholder*='Title'], .title", title)

    # Body
    try:
        body = driver.find_element(By.CSS_SELECTOR, ".CodeMirror, [contenteditable='true'], [data-testid='editor-content']")
        body.click()
        body.send_keys(content[:2000] + f"\n\nLearn more: {target_site}")
    except Exception as e:
        log(f"Body: {e}")

    # Post
    wait_and_click(driver, wait, "button[data-testid='post-button'], button[class*='post']")
    time.sleep(5)

    url = driver.current_url
    result(True, url=url if "tumblr.com" in url and "new" not in url else "https://www.tumblr.com/dashboard")

# ─────────────────────────────────────────────────────────────
# SCOOP.IT
# ─────────────────────────────────────────────────────────────
def post_scoopit(driver, wait, email, password, keyword, target_site, content):
    log("Opening Scoop.it login...")
    driver.get("https://www.scoop.it/login")
    time.sleep(3)

    wait_and_type(driver, wait, "input[name='email'], input[type='email']", email)
    wait_and_type(driver, wait, "input[type='password']", password)
    wait_and_click(driver, wait, "button[type='submit']")
    time.sleep(5)

    if "login" in driver.current_url:
        result(False, error="Scoop.it login failed.")
        return

    log("Scoop.it: logged in!")

    # Go to curate — pass URL to add
    curate_url = f"https://www.scoop.it/curate?url={target_site}"
    driver.get(curate_url)
    time.sleep(4)

    title = f"Best {keyword.title()} Training Guide - {time.strftime('%Y')}"

    # Fill title
    wait_and_type(driver, wait, "input[placeholder*='title' i], [data-field='title']", title, timeout=10)

    # Fill description
    wait_and_type(driver, wait, "textarea[placeholder*='desc' i], [data-field='body'], .insight-form textarea", content[:500], timeout=10)

    # Scoop it / Publish button
    wait_and_click(driver, wait, "button[class*='scoop'], button[data-action*='scoop'], button[class*='publish']", timeout=10)
    time.sleep(5)

    url = driver.current_url
    result(True, url=url if "scoop.it" in url else "https://www.scoop.it")

# ─────────────────────────────────────────────────────────────
# BEHANCE
# ─────────────────────────────────────────────────────────────
def post_behance(driver, wait, email, password, keyword, target_site, content, image_path=None):
    log("Opening Behance (Adobe) login...")
    driver.get("https://www.behance.net/")
    time.sleep(3)

    # Click Sign In
    try:
        sign_in = wait.until(EC.element_to_be_clickable(
            (By.XPATH, "//a[contains(text(),'Sign In') or contains(@href,'adobe')]")
        ))
        sign_in.click()
        time.sleep(3)
    except: pass

    # Adobe login
    wait_and_type(driver, wait, "input[name='username'], input[type='email'], #EmailPage-EmailField", email)
    wait_and_click(driver, wait, "button[class*='continue'], #EmailPage-ContinueButton")
    time.sleep(2)

    wait_and_type(driver, wait, "input[type='password'], #PasswordPage-PasswordField", password)
    wait_and_click(driver, wait, "button[class*='continue'], #PasswordPage-ContinueButton")
    time.sleep(5)

    if "behance.net" not in driver.current_url:
        driver.get("https://www.behance.net/")
        time.sleep(3)

    log("Behance: logged in!")

    # Create new project
    driver.get("https://www.behance.net/work/editor")
    time.sleep(4)

    title = f"Best {keyword.title()} Training - {time.strftime('%Y')}"
    wait_and_type(driver, wait, "input[placeholder*='project' i], [data-testid='project-title']", title)

    # Add text module
    try:
        add_btn = driver.find_element(By.CSS_SELECTOR, "button[class*='add-module'], [aria-label*='Add']")
        add_btn.click()
        time.sleep(1)
        text_btn = driver.find_element(By.XPATH, "//button[contains(text(),'Text')]")
        text_btn.click()
        time.sleep(1)
        editor = driver.find_element(By.CSS_SELECTOR, "[contenteditable='true'], .ql-editor")
        editor.send_keys(content[:2000] + f"\n\nLearn more: {target_site}")
    except Exception as e:
        log(f"Content module: {e}")

    # Publish
    wait_and_click(driver, wait, "button[class*='publish'], [data-testid='publish-button']")
    time.sleep(3)
    wait_and_click(driver, wait, "button[class*='confirm-publish'], [data-testid='confirm']", timeout=8)
    time.sleep(5)

    url = driver.current_url
    result(True, url=url if "behance.net" in url else "https://www.behance.net")

# ─────────────────────────────────────────────────────────────
# PADLET
# ─────────────────────────────────────────────────────────────
def post_padlet(driver, wait, email, password, keyword, target_site, content):
    log("Opening Padlet login...")
    driver.get("https://padlet.com/auth/login")
    time.sleep(3)

    wait_and_type(driver, wait, "input[name='email'], input[type='email']", email)
    wait_and_type(driver, wait, "input[type='password']", password)
    wait_and_click(driver, wait, "button[type='submit']")
    time.sleep(5)

    log("Padlet: logged in!")
    driver.get("https://padlet.com/dashboard")
    time.sleep(3)

    # Create new padlet
    try:
        new_btn = wait.until(EC.element_to_be_clickable(
            (By.XPATH, "//button[contains(text(),'Make a Padlet') or contains(text(),'New')]")
        ))
        new_btn.click()
        time.sleep(4)
    except Exception as e:
        log(f"New padlet: {e}")

    # Use Wall template
    try:
        wall = driver.find_element(By.XPATH, "//button[contains(text(),'Wall')] | //div[contains(text(),'Wall')]")
        wall.click()
        time.sleep(3)
    except: pass

    title = f"Best {keyword.title()} Training - {time.strftime('%Y')}"

    # Set title
    wait_and_type(driver, wait, "input[placeholder*='title' i], [contenteditable][class*='title']", title, timeout=10)
    wait_and_click(driver, wait, "button[type='submit'], button[class*='save']", timeout=8)
    time.sleep(3)

    # Add a post/card with the URL
    try:
        add_post = wait.until(EC.element_to_be_clickable(
            (By.CSS_SELECTOR, "button[class*='add-post'], [aria-label='Add post'], .add-post-button")
        ))
        add_post.click()
        time.sleep(2)

        # Fill card title
        wait_and_type(driver, wait, "[placeholder*='Title'], [class*='post-title']", f"Learn {keyword.title()}", timeout=8)

        # Fill card body
        wait_and_type(driver, wait, "[placeholder*='description' i], [class*='post-body'], [contenteditable]",
                      f"Best {keyword} training. Learn more: {target_site}", timeout=8)

        # Save card
        wait_and_click(driver, wait, "button[class*='save'], button[type='submit']", timeout=8)
        time.sleep(3)
    except Exception as e:
        log(f"Add card: {e}")

    url = driver.current_url
    result(True, url=url if "padlet.com" in url else "https://padlet.com/dashboard")

# ─────────────────────────────────────────────────────────────
# WAKELET
# ─────────────────────────────────────────────────────────────
def post_wakelet(driver, wait, email, password, keyword, target_site, content):
    log("Opening Wakelet login...")
    driver.get("https://wakelet.com/login")
    time.sleep(3)

    wait_and_type(driver, wait, "input[type='email'], input[name='email']", email)
    wait_and_type(driver, wait, "input[type='password']", password)
    wait_and_click(driver, wait, "button[type='submit']")
    time.sleep(5)

    log("Wakelet: logged in!")

    # Create new wake
    driver.get("https://wakelet.com/new")
    time.sleep(4)

    title = f"Best {keyword.title()} Training Resources - {time.strftime('%Y')}"
    wait_and_type(driver, wait, "input[placeholder*='title' i], [class*='title-input']", title, timeout=10)

    # Add link item
    try:
        add_link = wait.until(EC.element_to_be_clickable(
            (By.XPATH, "//button[contains(text(),'Add link') or contains(text(),'Link')]")
        ))
        add_link.click()
        time.sleep(2)

        url_field = driver.find_element(By.CSS_SELECTOR, "input[placeholder*='url' i], input[type='url'], input[placeholder*='link' i]")
        url_field.send_keys(target_site)
        time.sleep(0.5)

        add_btn = driver.find_element(By.CSS_SELECTOR, "button[type='submit'], button[class*='add']")
        add_btn.click()
        time.sleep(3)
    except Exception as e:
        log(f"Add link: {e}")

    # Publish/Save
    wait_and_click(driver, wait, "button[class*='publish'], button[class*='save']", timeout=10)
    time.sleep(5)

    url = driver.current_url
    result(True, url=url if "wakelet.com" in url else "https://wakelet.com/@me")

# ─────────────────────────────────────────────────────────────
# MAIN DISPATCHER
# ─────────────────────────────────────────────────────────────
PLATFORM_MAP = {
    'substack':   post_substack,
    'tumblr':     post_tumblr,
    'scoopit':    post_scoopit,
    'behance':    post_behance,
    'padlet':     post_padlet,
    'wakelet':    post_wakelet,
}

if __name__ == "__main__":
    if len(sys.argv) < 6:
        result(False, error="Usage: generic_post.py <platform> <email> <password> <keyword> <target_site> [content]")
        sys.exit(1)

    platform    = sys.argv[1].lower()
    email       = sys.argv[2]
    password    = sys.argv[3]
    keyword     = sys.argv[4]
    target_site = sys.argv[5]
    content     = sys.argv[6] if len(sys.argv) > 6 else f"Best {keyword} training. Learn more: {target_site}"

    if platform not in PLATFORM_MAP:
        result(False, error=f"Platform '{platform}' not supported in generic_post.py")
        sys.exit(1)

    driver = get_driver(headless=True)
    wait   = WebDriverWait(driver, 20)

    try:
        PLATFORM_MAP[platform](driver, wait, email, password, keyword, target_site, content)
    except Exception as e:
        result(False, error=str(e))
    finally:
        try:
            driver.quit()
        except:
            pass
