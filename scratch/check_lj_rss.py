import urllib.request
import xml.etree.ElementTree as ET

def check_rss(uname):
    sub = uname.lower().replace("_", "-")
    rss_url = f"https://{sub}.livejournal.com/data/rss"
    print(f"\n--- Feed for {uname} ({rss_url}) ---")
    try:
        req = urllib.request.Request(
            rss_url,
            headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}
        )
        with urllib.request.urlopen(req, timeout=10) as response:
            xml_data = response.read()
        root = ET.fromstring(xml_data)
        items = root.findall('.//item')
        print(f"Total items found: {len(items)}")
        for idx, item in enumerate(items, 1):
            title = item.find('title')
            link = item.find('link')
            pub_date = item.find('pubDate')
            t_text = title.text if title is not None else "No Title"
            l_text = link.text if link is not None else "No Link"
            d_text = pub_date.text if pub_date is not None else "No Date"
            print(f"{idx}. {t_text} | URL: {l_text} | Date: {d_text}")
    except Exception as e:
        print(f"Error reading feed: {e}")

check_rss("skyrank_solutio")
check_rss("skyranksolution")
