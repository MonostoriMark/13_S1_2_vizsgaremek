import { Builder, By, until } from "selenium-webdriver";
import "chromedriver";

async function loginFailRedirectTest() {
  const driver = await new Builder().forBrowser("chrome").build();

  try {
    // Login oldal
    await driver.get("http://172.16.13.18:3000/login");

    // Email és jelszó rosszul
    const emailInput = await driver.wait(until.elementLocated(By.id("email")), 5000);
    await emailInput.sendKeys("wrong@test.com");

    const passwordInput = await driver.findElement(By.id("password"));
    await passwordInput.sendKeys("wrongpassword");

    // Submit
    await driver.findElement(By.css("button[type='submit']")).click();

    // Rövid várakozás, hogy a router feldolgozza
    await driver.sleep(1000); // 1 másodperc

    // Ellenőrizzük az URL-t
    const currentUrl = await driver.getCurrentUrl();

    if (currentUrl.endsWith("/login")) {
      console.log("✅ Hibás login teszt PASS - maradtunk a /login oldalon");
    } else {
      console.log("❌ Hibás login teszt FAIL - átirányították:", currentUrl);
    }

  } catch (err) {
    console.log("❌ Hibás login teszt FAIL");
    console.error(err);
  } finally {
    await driver.quit();
  }
}

loginFailRedirectTest();