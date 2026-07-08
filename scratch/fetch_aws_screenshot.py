import urllib.request
import os

files = ["lj_popup_attempt_1.png", "lj_after_confirm_click.png", "lj_after_confirm_click_wait.png"]

for f in files:
    url = f"http://54.210.197.187/scratch/{f}"
    local_path = f"c:\\Users\\ADMIN\\Desktop\\seo-system\\scratch\\{f}"
    try:
        os.makedirs(os.path.dirname(local_path), exist_ok=True)
        print(f"Downloading {url}...")
        urllib.request.urlretrieve(url, local_path)
        print(f"Download of {f} successful!")
    except Exception as e:
        print(f"Download of {f} failed: {e}")
