from fastapi import FastAPI
import DbFetcher

app = FastAPI()

@app.get("/44")
async def root():
    print("Végpont elérve: /44")
    await DbFetcher.fetchDb()
    return {"status": "ok", "message": "asd"}