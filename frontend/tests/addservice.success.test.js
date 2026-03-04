import { Builder, By, until } from "selenium-webdriver";
import "chromedriver";

const ADMIN_EMAIL = "szabomate403@gmail.com";
const ADMIN_PASSWORD = "Gum55NDx";
const BASE_URL = "http://172.16.13.18:3000/admin/services";

async function runSuccessTest() {
  const driver = await new Builder().forBrowser("chrome").build();

  try {
    // --- BEJELENTKEZÉS ---
    await driver.get(BASE_URL);
    await driver.wait(until.elementLocated(By.id("email")), 10000).sendKeys(ADMIN_EMAIL);
    await driver.findElement(By.id("password")).sendKeys(ADMIN_PASSWORD);
    await driver.findElement(By.css(".btn-login")).click();

    console.log("⚠️ 2FA manuális bevitel szükséges a böngészőben...");
    const twoFAInput = await driver.wait(until.elementLocated(By.css(".code-input")), 15000);
    await driver.wait(async () => {
      try { return !(await twoFAInput.isDisplayed()); } catch { return true; }
    }, 300000);

    await driver.get(BASE_URL);
    await driver.sleep(2000);

    // --- SIKERES LÉTREHOZÁS TESZT ---
    console.log("\n🚀 'Sikeres létrehozás' teszt indítása...");

    const addButton = await driver.wait(until.elementLocated(By.id("add-service-btn")), 10000);
    await addButton.click();

    const modal = await driver.wait(until.elementLocated(By.css(".modal-content")), 5000);

    // Szálloda kiválasztása (ID alapján)
    const hotelSelect = await driver.wait(until.elementLocated(By.id("hotel-select")), 10000);
    await driver.wait(async () => {
      const options = await hotelSelect.findElements(By.css("option"));
      return options.length > 1;
    }, 10000);

    await driver.executeScript(`
      arguments[0].selectedIndex = 1;
      arguments[0].dispatchEvent(new Event('change', { bubbles: true }));
    `, hotelSelect);
    console.log("📍 Szálloda kiválasztva.");

    // Mezők kitöltése ID alapján
    await driver.findElement(By.id("service-name")).sendKeys("WiFi Szolgáltatás");
    await driver.findElement(By.id("service-description")).sendKeys("Stabil internet.");
    await driver.findElement(By.id("service-price")).sendKeys("0");

    // Mentés
    await driver.findElement(By.id("service-submit-btn")).click();

    await driver.wait(until.stalenessOf(modal), 10000);
    console.log("✅ Sikeres mentés teszt lefutott!");

  } catch (err) {
    console.error("\n❌ HIBA:", err.message);
  } finally {
    await driver.quit();
  }
}

runSuccessTest();