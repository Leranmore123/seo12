#!/usr/bin/env python3
"""
LiveJournal Auto-Post via Playwright
Migrated from Selenium and XML-RPC for maximum browser realism and stability.
"""
import sys, json, time, os, re, hashlib
os.environ['PLAYWRIGHT_BROWSERS_PATH'] = '/usr/local/share/playwright'
script_dir = os.path.dirname(os.path.abspath(__file__))

# Profile isolation by sys_user and username hash
try:
    import pwd
    sys_user = pwd.getpwuid(os.getuid())[0]
except Exception:
    import getpass
    sys_user = getpass.getuser()

def log(msg):
    print(json.dumps({"log": msg}), flush=True)

def result(success, url='', error=''):
    print(json.dumps({"success": success, "url": url, "error": error}), flush=True)

def get_lj_session(user, pwd):
    import requests
    challenge_url = "https://www.livejournal.com/interface/flat"
    try:
        r = requests.post(challenge_url, data={"mode": "getchallenge"}, timeout=30)
        lines = [line.strip() for line in r.text.split("\n") if line.strip()]
        data = {}
        for i in range(0, len(lines) - 1, 2):
            data[lines[i]] = lines[i+1]
        chal = data.get("challenge")
        if not chal:
            return None
            
        pw_hash = hashlib.md5(pwd.encode('utf-8')).hexdigest()
        auth_response = hashlib.md5((chal + pw_hash).encode('utf-8')).hexdigest()
        
        r2 = requests.post(challenge_url, data={
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
            return data2.get("ljsession")
    except Exception as e:
        log(f"Session generation error: {e}")
    return None

def livejournal_post(username, password, keyword, target_url, ai_title, image_path, content_file):
    user_hash = hashlib.md5(username.lower().encode('utf-8')).hexdigest()
    profile_dir = os.path.join(script_dir, f'chrome_profile_livejournal_{user_hash}_{sys_user}')
    
    # Remove locks
    for lock in ['SingletonLock', 'LOCK']:
        lock_path = os.path.join(profile_dir, lock)
        if os.path.exists(lock_path):
            try:
                os.remove(lock_path)
            except:
                pass

    # Load content from file
    if content_file and os.path.exists(content_file):
        with open(content_file, 'r', encoding='utf-8') as f:
            ai_content = f.read()
        log(f"LiveJournal: Content loaded from file ({len(ai_content)} chars)")
    else:
        ai_content = f"<p>Learn more about {keyword} here. Visit <a href='{target_url}'>{target_url}</a>.</p>"

    # Ensure title is not empty
    if not ai_title:
        ai_title = f"Guide on {keyword}"

    from playwright.sync_api import sync_playwright

    with sync_playwright() as p:
        try:
            log("Launching browser context...")
            context = p.chromium.launch_persistent_context(
                user_data_dir=profile_dir,
                headless=True,
                no_viewport=True,
                args=[
                    '--no-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-blink-features=AutomationControlled'
                ]
            )
            
            page = context.pages[0] if context.pages else context.new_page()
            page.set_viewport_size({"width": 1400, "height": 900})
            
            log("LiveJournal: Checking login state...")
            
            # Replicate secure flat login session generation and cookie injection
            ljsession = get_lj_session(username, password)
            if ljsession:
                log("LiveJournal: Flat API session generated. Injecting session cookie...")
                page.goto("https://www.livejournal.com/robots.txt", timeout=60000)
                page.wait_for_timeout(1000)
                
                context.add_cookies([{
                    "name": "ljsession",
                    "value": ljsession,
                    "domain": ".livejournal.com",
                    "path": "/"
                }])
                log("LiveJournal: Session cookie injected.")
            
            page.goto("https://www.livejournal.com/update.bml", timeout=60000)
            page.wait_for_timeout(3000)
            
            # Check if redirected to login page (fallback)
            if "login.bml" in page.url or page.locator("input[name='user']").count() > 0:
                log("LiveJournal: Logging in...")
                page.goto("https://www.livejournal.com/login.bml?returnto=https://www.livejournal.com/update.bml", timeout=60000)
                page.wait_for_timeout(3000)
                
                # Fill login fields
                user_input = page.locator("input[name='user'], input#user").first
                user_input.wait_for(state="visible", timeout=20000)
                user_input.fill(username)
                
                pass_input = page.locator("input[name='password'], input#password").first
                pass_input.fill(password)
                
                # Submit
                submit_btn = page.locator("button[type='submit'], input[type='submit']").first
                submit_btn.click()
                log("LiveJournal: Login submitted")
                
                # Wait for the login processing and redirect (up to 30 seconds)
                try:
                    page.wait_for_url(re.compile(r"(update\.bml|homepage|profile|feed)"), timeout=30000)
                except Exception as ex:
                    log(f"LiveJournal: Redirection wait timed out: {ex}")
                
                if "login.bml" in page.url:
                    # Check for visible errors
                    error_msg = ""
                    error_loc = page.locator(".b-login-error, .error, .alert").first
                    if error_loc.count() > 0 and error_loc.is_visible():
                        error_msg = ": " + error_loc.text_content().strip()
                    raise Exception(f"LiveJournal: Login failed. Verify credentials{error_msg}")
                
                log("LiveJournal: Logged in!")
            else:
                log("LiveJournal: Already logged in!")

            # Ensure update page is loaded
            if "update.bml" not in page.url:
                page.goto("https://www.livejournal.com/update.bml", timeout=60000)
                page.wait_for_timeout(5000)

            # 1. Fill title
            log("LiveJournal: Filling title...")
            title_input = page.locator("textarea[placeholder='Title'], input[placeholder='Title']").first
            title_input.wait_for(state="visible", timeout=20000)
            title_input.click()
            title_input.fill(ai_title)
            log("LiveJournal: Title filled")
            page.wait_for_timeout(1000)

            # 2. Fill content (Draft.js Editor)
            log("LiveJournal: Filling Draft.js content...")
            editor = page.locator(".public-DraftEditor-content").first
            editor.wait_for(state="visible", timeout=20000)
            editor.click()
            page.wait_for_timeout(500)

            # Use JavaScript ClipboardEvent dispatch to paste HTML content cleanly
            page.evaluate("""
                ([selector, html]) => {
                    var el = document.querySelector(selector);
                    var dt = new DataTransfer();
                    dt.setData('text/html', html);
                    dt.setData('text/plain', el.innerText || '');
                    var event = new ClipboardEvent('paste', {
                        clipboardData: dt,
                        bubbles: true,
                        cancelable: true
                    });
                    el.dispatchEvent(event);
                }
            """, [".public-DraftEditor-content", ai_content])

            # Trigger state synchronization with a space and backspace
            editor.press("Space")
            editor.press("Backspace")
            log("LiveJournal: Content pasted & state synced")
            page.wait_for_timeout(2000)

            # 3. Publish
            log("LiveJournal: Clicking publish button...")
            publish_btn = page.locator("button:has-text('Publish'), button:has-text('publish'), button:has-text('Tune'), button:has-text('tune')").first
            publish_btn.wait_for(state="visible", timeout=10000)
            publish_btn.click()
            page.wait_for_timeout(3000)

            # Confirm publish dialog
            confirm_btn = page.locator(".js--submit-post, button.js--submit-post").first
            confirm_btn.wait_for(state="visible", timeout=10000)
            confirm_btn.click()
            log("LiveJournal: Final publish confirmed")
            page.wait_for_timeout(10000)

            # Detect final post URL from redirect URL or page links
            final_url = page.url
            if "livejournal.com" in final_url and "update.bml" not in final_url and "post/" not in final_url:
                log(f"LiveJournal: Published at {final_url}")
                result(True, url=final_url)
            else:
                user_subdomain = username.lower().replace('_', '-')
                post_link_loc = page.locator(f"a[href*='{user_subdomain}.livejournal.com']").first
                try:
                    post_link_loc.wait_for(state="visible", timeout=5000)
                    extracted_url = post_link_loc.get_attribute("href")
                    if extracted_url:
                        log(f"LiveJournal: Extracted post URL: {extracted_url}")
                        result(True, url=extracted_url)
                        context.close()
                        return
                except Exception as ex:
                    log(f"No specific journal link found on page: {ex}")
                
                # Alternate search: search all links for entry URL format
                try:
                    all_links = page.locator("a").all()
                    for link in all_links:
                        href = link.get_attribute("href") or ""
                        if "livejournal.com" in href and any(c.isdigit() for c in href) and "update.bml" not in href and "post/" not in href:
                            log(f"LiveJournal: Extracted URL from entry link: {href}")
                            result(True, url=href)
                            context.close()
                            return
                except:
                    pass

                fallback_url = f"https://{user_subdomain}.livejournal.com/"
                log(f"LiveJournal: Fallback to profile URL: {fallback_url}")
                result(True, url=fallback_url)
            
            context.close()

        except Exception as e:
            try:
                if 'page' in locals():
                    page.screenshot(path=os.path.join(os.path.dirname(script_dir), 'uploads', 'livejournal_error.png'))
                    log("Saved exception error screenshot to livejournal_error.png")
            except Exception as ex:
                log(f"Screenshot exception: {ex}")
            result(False, error=str(e))
            try:
                if 'context' in locals():
                    context.close()
            except:
                pass

if __name__ == "__main__":
    if len(sys.argv) < 8:
        result(False, error="Usage: livejournal_post_playwright.py <username> <password> <keyword> <target_url> <title> <image_path> <content_file>")
        sys.exit(1)
        
    livejournal_post(
        sys.argv[1],
        sys.argv[2],
        sys.argv[3],
        sys.argv[4],
        sys.argv[5],
        sys.argv[6],
        sys.argv[7]
    )
