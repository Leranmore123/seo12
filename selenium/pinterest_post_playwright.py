#!/usr/bin/env python3
"""
Pinterest Auto-Post via Playwright
Migrated from Selenium for enhanced stability and auto-waiting.
"""
import sys, json, time, os, re
script_dir = os.path.dirname(os.path.abspath(__file__))

# Profile isolation by sys_user and email hash
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

def pinterest_post(email, password, keyword, target_site, image_path=None, ai_title="", ai_content=""):
    log(f"Starting Playwright Pinterest post with email: {email}")
    
    import hashlib
    email_hash = hashlib.md5(email.lower().encode('utf-8')).hexdigest()
    profile_dir = os.path.join(script_dir, f'chrome_profile_pinterest_{email_hash}_{sys_user}')
    
    # Clean up lock files from any previous crashed runs
    if os.path.exists(profile_dir):
        for lock_name in ["SingletonLock", "SingletonCookie", "SingletonSocket", "lock"]:
            lock_path = os.path.join(profile_dir, lock_name)
            if os.path.exists(lock_path) or os.path.islink(lock_path):
                try:
                    if os.path.islink(lock_path):
                        os.unlink(lock_path)
                    else:
                        os.remove(lock_path)
                except:
                    pass

    from playwright.sync_api import sync_playwright
    
    with sync_playwright() as p:
        try:
            # Launch persistent context
            launch_args = [
                "--no-sandbox",
                "--disable-dev-shm-usage",
                "--disable-blink-features=AutomationControlled",
                "--disable-gpu",
                "--disable-software-rasterizer"
            ]
            
            log("Launching browser context...")
            context = p.chromium.launch_persistent_context(
                user_data_dir=profile_dir,
                headless=True,
                args=launch_args,
                viewport={"width": 1400, "height": 900},
                user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
            )
            
            page = context.pages[0] if context.pages else context.new_page()
            
            # ── Step 1: Login Check ────────────────────────────────────
            log("Checking login status...")
            page.goto("https://www.pinterest.com/")
            page.wait_for_timeout(4000)
            
            already_logged = False
            try:
                if page.locator("[data-test-id='header-profile'], [data-test-id='header-accounts-options-button']").count() > 0:
                    already_logged = True
            except:
                pass
                
            if not already_logged:
                log("Not logged in — logging in...")
                page.goto("https://www.pinterest.com/login/")
                page.wait_for_timeout(4000)
                
                # Email input
                email_input = page.locator("input[type='email'], input#email, input[name='username']").first
                email_input.wait_for(state="visible", timeout=20000)
                email_input.click()
                email_input.fill(email)
                page.wait_for_timeout(500)
                
                # Password input
                pass_input = page.locator("input[type='password'], input#password, input[name='password']").first
                pass_input.click()
                pass_input.fill(password)
                page.wait_for_timeout(500)
                
                # Click Submit
                submit_btn = page.locator("button[type='submit'], button.red.SignupButton, button.red.LoginButton").first
                submit_btn.click()
                
                page.wait_for_timeout(7000)
                
                if "login" in page.url:
                    page.screenshot(path=os.path.join(script_dir, 'pinterest_error.png'))
                    log("Saved login failure screenshot to pinterest_error.png")
                    result(False, error="Pinterest login failed — may be blocked temporarily. Try again in 10 minutes.")
                    context.close()
                    return
            
            log("Login OK!")
            
            # ── Step 2: Pin Creation Tool ──────────────────────────────
            page.goto("https://www.pinterest.com/pin-creation-tool/")
            page.wait_for_timeout(6000)
            log("Pin builder opened")
            
            # ── Step 3: Upload image ───────────────────────────────────
            if image_path and os.path.exists(image_path):
                log("Uploading image...")
                try:
                    file_input = page.locator("input[data-test-id='storyboard-upload-input']")
                    file_input.wait_for(state="attached", timeout=15000)
                    file_input.set_input_files(os.path.abspath(image_path))
                    page.wait_for_timeout(8000)
                    log("Image uploaded!")
                except Exception as e:
                    log(f"Image upload: {e}")
            
            # ── Step 4: Title ──────────────────────────────────────────
            title = ai_title if ai_title else f"Best {keyword.title()} - {time.strftime('%Y')} Guide"
            log("Filling title...")
            try:
                title_input = page.locator("#storyboard-selector-title")
                title_input.wait_for(state="visible", timeout=10000)
                title_input.click()
                title_input.fill(title[:100])
                log("Title OK!")
            except Exception as e:
                log(f"Title: {e}")
                
            # ── Step 5: Description ────────────────────────────────────
            if ai_content and len(ai_content.strip()) > 50:
                desc = ai_content.strip()
            else:
                desc = (
                    f"Looking for the best {keyword}? Learnmore Technologies offers expert-led "
                    f"{keyword} with hands-on live projects, industry-recognized certification, "
                    f"and 100% placement support. Our {keyword} covers all key concepts from "
                    f"beginner to advanced level. Join hundreds of successful students who built "
                    f"their IT careers with us. Flexible batch timings, experienced trainers, "
                    f"small batches for personal attention. "
                    f"Enroll now at {target_site} — Limited seats available! "
                    f"#{keyword.replace(' ','')} #Training #Bangalore #Career #Education #Certification"
                )
            desc = desc[:500]
            log(f"Description length: {len(desc)} chars")
            log("Filling description...")
            try:
                desc_input = page.locator("[contenteditable='true'], .public-DraftEditor-editor, [data-test-id*='description']").first
                desc_input.wait_for(state="visible", timeout=10000)
                desc_input.click()
                desc_input.fill(desc)
                log("Description OK!")
            except Exception as e:
                try:
                    page.evaluate("([sel, val]) => { document.querySelector(sel).innerText = val; }", ["[contenteditable='true']", desc])
                    log("Description OK (JS fallback)!")
                except Exception as e2:
                    log(f"Desc: {e2}")
            
            # ── Step 6: Link ───────────────────────────────────────────
            log("Filling link...")
            try:
                link_input = page.locator("input[name='link'], input[id='WebsiteField'], input[placeholder*='link'], input[placeholder*='Link']").first
                link_input.wait_for(state="visible", timeout=10000)
                link_input.click()
                link_input.fill(target_site)
                log("Link OK!")
            except Exception as e:
                log(f"Link: {e}")
                
            # ── Step 7: Board ──────────────────────────────────────────
            log("Opening board dropdown...")
            try:
                board_btn = page.locator("[data-test-id='board-dropdown-select-button']").first
                board_btn.wait_for(state="visible", timeout=10000)
                board_btn.click()
                page.wait_for_timeout(3000)
                log("Board dropdown opened")
                
                flyout = page.locator("[data-test-id='board-picker-flyout']")
                if flyout.count() > 0:
                    rows = flyout.locator("[data-test-id='boardWithoutSection']")
                    row_count = rows.count()
                    log(f"Board rows found: {row_count}")
                    
                    kw_words = [w.lower() for w in keyword.split() if len(w) > 2]
                    best_idx = None
                    best_score = 0
                    
                    for idx in range(row_count):
                        txt = rows.nth(idx).inner_text().strip().lower()
                        if not txt or 'create' in txt:
                            continue
                        score = sum(1 for w in kw_words if w in txt)
                        if score > best_score:
                            best_score = score
                            best_idx = idx
                            
                    if best_idx is not None:
                        row_to_click = rows.nth(best_idx)
                        log(f"Board selected: '{row_to_click.inner_text().strip()}' (score={best_score})")
                        row_to_click.click()
                    else:
                        first_row = rows.first
                        log(f"Board selected: '{first_row.inner_text().strip()}' (fallback)")
                        first_row.click()
                    page.wait_for_timeout(3000)
            except Exception as e:
                log(f"Board select: {e}")
                
            # ── Step 8: Publish ────────────────────────────────────────
            log("Publishing pin...")
            published = False
            for attempt in range(4):
                try:
                    btns = page.locator("button")
                    btn_count = btns.count()
                    for idx in range(btn_count):
                        btn = btns.nth(idx)
                        txt = btn.inner_text().strip()
                        if txt == 'Publish' and btn.is_visible() and btn.is_enabled():
                            btn.click()
                            log("Published via Publish button click!")
                            published = True
                            break
                    if published:
                        break
                except Exception as e:
                    log(f"Publish attempt {attempt+1} failed: {e}")
                page.wait_for_timeout(3000)
                
            # ── Step 9: Get Pin URL ────────────────────────────────────
            page.wait_for_timeout(5000)
            for _ in range(8):
                cu = page.url
                if "/pin/" in cu:
                    result(True, url=cu)
                    context.close()
                    return
                page.wait_for_timeout(2000)
                
            page_source = page.content()
            pin_urls = re.findall(r'https://[a-z.]*pinterest\.com/pin/\d+', page_source)
            if pin_urls:
                result(True, url=pin_urls[0])
            elif published:
                uname = email.split("@")[0].lower().replace(".", "")
                result(True, url=f"https://www.pinterest.com/{uname}/")
            else:
                result(False, error="Pin may not have published — check Pinterest account.")
                
            context.close()
            
        except Exception as e:
            try:
                page.screenshot(path=os.path.join(script_dir, 'pinterest_error.png'))
                log("Saved exception error screenshot to pinterest_error.png")
            except Exception as ex:
                log(f"Screenshot exception: {ex}")
            result(False, error=str(e))
            try:
                context.close()
            except:
                pass

if __name__ == "__main__":
    if len(sys.argv) < 5:
        result(False, error="Usage: pinterest_post_playwright.py <email> <password> <keyword> <target_site> [image_path] [ai_title] [ai_content]")
        sys.exit(1)
    pinterest_post(
        sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4],
        sys.argv[5] if len(sys.argv) > 5 else None,
        sys.argv[6] if len(sys.argv) > 6 else "",
        sys.argv[7] if len(sys.argv) > 7 else "",
    )
