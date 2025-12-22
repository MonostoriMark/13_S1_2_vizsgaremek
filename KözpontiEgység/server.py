from fastapi import FastAPI
import DbFetcher

app = FastAPI()

@app.get("/37")
async def root():
    print("Végpont elérve: /37")
    await DbFetcher.main()
    return {"status": "ok", "message": "asd"}