import { Builder, By, Key, until } from "selenium-webdriver";
import "chromedriver";

async function searchFormInvalidDatesTest() {
  const driver = await new Builder().forBrowser("chrome").build();

  try {
    // --- Megnyitjuk a kereső oldalt ---
    await driver.get("http://172.16.13.18:3000/search");

    // --- Random város kiválasztása ---
    const cityInput = await driver.wait(until.elementLocated(By.id("city")), 5000);
    await cityInput.click();

    const options = await driver.wait(
      until.elementsLocated(By.css("#locations-list option")),
      5000
    );

    const values = [];
    for (let opt of options) {
      const val = await opt.getAttribute("value");
      if (val) values.push(val);
    }

    const randomCity = values[Math.floor(Math.random() * values.length)];
    await cityInput.sendKeys(randomCity, Key.TAB);
    console.log("✅ Random város kiválasztva:", randomCity);

    // --- Hibás dátumok (startDate > endDate) ---
    const today = new Date();
    const startDateObj = new Date(today.getFullYear(), today.getMonth() + 2, 20);
    const endDateObj = new Date(today.getFullYear(), today.getMonth() + 2, 10); // korábbi nap

    const formatDate = (d) => d.toISOString().split("T")[0];
    const startDate = formatDate(startDateObj);
    const endDate = formatDate(endDateObj);

    const startDateInput = await driver.findElement(By.id("startDate"));
    const endDateInput = await driver.findElement(By.id("endDate"));

    await startDateInput.clear();
    await startDateInput.sendKeys(startDate);
    await endDateInput.clear();
    await endDateInput.sendKeys(endDate);

    console.log("✅ Hibás dátumok beállítva:", startDate, "-", endDate);

    // --- Vendégek ---
    const guestsInput = await driver.findElement(By.id("guests"));
    await guestsInput.clear();
    await guestsInput.sendKeys("2");

    // --- Submit ---
    const submitBtn = await driver.findElement(By.css(".btn-search-booking"));
    await submitBtn.click();

    // --- HTML5 validáció üzenet lekérése ---
    const validationMessage = await endDateInput.getAttribute("validationMessage");
    if (validationMessage) {
      console.log("⚠️ Hibás dátum üzenet elkapva:", validationMessage);
      console.log("✅ Hibás dátum teszt PASS – form nem enged tovább");
    } else {
      console.log("❌ Hibás dátum teszt FAIL – nem kaptunk validációs üzenetet");
    }

  } catch (err) {
    console.log("❌ Hibás dátum teszt FAIL");
    console.error(err);
  } finally {
    await driver.quit();
  }
}

searchFormInvalidDatesTest();