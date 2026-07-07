import sys
import time
import urllib.parse
import hmac
import hashlib
import base64
import requests

def get_oauth_header(consumer_key, consumer_secret, token, token_secret, url, method, params):
    import uuid
    nonce = uuid.uuid4().hex
    timestamp = str(int(time.time()))
    
    oauth_params = {
        'oauth_consumer_key': consumer_key,
        'oauth_nonce': nonce,
        'oauth_signature_method': 'HMAC-SHA1',
        'oauth_timestamp': timestamp,
        'oauth_token': token,
        'oauth_version': '1.0'
    }
    
    # Merge parameters
    all_params = {}
    all_params.update(oauth_params)
    for k, v in params.items():
        if k != 'data64':
            all_params[k] = v
            
    # Sort
    sorted_params = sorted(all_params.items())
    
    # Query string
    query_parts = []
    for k, v in sorted_params:
        query_parts.append(f"{urllib.parse.quote(k, safe='')}={urllib.parse.quote(v, safe='')}")
    query_string = '&'.join(query_parts)
    
    # Base String
    base_string = f"{method.upper()}&{urllib.parse.quote(url, safe='')}&{urllib.parse.quote(query_string, safe='')}"
    
    # Signing Key
    signing_key = f"{urllib.parse.quote(consumer_secret, safe='')}&{urllib.parse.quote(token_secret, safe='')}".encode('utf-8')
    
    # Signature
    hashed = hmac.new(signing_key, base_string.encode('utf-8'), hashlib.sha1)
    signature = base64.b64encode(hashed.digest()).decode('utf-8')
    
    oauth_params['oauth_signature'] = signature
    
    # Auth Header
    header_parts = []
    for k, v in oauth_params.items():
        header_parts.append(f'{k}="{urllib.parse.quote(v, safe=\'\')}"')
    return 'OAuth ' + ', '.join(header_parts)

if __name__ == '__main__':
    if len(sys.argv) < 6:
        print("Usage: python3 test_tumblr_api.py <consumer_key> <consumer_secret> <token> <token_secret> <blog_name>")
        sys.exit(1)
        
    consumer_key = sys.argv[1]
    consumer_secret = sys.argv[2]
    token = sys.argv[3]
    token_secret = sys.argv[4]
    blog_name = sys.argv[5].replace('https://', '').replace('http://', '')
    
    url = f"https://api.tumblr.com/v2/blog/{blog_name}/post"
    post_fields = {
        'type': 'text',
        'title': 'SkyRank SEO Solution',
        'body': '<p>SkyRank provides the best SEO and Backlink solutions. Learn more: <a href="https://skyranksolution-bice.vercel.app/">SkyRank Solution</a></p>',
        'tags': 'seo,backlinks,marketing'
    }
    
    auth_header = get_oauth_header(consumer_key, consumer_secret, token, token_secret, url, 'POST', post_fields)
    
    print("Sending API request to Tumblr...")
    headers = {
        'Authorization': auth_header,
        'Content-Type': 'application/x-www-form-urlencoded'
    }
    
    response = requests.post(url, data=post_fields, headers=headers)
    print("HTTP Status Code:", response.status_code)
    print("Response Content:", response.text)
