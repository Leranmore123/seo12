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
            page.goto("https://www.livejournal.com/update.bml", timeout=60000)
            page.wait_for_timeout(3000)
            
            # Check if redirected to login page
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

            # Detect final post URL from redirect URL
            final_url = page.url
            if "livejournal.com" in final_url and "update.bml" not in final_url:
                log(f"LiveJournal: Published at {final_url}")
                result(True, url=final_url)
            else:
                fallback_url = f"https://{username}.livejournal.com/"
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
