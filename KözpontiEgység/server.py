from fastapi import FastAPI
import DbFetcher

app = FastAPI()

@app.get("/")
def root():
    asd = DbFetcher.fetch_data_from_backend()
    return {"status": "ok", "message": asd}
