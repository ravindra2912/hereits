import json
import math
import os
import sqlite3
import ollama

def cosine_similarity(v1, v2):
    dot_product = sum(a * b for a, b in zip(v1, v2))
    magnitude_1 = math.sqrt(sum(a * a for a in v1))
    magnitude_2 = math.sqrt(sum(a * a for a in v2))
    if not magnitude_1 or not magnitude_2:
        return 0.0
    return dot_product / (magnitude_1 * magnitude_2)

base_dir = os.path.dirname(os.path.abspath(__file__))
db_path = os.path.join(base_dir, "rag_store.db")
print("Resolved DB path:", db_path)
print("File exists:", os.path.exists(db_path))

try:
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    cursor.execute("SELECT filename, text_content, embedding FROM document_chunks")
    rows = cursor.fetchall()
    conn.close()
    print("Found rows in DB:", len(rows))
    
    query = "what is the delivery charge"
    resp = ollama.embeddings(model="nomic-embed-text", prompt=query)
    query_vector = resp.get("embedding")
    
    for filename, text, emb_json in rows:
        emb = json.loads(emb_json)
        score = cosine_similarity(query_vector, emb)
        print(f" -> File: {filename} | Score: {score:.4f}")
except Exception as e:
    print("Error:", e)
