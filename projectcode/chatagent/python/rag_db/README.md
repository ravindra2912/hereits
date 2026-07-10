# Runing instuction
python chatbot.py (for run project)
ollama create hereits-chatbot -f modelfile (for create or update model)

# RAG Database and Document Management Instructions

This directory contains the knowledge base files and database for the RAG-based AI chatbot.

## Directory Structure
- `documents/`: Contains the text files (`about_us.txt`, `faq.txt`, `privacy_policy.txt`) representing the company knowledge base.
- `ingest.py`: Script to parse documents, compute embeddings via Ollama, and save them in the vector store database.
- `rag_store.db`: SQLite database containing the ingested document chunks and their high-dimensional vector embeddings.

---

## How to Manage Documents
1. Open any file in the `documents/` directory (e.g. `faq.txt` or `about_us.txt`) to edit facts, emails, delivery charges, or policies.
2. You can also add new files (such as `.txt`, `.md`, or `.pdf`) to the `documents/` directory to expand the chatbot's knowledge base.

---

## How to Synchronize Changes
Whenever you make any changes to files in the `documents/` folder, you **MUST** run the ingestion script to synchronize and re-index the database.

From the `chatagent/python/` directory, execute:
```bash
python rag_db\ingest.py
```

This will clear the existing index, generate fresh embeddings for all documents, and update `rag_store.db`.
