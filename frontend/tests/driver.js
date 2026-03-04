import { Builder } from "selenium-webdriver";
import "chromedriver";

export async function createDriver() {
  return await new Builder().forBrowser("chrome").build();
}