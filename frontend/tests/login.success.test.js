import { Builder, By, until } from "selenium-webdriver";
import "chromedriver";

async function loginFullFlowTest() {
  const driver = await new Builder().forBrowser("chrome").build();

  try {
    // Login oldal
    await driver.get("http://172.16.13.18:3000/login");

    // ⏳ VÁRJUK MEG az email inputot
    const emailInput = await driver.wait(
      until.elementLocated(By.id("email")),
      10000
    );

    // Kitöltés
    await emailInput.sendKeys("szabo.mate@diak.szbi-pg.hu");

    const passwordInput = await driver.findElement(By.id("password"));
    await passwordInput.sendKeys("Gum55NDx");

    // Submit
    await driver.findElement(By.css("button[type='submit']")).click();

    // ⏳ 2FA popup várása
    const skipButton = await driver.wait(
      until.elementLocated(By.css(".btn-skip")),
      100000
    );

    // Biztos ami biztos látható is legyen
    await driver.wait(until.elementIsVisible(skipButton), 5000);

    // Kattintás
    await skipButton.click();

    // ⏳ Várunk a bookings oldalra
    await driver.wait(
      until.urlIs("http://172.16.13.18:3000/bookings"),
      10000
    );

    console.log("✅ Teljes login flow PASS");

  } catch (err) {
    console.log("❌ Teljes login flow FAIL");
    console.log(err);
  } finally {
    await driver.quit();
  }
}

loginFullFlowTest();