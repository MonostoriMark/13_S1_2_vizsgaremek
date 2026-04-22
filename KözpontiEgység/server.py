from fastapi import FastAPI
import DbFetcher

app = FastAPI()

@app.get("/1")
async def root():
    print("Végpont elérve: /1")
    await DbFetcher.fetchDb()
    return {"status": "ok", "message": "asd"}
