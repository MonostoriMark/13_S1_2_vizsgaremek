import { Builder, By, Key, until } from "selenium-webdriver";
import "chromedriver";

async function searchFormTest() {
  const driver = await new Builder().forBrowser("chrome").build();

  try {
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

    // --- Dátumok beállítása (2 hónap múlva, 2 nap különbséggel) ---
    const today = new Date();
    const startDateObj = new Date(today.getFullYear(), today.getMonth() + 2, Math.floor(Math.random() * 28) + 1);
    const endDateObj = new Date(startDateObj.getTime() + 2 * 24 * 60 * 60 * 1000);

    const formatDate = (d) => d.toISOString().split("T")[0];
    const startDate = formatDate(startDateObj);
    const endDate = formatDate(endDateObj);

    const startDateInput = await driver.findElement(By.id("startDate"));
    const endDateInput = await driver.findElement(By.id("endDate"));

    await startDateInput.clear();
    await startDateInput.sendKeys(startDate);
    await endDateInput.clear();
    await endDateInput.sendKeys(endDate);

    console.log("✅ Dátumok beállítva:", startDate, "-", endDate);

    // --- Vendégek ---
    const guestsInput = await driver.findElement(By.id("guests"));
    await guestsInput.clear();
    await guestsInput.sendKeys("2");

    // --- Submit ---
    const submitBtn = await driver.findElement(By.css(".btn-search-booking"));
    await submitBtn.click();

    // Rövid várakozás, hogy a submit lefusson
    await driver.sleep(1000);
    console.log("✅ Keresés futtatva (submit megtörtént).");

  } catch (err) {
    console.log("❌ Search form teszt FAIL");
    console.error(err);
  } finally {
    await driver.quit();
  }
}

searchFormTest();