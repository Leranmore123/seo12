#!/usr/bin/env python3
"""
Symbaloo Auto-Post Tile via Playwright
Migrated from Selenium for enhanced stability and auto-waiting.
"""
import sys, json, time, os, re
os.environ['PLAYWRIGHT_BROWSERS_PATH'] = '/usr/local/share/playwright'
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

def close_consent_modal(page):
    try:
        selectors = [
            "button:has-text('Agree')",
            "button:has-text('Accept')",
            "button:has-text('I agree')",
            "button:has-text('Consent')",
            "button[class*='agree']",
            "button[class*='consent']",
            "//button[contains(text(),'Agree')]",
        ]
        for sel in selectors:
            btn = page.locator(sel).first
            if btn.count() > 0 and btn.is_visible():
                btn.click()
                log(f"Symbaloo: Cookie Consent Accepted via {sel}.")
                page.wait_for_timeout(2000)
                return True
    except Exception as e:
        log(f"Symbaloo: Consent check error: {e}")
    return False

def close_adblock_modal(page):
    try:
        body_text = page.locator("body").inner_text()
        if "ad-block" not in body_text.lower() and "adblock" not in body_text.lower():
            return False
            
        log("Symbaloo: Adblock warning detected, closing...")
        selectors = [
            "button[aria-label*='Close' i]",
            "button[class*='close' i]",
            "[class*='close-btn' i]",
            "[class*='CloseBtn']",
            "span[class*='close' i]"
        ]
        for sel in selectors:
            btns = page.locator(sel)
            for idx in range(btns.count()):
                btn = btns.nth(idx)
                if btn.is_visible():
                    btn.click()
                    log(f"Symbaloo: Closed adblock popup via {sel}")
                    page.wait_for_timeout(2000)
                    return True
        # Try escape key
        page.keyboard.press("Escape")
        log("Symbaloo: Pressed Escape to close popup")
        page.wait_for_timeout(1000)
        return True
    except Exception as e:
        log(f"Symbaloo: Adblock close error: {e}")
    return False

