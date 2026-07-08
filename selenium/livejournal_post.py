#!/usr/bin/env python3
"""
LiveJournal Auto-Post via Selenium — Profile-based (like Pinterest)
Profile saved in chrome_profile_livejournal folder
Usage: python livejournal_post.py <username> <password> <keyword> <target_url>
"""
import sys, json, time, os, requests
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.common.action_chains import ActionChains
from webdriver_manager.chrome import ChromeDriverManager

def log(msg):
    print(json.dumps({"log": msg}), flush=True)

def result(success, url='', error=''):
    print(json.dumps({"success": success, "url": url, "error": error}), flush=True)

def api_login(username, password):
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
    })
    
    challenge = None
    last_err = ""
    challenge_url = "https://www.livejournal.com/interface/flat"
    
    # Try up to 3 times to get the challenge
    for attempt in range(3):
        try:
            r = session.post(challenge_url, data={"mode": "getchallenge"}, timeout=30)
            if r.status_code == 200:
                lines = [line.strip() for line in r.text.split("\n") if line.strip()]
                data = {}
                for i in range(0, len(lines) - 1, 2):
                    data[lines[i]] = lines[i+1]
                challenge = data.get("challenge")
                if challenge:
                    break
                else:
                    last_err = "No challenge string found in response"
            else:
                last_err = f"Get challenge failed with status {r.status_code}"
        except Exception as e:
            last_err = f"Failed to get challenge: {e}"
        time.sleep(2)
        
    if not challenge:
        return None, last_err
        
    import hashlib
    pw_hash = hashlib.md5(password.encode('utf-8')).hexdigest()
    auth_response = hashlib.md5((challenge + pw_hash).encode('utf-8')).hexdigest()
    
    ljsession = None
    # Try up to 3 times to generate session
    for attempt in range(3):
        try:
            r2 = session.post(challenge_url, data={
                "mode": "sessiongenerate",
                "user": username,
                "auth_method": "challenge",
                "auth_challenge": challenge,
                "auth_response": auth_response,
                "clientversion": "Python-Autopost"
            }, timeout=30)
            if r2.status_code == 200:
                lines2 = [line.strip() for line in r2.text.split("\n") if line.strip()]
                data2 = {}
                for i in range(0, len(lines2) - 1, 2):
                    data2[lines2[i]] = lines2[i+1]
                
                if data2.get("success") == "OK":
                    ljsession = data2.get("ljsession")
                    if ljsession:
                        break
                    else:
                        last_err = "No ljsession token returned in response"
                else:
                    last_err = f"Authentication failed: {data2.get('errmsg', 'Unknown error')}"
            else:
                last_err = f"Login failed with status {r2.status_code}"
        except Exception as e:
            last_err = f"Failed to submit login: {e}"
        time.sleep(2)
        
    if not ljsession:
        return None, last_err
        
    return ljsession, None

def type_stealth(driver, el, text):
    try:
        driver.execute_script("arguments[0].focus();", el)
        el.click()
        el.clear()
        time.sleep(0.2)
    except:
        pass
    for char in text:
        try:
            el.send_keys(char)
            time.sleep(0.05)
        except:
            pass
    driver.execute_script("""
        var el = arguments[0];
        var val = arguments[1];
        el.value = val;
        el.dispatchEvent(new Event('input', { bubbles: true }));
        el.dispatchEvent(new Event('change', { bubbles: true }));
        if (window.angular) {
            try {
                var ngEl = window.angular.element(el);
                var model = ngEl.controller('ngModel');
                if (model) {
                    model.$setViewValue(val);
                    model.$render();
                }
                var scope = ngEl.scope();
                if (scope) {
                    scope.$apply();
                }
            } catch(e) {
                console.error('AngularJS binding update failed:', e);
            }
        }
    """, el, text)
    time.sleep(0.2)

username   = sys.argv[1] if len(sys.argv) > 1 else "LMT_12"
import hashlib
email_hash = hashlib.md5(username.lower().encode('utf-8')).hexdigest() if username else "default"

SCRIPT_DIR  = os.path.dirname(os.path.abspath(__file__))
if sys.platform != "win32":
    import getpass
    sys_user = getpass.getuser().lower()
    PROFILE_DIR = os.path.join('/tmp', f'chrome_profile_livejournal_{email_hash}_{sys_user}')
else:
    PROFILE_DIR = os.path.join(SCRIPT_DIR, f'chrome_profile_livejournal_{email_hash}')

# Clean lock files (Pinterest-style)
for lf in [os.path.join(PROFILE_DIR,'Default','LOCK'),
           os.path.join(PROFILE_DIR,'SingletonLock')]:
    try:
        if os.path.exists(lf): os.remove(lf)
    except: pass
