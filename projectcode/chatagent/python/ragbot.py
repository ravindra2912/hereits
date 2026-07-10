import datetime
import json
import math
import os
import sqlite3
import sys
import urllib.error
import urllib.request
import ollama


def load_dotenv():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    dotenv_paths = [
        os.path.join(base_dir, ".env"),
        os.path.join(os.path.dirname(base_dir), ".env"),
        os.path.join(os.path.dirname(os.path.dirname(os.path.dirname(base_dir))), ".env")
    ]
    
    for dotenv_path in dotenv_paths:
        if os.path.exists(dotenv_path):
            with open(dotenv_path, "r", encoding="utf-8") as f:
                for line in f:
                    line = line.strip()
                    if not line or line.startswith("#"):
                        continue
                    if "=" in line:
                        key, val = line.split("=", 1)
                        key = key.strip()
                        val = val.strip().strip("'\"")
                        os.environ[key] = val


# Load environment variables from .env
load_dotenv()

OLLAMA_URL = os.environ.get("OLLAMA_URL", "http://localhost:11434/api/generate")
MODEL_NAME = os.environ.get("OLLAMA_MODEL", "hereits-chatbot")



def get_log_timestamp():
    now = datetime.datetime.now()
    return now.strftime("%Y-%m-%d %H:%M:%S") + f",{now.microsecond // 1000:03d}"


def log_request(model, url, payload, timeout=120):
    try:
        log_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "log")
        os.makedirs(log_dir, exist_ok=True)
        log_path = os.path.join(log_dir, "log.txt")
        
        timestamp = get_log_timestamp()
        payload_str = json.dumps(payload, ensure_ascii=False)
        
        log_line = (
            f"{timestamp} INFO [OLLAMA_REQUEST] "
            f"model={model} "
            f"url={url} "
            f"timeout={timeout} "
            f"payload={payload_str}\n"
        )
        with open(log_path, "a", encoding="utf-8") as f:
            f.write(log_line)
    except Exception as e:
        print(f"Failed to write to request log file: {e}", file=sys.stderr)


def log_response(model, status, body_data):
    try:
        log_dir = os.path.join(os.path.dirname(os.path.abspath(__file__)), "log")
        os.makedirs(log_dir, exist_ok=True)
        log_path = os.path.join(log_dir, "log.txt")
        
        timestamp = get_log_timestamp()
        body_str = json.dumps(body_data, ensure_ascii=False)
        
        log_line = (
            f"{timestamp} INFO [OLLAMA_RESPONSE] "
            f"model={model} "
            f"status={status} "
            f"body={body_str}\n"
        )
        with open(log_path, "a", encoding="utf-8") as f:
            f.write(log_line)
    except Exception as e:
        print(f"Failed to write to response log file: {e}", file=sys.stderr)


def cosine_similarity(v1, v2):
    dot_product = sum(a * b for a, b in zip(v1, v2))
    magnitude_1 = math.sqrt(sum(a * a for a in v1))
    magnitude_2 = math.sqrt(sum(a * a for a in v2))
    if not magnitude_1 or not magnitude_2:
        return 0.0
    return dot_product / (magnitude_1 * magnitude_2)


def get_context(message, threshold=0.50, limit=3):
    try:
        # Generate query embedding vector
        resp = ollama.embeddings(model="nomic-embed-text", prompt=message)
        query_vector = resp.get("embedding")
        if not query_vector:
            return [], False
            
        # Connect to SQLite store
        base_dir = os.path.dirname(os.path.abspath(__file__))
        db_path = os.path.join(base_dir, "rag_db", "rag_store.db")
        if not os.path.exists(db_path):
            return [], False
            
        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()
        cursor.execute("SELECT text_content, embedding FROM document_chunks")
        rows = cursor.fetchall()
        conn.close()
        
        scored_chunks = []
        for text, emb_json in rows:
            emb = json.loads(emb_json)
            score = cosine_similarity(query_vector, emb)
            if score >= threshold:
                scored_chunks.append((score, text))
                
        scored_chunks.sort(key=lambda x: x[0], reverse=True)
        best_chunks = [chunk[1] for chunk in scored_chunks[:limit]]
        
        return best_chunks, len(best_chunks) > 0
    except sqlite3.OperationalError as e:
        print(f"Database operational error (maybe need to run ingest.py?): {e}", file=sys.stderr)
        return [], False
    except Exception as e:
        print(f"Error retrieving context: {e}", file=sys.stderr)
        return [], False


