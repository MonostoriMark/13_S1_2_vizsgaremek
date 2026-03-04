import { Builder, By, until } from "selenium-webdriver";
import "chromedriver";
async function navigationTest() {
  const driver = await new Builder().forBrowser("chrome").build();

  try {
    // --- Megnyitjuk a főoldalt ---
    await driver.get("http://172.16.13.18:3000/");

    // --- Kattintás a "Regisztráció" gombra ---
    const signupBtn = await driver.wait(
      until.elementLocated(By.css(".btn-signup")),
      5000
    );
    await signupBtn.click();


    // --- Várjuk a /signup oldalra való átirányítást ---
    await driver.wait(
      until.urlIs("http://172.16.13.18:3000/register"),
      5000
    );
    console.log("✅ Navigációs teszt PASS - sikeres átirányítás a /register oldalra");

  } catch (err) {
    console.log("❌ Navigációs teszt FAIL");
    console.error(err);
  } finally {
    await driver.quit();
  }
}

navigationTest();