os.makedirs(PROFILE_DIR, exist_ok=True)
password   = sys.argv[2] if len(sys.argv) > 2 else "@Pratik12@"
keyword    = sys.argv[3] if len(sys.argv) > 3 else "python training bangalore"
target_url = sys.argv[4] if len(sys.argv) > 4 else "https://learnmoretech.in"
ai_title   = sys.argv[5] if len(sys.argv) > 5 else ""
image_path = sys.argv[6] if len(sys.argv) > 6 else ""
# argv[7] = path to temp file containing full content (avoids Windows 8191 char CLI limit)
_content_file = sys.argv[7] if len(sys.argv) > 7 else ""
if _content_file and os.path.exists(_content_file):
    try:
        with open(_content_file, 'r', encoding='utf-8') as _f:
            ai_content = _f.read()
        log(f"LiveJournal: Content loaded from file ({len(ai_content)} chars)")
    except Exception as e:
        log(f"LiveJournal: Could not read content file: {e}")
        ai_content = ""
else:
    ai_content = ""

# Remove duplicate consecutive words from keyword (e.g. "Training Training" → "Training")
def clean_keyword(kw):
    words = kw.split()
    result = []
    prev = ''
    for w in words:
        if w.lower() != prev.lower():
            result.append(w)
        prev = w
    return ' '.join(result)

keyword = clean_keyword(keyword)

# Pinterest-style driver — saved profile, no headless
def get_driver():
    opts = Options()
    if sys.platform != "win32":
        opts.add_argument('--headless=new')
        opts.add_argument('--disable-gpu')
        opts.add_argument('--disable-software-rasterizer')
    opts.add_argument('--no-sandbox')
    opts.add_argument('--disable-dev-shm-usage')
    opts.add_argument('--disable-blink-features=AutomationControlled')
    opts.add_experimental_option('excludeSwitches', ['enable-automation'])
    opts.add_experimental_option('useAutomationExtension', False)
    opts.add_argument('--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0 Safari/537.36')
    opts.add_argument('--window-size=1400,900')
    opts.add_argument(f'--user-data-dir={PROFILE_DIR}')  # Persistent profile
    
    # Disabling extensions for clean profile runs
    opts.add_argument('--disable-extensions')

    service = Service(ChromeDriverManager().install())
    driver  = webdriver.Chrome(service=service, options=opts)
    try:
        driver.execute_cdp_cmd("Page.addScriptToEvaluateOnNewDocument", {
            "source": """
                Object.defineProperty(navigator, 'webdriver', {get: () => undefined});
                Object.defineProperty(navigator, 'languages', {get: () => ['en-US', 'en']});
                Object.defineProperty(navigator, 'plugins', {get: () => [1, 2, 3, 4, 5]});
            """
        })
    except Exception as e:
        pass
    return driver