def decrypt_laravel_user_info(encrypted_str):
    if not encrypted_str:
        return None
        
    app_key = os.environ.get("APP_KEY")
    if not app_key:
        print("Error: APP_KEY not found in environment variables.", file=sys.stderr)
        return None
        
    try:
        import base64
        import json
        import hmac
        import hashlib
        from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
        from cryptography.hazmat.primitives import padding
        
        # Decode outer base64
        payload_json = base64.b64decode(encrypted_str).decode('utf-8')
        payload = json.loads(payload_json)
        
        iv_b64 = payload['iv']
        value_b64 = payload['value']
        mac = payload['mac']
        
        # Decode key
        if app_key.startswith('base64:'):
            key_bytes = base64.b64decode(app_key[7:])
        else:
            key_bytes = app_key.encode('utf-8')
            
        # Verify MAC
        expected_mac_input = (iv_b64 + value_b64).encode('utf-8')
        calculated_mac = hmac.new(key_bytes, expected_mac_input, hashlib.sha256).hexdigest()
        if not hmac.compare_digest(calculated_mac, mac):
            raise ValueError("MAC verification failed")
            
        # Decrypt
        iv_bytes = base64.b64decode(iv_b64)
        ciphertext_bytes = base64.b64decode(value_b64)
        
        cipher = Cipher(algorithms.AES(key_bytes), modes.CBC(iv_bytes))
        decryptor = cipher.decryptor()
        decrypted_padded = decryptor.update(ciphertext_bytes) + decryptor.finalize()
        
        # Unpad PKCS7
        unpadder = padding.PKCS7(128).unpadder()
        decrypted_bytes = unpadder.update(decrypted_padded) + unpadder.finalize()
        
        decrypted_str = decrypted_bytes.decode('utf-8')
        try:
            return json.loads(decrypted_str)
        except Exception:
            return decrypted_str
    except Exception as e:
        print(f"Error decrypting user info: {e}", file=sys.stderr)
        return None


def ask_chatbot(message, session_id=None, history=None, is_login=False, user_info=None):
    # Retrieve context
    context_chunks, has_context = get_context(message)
    
    # Construct conversation prompt with context and history
    prompt = ""
    
    # Prepend user logged-in context if available
    if is_login and user_info:
        decrypted_info = decrypt_laravel_user_info(user_info) if isinstance(user_info, str) else user_info
        if decrypted_info:
            if isinstance(decrypted_info, dict):
                id = decrypted_info.get("id")
                first_name = decrypted_info.get("first_name") or ""
                last_name = decrypted_info.get("last_name") or ""
                name = (first_name + ' ' + last_name).strip() or "User"
                email = decrypted_info.get("email", "")
                role = decrypted_info.get("role", "Admin")
                prompt += f"User details: id: {id}, Name: {name}, Email: {email}, Role: {role}\n\n"
            elif isinstance(decrypted_info, str) and decrypted_info.strip():
                prompt += f"User details: {decrypted_info}\n\n"
            
    if has_context:
        context_text = "\n\n".join(context_chunks)
        prompt += f"Context:\n{context_text}\n\n"
        
    if history:
        for msg in history:
            role = "User" if msg.get("role") == "user" else "Assistant"
            content = msg.get("content", "")
            prompt += f"{role}: {content}\n"
        prompt += f"User: {message}\nAssistant:"
    else:
        if has_context:
            prompt += f"User Query:\n{message}"
        else:
            prompt = message

    payload = {
        "model": MODEL_NAME,
        "prompt": str(prompt),
        "stream": False,
        "think": False,
        "images_count": 0,
        "session_id": session_id,
    }
    
    # Log request immediately when prepared
    log_request(
        model=MODEL_NAME,
        url=OLLAMA_URL,
        payload=payload,
        timeout=120
    )
    
    req = urllib.request.Request(
        OLLAMA_URL,
        data=json.dumps(payload).encode("utf-8"),
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    
    try:
        with urllib.request.urlopen(req, timeout=120) as resp:
            raw_response = resp.read().decode("utf-8")
            data = json.loads(raw_response)
            
        # Log successful response immediately upon receipt
        log_response(
            model=MODEL_NAME,
            status=200,
            body_data=data
        )
        
        response_text = str(data.get("response", "")).strip()
        return response_text, has_context
    except urllib.error.HTTPError as e:
        try:
            err_body = e.read().decode("utf-8")
            try:
                err_data = json.loads(err_body)
            except Exception:
                err_data = {"error": err_body}
        except Exception:
            err_data = {"error": str(e)}
            
        log_response(
            model=MODEL_NAME,
            status=e.code,
            body_data=err_data
        )
        raise e
    except Exception as e:
        log_response(
            model=MODEL_NAME,
            status=500,
            body_data={"error": str(e)}
        )
        raise e


def main():
    message = " ".join(sys.argv[1:]).strip()
    if not message:
        print(json.dumps({"status": "error", "response": "message is required"}, ensure_ascii=True))
        sys.exit(1)

    try:
        response, has_context = ask_chatbot(message)
    except urllib.error.URLError:
        print(json.dumps({"status": "error", "response": "Cannot reach Ollama"}, ensure_ascii=True))
        sys.exit(2)
    except Exception:
        print(json.dumps({"status": "error", "response": "Unexpected server error"}, ensure_ascii=True))
        sys.exit(3)

    print(json.dumps({"status": "success", "response": response, "has_context": has_context}, ensure_ascii=True))


if __name__ == "__main__":
    main()
