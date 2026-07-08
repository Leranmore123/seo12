import xmlrpc.client
import hashlib
import sys

username = "LMT_12"
password = "@Pratik12@"

print("Connecting to LiveJournal XML-RPC server...")
try:
    server = xmlrpc.client.ServerProxy("https://www.livejournal.com/interface/xmlrpc")
    
    print("Getting challenge...")
    challenge_data = server.LJ.XMLRPC.getchallenge()
    challenge = challenge_data.get('challenge')
    if not challenge:
        print("Error: No challenge returned.")
        sys.exit(1)
        
    print(f"Challenge received: {challenge}")
    
    password_hash = hashlib.md5(password.encode('utf-8')).hexdigest()
    auth_response = hashlib.md5((challenge + password_hash).encode('utf-8')).hexdigest()
    
    from datetime import datetime
    now = datetime.now()
    
    params = {
        "username": username,
        "auth_method": "challenge",
        "auth_challenge": challenge,
        "auth_response": auth_response,
        "ver": "1",
        "event": "<p>This is a test post from Python XML-RPC. Visit us at <a href='https://learnmoretech.in'>LearnMoreTech</a>.</p>",
        "subject": "XML-RPC API Test Post",
        "year": now.year,
        "mon": now.month,
        "day": now.day,
        "hour": now.hour,
        "min": now.minute,
        "props": {
            "opt_preformatted": True,
        }
    }
    
    print("Sending postevent request...")
    response = server.LJ.XMLRPC.postevent(params)
    print("\nResponse from server:")
    print(response)
    
except Exception as e:
    print(f"Error occurred: {e}")
