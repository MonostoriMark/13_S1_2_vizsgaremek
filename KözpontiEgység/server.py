from fastapi import FastAPI
import DbFetcher

app = FastAPI()

@app.get("/38")
async def root():
    print("Végpont elérve: /38")
    await DbFetcher.fetchDb()
    return {"status": "ok", "message": "asd"}