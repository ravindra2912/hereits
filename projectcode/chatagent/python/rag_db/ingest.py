import os
import sqlite3
import json
import ollama
from pypdf import PdfReader

# Database path
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DB_PATH = os.path.join(BASE_DIR, "rag_store.db")
DOCS_DIR = os.path.join(BASE_DIR, "documents")
EMBEDDING_MODEL = "nomic-embed-text"

def init_db():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS document_chunks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT NOT NULL,
            chunk_index INTEGER NOT NULL,
            text_content TEXT NOT NULL,
            embedding TEXT NOT NULL
        )
    """)
    conn.commit()
    conn.close()

def chunk_text(text, chunk_size=800, overlap=150):
    chunks = []
    start = 0
    while start < len(text):
        end = min(start + chunk_size, len(text))
        chunks.append(text[start:end])
        if end == len(text):
            break
        start += chunk_size - overlap
    return chunks

def extract_text(file_path):
    ext = os.path.splitext(file_path)[1].lower()
    text = ""
    if ext == ".txt" or ext == ".md":
        with open(file_path, "r", encoding="utf-8", errors="ignore") as f:
            text = f.read()
    elif ext == ".pdf":
        try:
            reader = PdfReader(file_path)
            for page in reader.pages:
                page_text = page.extract_text()
                if page_text:
                    text += page_text + "\n"
        except Exception as e:
            print(f"Error parsing PDF {file_path}: {e}")
    return text.strip()

def run_ingestion():
    init_db()
    if not os.path.exists(DOCS_DIR):
        os.makedirs(DOCS_DIR)
        print(f"Created '{DOCS_DIR}' directory. Put documents (TXT, MD, PDF) there to index.")
        return

    files = [f for f in os.listdir(DOCS_DIR) if os.path.isfile(os.path.join(DOCS_DIR, f))]
    if not files:
        print(f"No documents found in '{DOCS_DIR}' directory. Put TXT, MD, or PDF files there.")
        return

    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    # Clear index for complete rebuild
    cursor.execute("DELETE FROM document_chunks")
    conn.commit()

    print(f"Found {len(files)} files to ingest.")
    
    total_chunks = 0
    for filename in files:
        file_path = os.path.join(DOCS_DIR, filename)
        print(f"Processing {filename}...")
        
        text = extract_text(file_path)
        if not text:
            print(f"Skipping {filename} (no text extracted).")
            continue
            
        chunks = chunk_text(text)
        print(f"Split {filename} into {len(chunks)} chunks. Generating embeddings...")
        
        for i, chunk in enumerate(chunks):
            try:
                # Generate embedding vector from Ollama
                response = ollama.embeddings(model=EMBEDDING_MODEL, prompt=chunk)
                vector = response.get("embedding")
                
                if vector:
                    # Save to database
                    cursor.execute(
                        "INSERT INTO document_chunks (filename, chunk_index, text_content, embedding) VALUES (?, ?, ?, ?)",
                        (filename, i, chunk, json.dumps(vector))
                    )
                    total_chunks += 1
                else:
                    print(f"Failed to generate embedding for {filename} chunk {i}")
            except Exception as e:
                print(f"Error generating embedding for {filename} chunk {i}: {e}")
                
        conn.commit()
        print(f"Finished {filename}")

    conn.close()
    print(f"Ingestion complete! Total indexed chunks: {total_chunks}")

if __name__ == "__main__":
    run_ingestion()