def symbaloo_post(email, password, keyword, target_url, custom_mix_url="", ai_description=""):
    log(f"Starting Playwright Symbaloo post for: {email}")
    import hashlib
    email_hash = hashlib.md5(email.lower().encode('utf-8')).hexdigest()
    profile_dir = os.path.join(script_dir, f'chrome_profile_symbaloo_{email_hash}_{sys_user}')
    
    # Clean locks
    if os.path.exists(profile_dir):
        for lf in [os.path.join(profile_dir,'Default','LOCK'), os.path.join(profile_dir,'SingletonLock')]:
            try:
                if os.path.exists(lf): os.remove(lf)
            except: pass

    from playwright.sync_api import sync_playwright
    
    with sync_playwright() as p:
        try:
            launch_args = [
                "--no-sandbox",
                "--disable-dev-shm-usage",
                "--disable-blink-features=AutomationControlled",
                "--disable-gpu",
                "--disable-software-rasterizer"
            ]
            
            context = p.chromium.launch_persistent_context(
                user_data_dir=profile_dir,
                headless=True,
                args=launch_args,
                viewport={"width": 1400, "height": 900},
                user_agent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36"
            )
            
            page = context.pages[0] if context.pages else context.new_page()
            
            # Navigate to login
            log("Symbaloo: Navigating to login page...")
            page.goto("https://www.symbaloo.com/login")
            page.wait_for_timeout(5000)
            
            close_consent_modal(page)
            
            if "login" in page.url.lower():
                log("Symbaloo: Logging in...")
                
                # Email input
                email_input = page.locator("input[type='email'], input[name='email'], #email").first
                email_input.wait_for(state="visible", timeout=15000)
                email_input.click()
                email_input.fill(email)
                
                # Password input
                pass_input = page.locator("input[type='password'], #password").first
                pass_input.click()
                pass_input.fill(password)
                
                # Submit
                submit_btn = page.locator("button[type='submit'], #login-button").first
                submit_btn.click()
                
                page.wait_for_timeout(10000)
            else:
                log("Symbaloo: Already logged in!")
                
            # Go to target mix URL
            target_mix = custom_mix_url if (custom_mix_url and "symbaloo.com" in custom_mix_url) else "https://www.symbaloo.com/"
            log(f"Symbaloo: Navigating to mix = {target_mix}")
            page.goto(target_mix)
            page.wait_for_timeout(10000)
            
            close_consent_modal(page)
            close_adblock_modal(page)
            
            log(f"Symbaloo: Mix loaded = {page.url}")
            
            # Click Edit Webmix to enter edit mode
            try:
                edit_btn = page.locator("button:has-text('Edit Webmix'), button:has-text('Edit webmix')").first
                if edit_btn.count() > 0 and edit_btn.is_visible():
                    edit_btn.click()
                    log("Symbaloo: Clicked 'Edit Webmix' to enter edit mode")
                    page.wait_for_timeout(4000)
            except Exception as e:
                log(f"Symbaloo: Edit mode click error: {e}")
                
            # Find empty cells
            cells = page.locator("[id^='gridEmptyCell']")
            cell_count = cells.count()
            log(f"Symbaloo: Empty cells = {cell_count}")
            
            if cell_count == 0:
                result(False, error="Symbaloo: No empty cells in this mix")
                context.close()
                return
                
            # Try first few cells to open sidebar
            tile_input = None
            max_cells_to_try = min(cell_count, 5)
            
            for idx in range(max_cells_to_try):
                cell = cells.nth(idx)
                log(f"Symbaloo: Trying empty cell #{idx+1}...")
                
                try:
                    cell.scroll_into_view_if_needed()
                    page.wait_for_timeout(500)
                    cell.click()
                    page.wait_for_timeout(2000)
                except Exception as e:
                    log(f"Cell click exception: {e}")
                    continue
                    
                close_adblock_modal(page)
                
                # Check for tile search input
                for sel in [
                    "#tileSearchInput",
                    "input[placeholder*='URL' i]",
                    "input[placeholder*='url' i]",
                    "input[placeholder*='search query' i]",
                    "input[placeholder*='Enter a URL' i]",
                ]:
                    try:
                        inp = page.locator(sel).first
                        if inp.count() > 0 and inp.is_visible():
                            tile_input = inp
                            log(f"Symbaloo: Found tileSearchInput [{sel}] on cell #{idx+1}!")
                            break
                    except:
                        continue
                        
                if tile_input:
                    break
                    
            if not tile_input:
                page.screenshot(path=os.path.join(script_dir, 'symbaloo_error.png'))
                result(False, error="Symbaloo: tileSearchInput not found after trying multiple empty cells")
                context.close()
                return
                
            # Type URL
            tile_input.click()
            tile_input.fill(target_url)
            log(f"Symbaloo: URL typed = {target_url}")
            page.wait_for_timeout(1000)
            
            # Press enter
            tile_input.press("Enter")
            log("Symbaloo: Enter pressed on input")
            page.wait_for_timeout(6000)
            
            page.screenshot(path=os.path.join(script_dir, 'symbaloo_tile_added.png'))
            page.wait_for_timeout(6000)
            
            # Try to click search result item
            added = False
            for sel in [
                "[class*='result']", "[class*='searchResult']",
                "[class*='suggestion']", "[class*='tileResult']",
            ]:
                try:
                    els = page.locator(sel)
                    for i in range(els.count()):
                        el = els.nth(i)
                        if el.is_visible():
                            el.click()
                            log(f"Symbaloo: Clicked result [{sel}]")
                            page.wait_for_timeout(3000)
                            added = True
                            break
                    if added: break
                except:
                    continue
                    
            if not added:
                log("Symbaloo: No result button — tile auto-added on Enter")
                
            # Look for "Edit Tile"
            page.wait_for_timeout(2000)
            edit_tile_btn = page.locator("button:has-text('Edit Tile')").first
            if edit_tile_btn.count() > 0 and edit_tile_btn.is_visible():
                edit_tile_btn.click()
                log("Symbaloo: Edit Tile clicked")
                page.wait_for_timeout(4000)
                
                # Fill URL input
                url_field = page.locator("input[name='url']").first
                if url_field.count() > 0 and url_field.is_visible():
                    url_field.click()
                    url_field.fill(target_url)
                    log("Symbaloo: URL = " + target_url)
                    
                # Fill Name input
                tile_title = "Best " + keyword.title() + " Training"
                name_field = page.locator("input[name='name']").first
                if name_field.count() > 0 and name_field.is_visible():
                    name_field.click()
                    name_field.fill(tile_title)
                    log("Symbaloo: Name = " + tile_title)
                    
                # Fill Description textarea
                if ai_description:
                    tile_desc = ai_description
                else:
                    tile_desc = ("Best " + keyword + " training at Learnmore Technologies. "
                                 "Expert trainers, live projects, placement support. "
                                 "Enroll: " + target_url)
                desc_field = page.locator("textarea").first
                if desc_field.count() > 0 and desc_field.is_visible():
                    desc_field.click()
                    desc_field.fill(tile_desc[:300])
                    log("Symbaloo: Description filled")
                    
            # Click "Finish editing Webmix"
            page.wait_for_timeout(2000)
            finish_btn = page.locator("button:has-text('Finish'), button:has-text('finish')").first
            if finish_btn.count() > 0 and finish_btn.is_visible():
                finish_btn.click()
                log("Symbaloo: Clicked Finish button")
                page.wait_for_timeout(4000)
                
            page.screenshot(path=os.path.join(script_dir, 'symbaloo_tile_final.png'))
            log(f"Symbaloo: Final URL = {page.url}")
            result(True, url=page.url)
            context.close()
            
        except Exception as e:
            try:
                if 'page' in locals():
                    page.screenshot(path=os.path.join(script_dir, 'symbaloo_error.png'))
                    log("Saved exception error screenshot to symbaloo_error.png")
            except Exception as ex:
                log(f"Screenshot exception: {ex}")
            result(False, error=str(e))
            try:
                if 'context' in locals():
                    context.close()
            except:
                pass

if __name__ == "__main__":
    if len(sys.argv) < 5:
        result(False, error="Usage: symbaloo_post_playwright.py <email> <password> <keyword> <target_site> [custom_mix_url] [ai_description]")
        sys.exit(1)
    symbaloo_post(
        sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4],
        sys.argv[5] if len(sys.argv) > 5 else "",
        sys.argv[6] if len(sys.argv) > 6 else "",
    )
