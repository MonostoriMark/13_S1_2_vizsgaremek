import { Builder, By, until } from "selenium-webdriver";
import "chromedriver";

const ADMIN_EMAIL = "szabomate403@gmail.com";
const ADMIN_PASSWORD = "Gum55NDx";
const BASE_URL = "http://172.16.13.18:3000/admin/services";

async function runErrorTest() {
  const driver = await new Builder().forBrowser("chrome").build();

  try {
    // --- BEJELENTKEZÉS ---
    await driver.get(BASE_URL);
    await driver.wait(until.elementLocated(By.id("email")), 10000).sendKeys(ADMIN_EMAIL);
    await driver.findElement(By.id("password")).sendKeys(ADMIN_PASSWORD);
    await driver.findElement(By.css(".btn-login")).click();

    console.log("⚠️ 2FA manuális bevitel szükséges...");
    const twoFAInput = await driver.wait(until.elementLocated(By.css(".code-input")), 15000);
    await driver.wait(async () => {
      try { return !(await twoFAInput.isDisplayed()); } catch { return true; }
    }, 300000);

    await driver.get(BASE_URL);
    await driver.sleep(2000);

    // --- HIBAÜZENET TESZT ---
    console.log("\n🚀 'Hibaüzenet elkapása' teszt indítása...");

    const addButton = await driver.wait(until.elementLocated(By.id("add-service-btn")), 10000);
    await addButton.click();

    const modal = await driver.wait(until.elementLocated(By.css(".modal-content")), 5000);

    // Szálloda fix kiválasztása (hogy csak a név hiányozzon)
    const hotelSelect = await driver.wait(until.elementLocated(By.id("hotel-select")), 10000);
    await driver.wait(async () => {
      const options = await hotelSelect.findElements(By.css("option"));
      return options.length > 1;
    }, 10000);

    await driver.executeScript("arguments[0].selectedIndex = 1; arguments[0].dispatchEvent(new Event('change'));", hotelSelect);

    // Ár kitöltése, NÉV ÜRESEN HAGYÁSA
    await driver.findElement(By.id("service-price")).sendKeys("15");

    // Mentés megkísérlése
    await driver.findElement(By.id("service-submit-btn")).click();

    // Hibaüzenet elkapása ID alapján
    const errorBox = await driver.wait(until.elementLocated(By.id("service-error-msg")), 8000);
    const text = await errorBox.getText();
    
    if (text.includes("The name field is required")) {
      console.log("✅ Hibaüzenet sikeresen elkapva: " + text);
    } else {
      console.log("❌ Nem a várt hibaüzenet jelent meg: " + text);
    }

    // Modal bezárása
    const closeBtn = await driver.findElement(By.css(".modal-header .btn-close, .modal-close"));
    await closeBtn.click();
    await driver.wait(until.stalenessOf(modal), 5000);

  } catch (err) {
    console.error("\n❌ HIBA:", err.message);
  } finally {
    await driver.quit();
  }
}

runErrorTest();