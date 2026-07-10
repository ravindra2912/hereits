# chatbot.py
# FastAPI server to handle requests from the frontend chatbot widget
import uvicorn
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import List, Optional, Any

# Import the chatbot function from ragbot
from ragbot import ask_chatbot
import json
import re
from handler.supportTicketHandler import handle_support_ticket

# Initialize FastAPI application
app = FastAPI(
    title="RAG Chatbot API",
    description="Backend API serving Qwen 3.5 RAG requests from the chatbot widget",
    version="1.0.0"
)

# Enable CORS (Cross-Origin Resource Sharing) to allow requests from Laravel storefront / frontend origins
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # In production, specify your exact domain (e.g. http://localhost)
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Pydantic schemas for request validation
class ChatRequest(BaseModel):
    session_id: str
    prompt: str
    history: Optional[List[dict]] = None
    is_login: Optional[bool] = False
    user_info: Optional[Any] = None

# Status / Health check endpoint
@app.get("/api/status")
async def get_status():
    return {
        "status": "online",
        "message": "Chatbot backend server is active and running."
    }

# Health check endpoint requested by user
@app.get("/helth")
async def get_helth():
    return {
        "status": "online",
        "message": "Python is running"
    }

# Chat processing endpoint
@app.post("/api/chat")
async def chat_endpoint(request: ChatRequest):
    if not request.prompt.strip():
        raise HTTPException(status_code=400, detail="Prompt content cannot be empty.")
    if not request.session_id.strip():
        raise HTTPException(status_code=400, detail="Session ID cannot be empty.")
        
    try:
        session_id = request.session_id
        user_prompt = request.prompt
        
        # Process query directly via Ollama (passing prompt, session_id, and history)
        response_text, has_context = ask_chatbot(
            user_prompt,
            session_id=session_id,
            history=request.history,
            is_login=request.is_login,
            user_info=request.user_info
        )
        
        # Check if response is or contains a JSON block with type "storeTicket"
        possible_json = response_text
        if "```" in response_text:
            match = re.search(r"```(?:json)?\s*(.*?)\s*```", response_text, re.DOTALL)
            if match:
                possible_json = match.group(1).strip()
        
        try:
            parsed_res = json.loads(possible_json.strip())
            if isinstance(parsed_res, dict) and parsed_res.get("type") == "storeTicket":
                api_response = handle_support_ticket(parsed_res, request.user_info)
                
                # Pass the API response to Ollama to generate the final friendly response
                ollama_prompt = (
                    f"The support ticket submission API returned: {json.dumps(api_response)}. "
                    f"Please generate a friendly, natural response to the user informing them of the status. "
                    f"If successful, make sure to include the ticket number. If failed, explain the issue. "
                    f"Output ONLY the message to the user."
                )
                
                response_text, has_context = ask_chatbot(
                    ollama_prompt,
                    session_id=session_id,
                    history=request.history,
                    is_login=request.is_login,
                    user_info=request.user_info
                )
        except Exception:
            pass
            
        return {
            "response": response_text,
            "has_context": has_context
        }
        
    except Exception as e:
        print(f"[Error in Chat Endpoint]: {e}")
        raise HTTPException(status_code=500, detail=f"Internal Server Error: {str(e)}")

if __name__ == "__main__":
    # Start the Uvicorn server on port 8000
    uvicorn.run("chatbot:app", host="0.0.0.0", port=8000, reload=True)
