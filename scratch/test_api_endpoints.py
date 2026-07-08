import requests
import json

BASE_URL = "http://54.210.197.187/api"

# We will test login, stats, and projects endpoints
def run_test():
    print("=== STARTING LIVE API ENDPOINT VALIDATION ===")
    
    # 1. Login Test (Needs valid credentials)
    # We will try standard admin login
    login_url = f"{BASE_URL}/login.php"
    payload = {
        "username": "admin",
        "password": "admin" # Replace with actual password during execution if needed
    }
    
    print(f"\n1. Testing Login against: {login_url}")
    try:
        r = requests.post(login_url, json=payload, timeout=10)
        print(f"Status Code: {r.status_code}")
        response_data = r.json()
        print("Response:", json.dumps(response_data, indent=2))
        
        if not response_data.get("success"):
            print("Login failed (probably password mismatch, which is expected if not default).")
            # We can still test Auth block by sending a bad key
            api_key = "dummy_invalid_key"
        else:
            api_key = response_data.get("api_key")
            print(f"Login SUCCESS! Received API Key: {api_key}")
            
    except Exception as e:
        print(f"Login request failed: {e}")
        return

    # 2. Authenticated Stats Test
    stats_url = f"{BASE_URL}/stats.php"
    headers = {
        "Authorization": f"Bearer {api_key}"
    }
    print(f"\n2. Testing Authenticated Stats: {stats_url}")
    try:
        r = requests.get(stats_url, headers=headers, timeout=10)
        print(f"Status Code: {r.status_code}")
        print("Response:", json.dumps(r.json(), indent=2))
    except Exception as e:
        print(f"Stats request failed: {e}")

    # 3. Authenticated Projects List Test
    projects_url = f"{BASE_URL}/projects.php"
    print(f"\n3. Testing Authenticated Projects List: {projects_url}")
    try:
        r = requests.get(projects_url, headers=headers, timeout=10)
        print(f"Status Code: {r.status_code}")
        print("Response:", json.dumps(r.json(), indent=2))
    except Exception as e:
        print(f"Projects request failed: {e}")

    # 4. Authenticated Social Accounts List Test
    social_url = f"{BASE_URL}/social_accounts.php"
    print(f"\n4. Testing Authenticated Social Accounts List: {social_url}")
    try:
        r = requests.get(social_url, headers=headers, timeout=10)
        print(f"Status Code: {r.status_code}")
        print("Response:", json.dumps(r.json(), indent=2))
    except Exception as e:
        print(f"Social accounts request failed: {e}")

if __name__ == "__main__":
    run_test()