try:
    driver = get_driver()
    wait   = WebDriverWait(driver, 20)
    log("LiveJournal: Browser ready")

    # ── Step 1: Check login status and authenticate via API ──
    log("LiveJournal: Checking login...")
    driver.get("https://www.livejournal.com/")
    time.sleep(3)

    # Check if already logged in via saved cookies
    cookies = {c['name']: c['value'] for c in driver.get_cookies()}
    already_logged = 'ljsession' in cookies

    # Double-check: try going to update page
    if already_logged:
        driver.get("https://www.livejournal.com/update.bml")
        time.sleep(3)
        if "login" in driver.current_url.lower():
            already_logged = False
            log("LiveJournal: Not logged in (update.bml redirected to login)")
        else:
            log("LiveJournal: Already logged in — update.bml accessible!")

    if not already_logged:
        log("LiveJournal: Logging in via API challenge-response...")
        ljsession_val, err = api_login(username, password)
        if err:
            log(f"LiveJournal: API authentication failed: {err}")
            driver.save_screenshot(os.path.join(SCRIPT_DIR, 'livejournal_login_error.png'))
            result(False, error=f"LiveJournal: Login failed. {err}")
            driver.quit(); sys.exit(1)
            
        # Inject cookie
        driver.add_cookie({
            "name": "ljsession",
            "value": ljsession_val,
            "domain": ".livejournal.com",
            "path": "/"
        })
        log("LiveJournal: Session token injected. Reloading homepage...")
        driver.get("https://www.livejournal.com/")
        time.sleep(3)

    # ── Step 2: Go to new post page ───────────────────────────
    log("LiveJournal: Opening new post page...")
    if "update.bml" not in driver.current_url and "/post" not in driver.current_url:
        driver.get("https://www.livejournal.com/update.bml")
        time.sleep(6)
    log(f"LiveJournal: Post page = {driver.current_url}")

    if "login" in driver.current_url.lower():
        driver.save_screenshot(os.path.join(SCRIPT_DIR, 'livejournal_login_error.png'))
        result(False, error="LiveJournal: Redirected to login. Check credentials. Screenshot saved.")
        driver.quit(); sys.exit(1)

    # ── Step 3: Fill Subject/Title ────────────────────────────
    title_text = ai_title if ai_title else f"Best {keyword.title()} - Complete Guide {time.strftime('%Y')}"
    # LJ /post/ page uses textarea with placeholder='Title'
    for sel in ["textarea[placeholder='Title']","textarea[name='subject']",
                "input[id='subject']","input[name='subject']","#subject"]:
        try:
            el = WebDriverWait(driver,8).until(EC.presence_of_element_located((By.CSS_SELECTOR,sel)))
            if el.is_displayed():
                driver.execute_script("arguments[0].click(); arguments[0].value = arguments[1]; arguments[0].dispatchEvent(new Event('input', { bubbles: true }));", el, title_text)
                log(f"LiveJournal: Title typed via JS [{sel}]"); break
        except: continue

    # ── Step 4: Fill Post Content ─────────────────────────────
    # Prepend image HTML if image path provided
    img_html = ""
    if image_path and os.path.exists(image_path):
        try:
            import base64 as _b64
            with open(image_path, 'rb') as _f:
                _data = _b64.b64encode(_f.read()).decode()
            _ext = image_path.split('.')[-1].lower()
            _mime = 'image/png' if _ext == 'png' else 'image/jpeg'
            img_html = f'<img src="data:{_mime};base64,{_data}" alt="{keyword}" style="max-width:100%;margin-bottom:12px;" />\n\n'
            log("LiveJournal: Image embedded in content")
        except Exception as e:
            log(f"LiveJournal: Image embed failed: {e}")

    content_text = img_html + (ai_content if ai_content else (
        f"Best {keyword} at Learnmore Technologies — Complete Guide {time.strftime('%Y')}\n\n"
        f"Are you searching for the best {keyword} in Bangalore? Look no further. Learnmore Technologies offers a comprehensive {keyword} program designed to make you job-ready in 60-90 days.\n\n"
        f"Why Learn {keyword}?\n"
        f"- High salary potential — ₹4 LPA to ₹20 LPA\n"
        f"- Massive job demand across India and globally\n"
        f"- Industry-recognised certification\n"
        f"- Hands-on live projects with real-world data\n"
        f"- Flexible batch timings — weekday and weekend\n"
        f"- 100% placement assistance included\n\n"
        f"What You Will Learn:\n"
        f"Our {keyword} program covers everything from core fundamentals to advanced industry applications. You will work on real projects, learn the tools professionals use daily, and graduate with a portfolio that impresses employers.\n\n"
        f"Enroll now: {target_url}\n\n"
        f"Career Opportunities:\n"
        f"After completing {keyword}, you can work as analyst, developer, or consultant with top companies in India and abroad.\n\n"
        f"Why Choose Learnmore Technologies?\n"
        f"Expert trainers with 10+ years experience, small batches, proven placement record, and ongoing support.\n\n"
        f"Visit us at Kalyan Nagar, Bangalore or enroll online: {target_url}"
    ))

    for sel in ["div.notranslate.public-DraftEditor-content",
                ".public-DraftEditor-content",
                "[contenteditable='true']",
                "textarea[id='entry']","textarea[name='event']","#entry"]:
        try:
            el = WebDriverWait(driver,8).until(EC.presence_of_element_located((By.CSS_SELECTOR,sel)))
            if el.is_displayed():
                driver.execute_script("arguments[0].click();", el)
                time.sleep(0.5)
                tag = el.tag_name.lower()

                if tag == 'textarea':
                    el.clear()
                    el.send_keys(content_text)
                    log(f"LiveJournal: Full content written to textarea ({len(content_text)} chars)")
                else:
                    time.sleep(0.3)
                    from selenium.webdriver.common.keys import Keys as _Keys
                    el.send_keys(_Keys.CONTROL + 'a')
                    time.sleep(0.2)
                    el.send_keys(_Keys.DELETE)
                    time.sleep(0.5)
                    driver.execute_script("""
                        var el = arguments[0];
                        var html = arguments[1];
                        var dt = new DataTransfer();
                        dt.setData('text/html', html);
                        dt.setData('text/plain', el.innerText);
                        var event = new ClipboardEvent('paste', {
                            clipboardData: dt,
                            bubbles: true,
                            cancelable: true
                        });
                        el.dispatchEvent(event);
                    """, el, content_text)
                    log(f"LiveJournal: Rich HTML pasted via ClipboardEvent ({len(content_text)} chars)")
                break
        except Exception as e:
            log(f"LiveJournal: content type error [{sel}]: {e}")
            continue

    time.sleep(2)

    # ── Step 5: Publish ───────────────────────────────────────
    # Click "Tune in and publish" button
    published = False

    # Try JS-based click by button text (handles non-breaking spaces, Unicode)
    try:
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
        if clicked:
            log(f"LiveJournal: JS publish clicked: '{clicked.strip()}'")
            published = True
            time.sleep(3)  # Wait for popup to appear

            # Log post-click buttons for debugging popup
            try:
                all_btns2 = driver.find_elements(By.TAG_NAME, 'button')
                for b in all_btns2:
                    try:
                        log(f"LiveJournal: Post-click button='{b.text.strip()}' class='{b.get_attribute('class')}'")
                    except: pass
            except: pass

            # Click the confirm Publish button inside the popup (class js--submit-post)
            confirm_clicked = driver.execute_script("""
                // First try the specific js--submit-post class (the real Publish button in popup)
                var submitBtn = document.querySelector('.js--submit-post');
                if (submitBtn) {
                    submitBtn.click();
                    return submitBtn.innerText || submitBtn.textContent || 'js--submit-post';
                }
                return null;
            """)
            if confirm_clicked:
                log(f"LiveJournal: Popup confirm clicked: '{confirm_clicked.strip()}'")
                # Wait for page to navigate to published post URL
                time.sleep(3)
                # Poll for URL change (up to 12 seconds)
                for _ in range(12):
                    cur = driver.current_url
                    if "/post/" not in cur and "draft" not in cur and "livejournal.com" in cur:
                        log(f"LiveJournal: URL changed to: {cur}")
                        break
                    time.sleep(1)
            else:
                time.sleep(8)
    except Exception as e:
        log(f"LiveJournal: JS click error: {e}")

    if not published:
        # Try XPath selectors
        for sel in [
            "//button[contains(text(),'Tune in and publish')]",
            "//button[contains(normalize-space(),'Tune in and publish')]",
            "//button[contains(text(),'publish')]",
            "//button[contains(text(),'Publish')]",
        ]:
            try:
                btn = WebDriverWait(driver, 8).until(EC.element_to_be_clickable((By.XPATH, sel)))
                if btn.is_displayed() and btn.is_enabled():
                    driver.execute_script("arguments[0].click();", btn)
                    log(f"LiveJournal: Publish clicked: '{btn.text.strip()}'")
                    published = True
                    time.sleep(8)
                    break
            except: continue

    if not published:
        log("LiveJournal: Publish button not found — trying keyboard shortcut")
        try:
            driver.find_element(By.TAG_NAME, 'body').send_keys(Keys.CONTROL + Keys.RETURN)
            time.sleep(6)
        except: pass

    # ── Step 6: Get post URL ──────────────────────────────────
    final = driver.current_url
    log(f"LiveJournal: Final URL = {final}")

    subdomain = username.lower().replace("_", "-")
    profile_url = f"https://{subdomain}.livejournal.com/"

    import re
    post_pattern = re.escape(subdomain) + r'\.livejournal\.com/[0-9]+\.html'

    def find_post_link(driver, pattern):
        try:
            links = driver.find_elements(By.TAG_NAME, "a")
            for l in links:
                href = l.get_attribute("href") or ""
                if re.search(pattern, href, re.IGNORECASE):
                    return href
        except:
            pass
        return None

    # Check if current page has a valid post link
    valid_link = find_post_link(driver, post_pattern)
    if valid_link:
        final = valid_link
        log(f"LiveJournal: Found post URL on current page = {final}")
    else:
        log(f"LiveJournal: Navigating to profile to find latest post: {profile_url}")
        try:
            driver.get(profile_url)
            time.sleep(5)
            valid_link = find_post_link(driver, post_pattern)
            if valid_link:
                final = valid_link
                log(f"LiveJournal: Found latest post URL on profile page = {final}")
            else:
                final = profile_url
                log(f"LiveJournal: Fallback to profile URL = {final}")
        except Exception as pe:
            log(f"LiveJournal: Profile page navigation failed/crashed: {pe}. Falling back to profile URL.")
            final = profile_url

    if "livejournal.com" in final and "/post/" not in final and "login" not in final and "/photo" not in final:
        result(True, url=final)
    else:
        result(True, url=profile_url)

except Exception as e:
    log(f"LiveJournal: Error = {e}")
    result(False, error=str(e))
finally:
    try: driver.quit()
    except: pass
