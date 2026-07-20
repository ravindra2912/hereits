import json
import os
import ssl
import sys
import urllib.error
import urllib.request

def submit_support_ticket(ticket_data, user_info=None):
    base_url = os.environ.get("API_BASE_URL", "https://hereits.test/api/v1/").rstrip("/")
    url = f"{base_url}/tickets"
    
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE
    
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",
    }
    if user_info:
        headers["X-User-Info"] = str(user_info)
        
    req = urllib.request.Request(
        url,
        data=json.dumps(ticket_data).encode("utf-8"),
        headers=headers,
        method="POST"
    )
    
    try:
        with urllib.request.urlopen(req, context=ctx, timeout=30) as resp:
            res_data = json.loads(resp.read().decode("utf-8"))
            return res_data
    except Exception as e:
        print(f"Error submitting ticket to Laravel: {e}", file=sys.stderr)
        if hasattr(e, "read"):
            try:
                err_body = e.read().decode("utf-8")
                return json.loads(err_body)
            except Exception:
                pass
        return {"success": False, "message": str(e)}

def handle_support_ticket(parsed_res, user_info=None):
    ticket_info = parsed_res.get("json") if isinstance(parsed_res.get("json"), dict) else parsed_res
    
    ticket_data = {
        "category": ticket_info.get("category", ""),
        "subject": ticket_info.get("subject", ""),
        "description": ticket_info.get("description", ""),
        "email": ticket_info.get("email", ""),
        "contact": ticket_info.get("contact", "")
    }
    
    # Submit to Laravel API
    return submit_support_ticket(ticket_data, user_info)
