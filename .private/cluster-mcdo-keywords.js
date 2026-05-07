const fs = require("fs");
const path = require("path");

const INPUT_FILE =
  "C:/Users/muham/Downloads/mcdomenuusa.com-content-gap-subdomains-us_2026-05-05_15-12-44.csv";
const MENU_JSON = path.join(
  process.cwd(),
  "wp-content/themes/kadence/assets/data/mcprices-usa-menu.json",
);
const OUTPUT_DIR = path.join(
  process.cwd(),
  "outputs/keyword-clusters-2026-05-05",
);
const HOME_URL = "https://mcdomenuusa.com";

const SECTION_META = {
  kpop: {
    silo: "Limited Time",
    entityName: "KPop Demon Hunters Meal",
    guideUrl: `${HOME_URL}/limited-time-menu/`,
    categoryUrl: `${HOME_URL}/menu/whats-new/`,
    categorySlug: "whats-new",
    entityType: "limited_time_category",
  },
  bigarch: {
    silo: "Limited Time",
    entityName: "The BIG ARCH",
    guideUrl: `${HOME_URL}/limited-time-menu/`,
    categoryUrl: `${HOME_URL}/menu/whats-new/`,
    categorySlug: "whats-new",
    entityType: "limited_time_category",
  },
  evm: {
    silo: "Value & Deals",
    entityName: "Extra Value Meals",
    guideUrl: `${HOME_URL}/extra-value-meals/`,
    categoryUrl: `${HOME_URL}/menu/extra-value-meals/`,
    categorySlug: "extra-value-meals",
    entityType: "menu_guide",
  },
  mcvalue: {
    silo: "Value & Deals",
    entityName: "McValue Menu",
    guideUrl: `${HOME_URL}/mcdonalds-deals-mcvalue-guide/`,
    categoryUrl: `${HOME_URL}/menu/mcvalue-menu/`,
    categorySlug: "mcvalue-menu",
    entityType: "menu_guide",
  },
  bfast: {
    silo: "Breakfast",
    entityName: "Breakfast Menu",
    guideUrl: `${HOME_URL}/breakfast-menu/`,
    categoryUrl: `${HOME_URL}/menu/breakfast-menu/`,
    categorySlug: "breakfast-menu",
    entityType: "menu_guide",
  },
  burgers: {
    silo: "Burgers",
    entityName: "Burgers Menu",
    guideUrl: `${HOME_URL}/burgers-menu/`,
    categoryUrl: `${HOME_URL}/menu/burgers-menu/`,
    categorySlug: "burgers-menu",
    entityType: "menu_guide",
  },
  chicken: {
    silo: "Chicken & Fish",
    entityName: "Chicken & Fish Menu",
    guideUrl: `${HOME_URL}/chicken-fish-menu/`,
    categoryUrl: `${HOME_URL}/menu/chicken-fish/`,
    categorySlug: "chicken-fish",
    entityType: "menu_guide",
  },
  nuggets: {
    silo: "McNuggets & Strips",
    entityName: "McNuggets & Strips",
    guideUrl: `${HOME_URL}/nuggets-and-strips/`,
    categoryUrl: `${HOME_URL}/menu/mcnuggets-strips/`,
    categorySlug: "mcnuggets-strips",
    entityType: "menu_guide",
  },
  wrap: {
    silo: "Snack Wrap",
    entityName: "Snack Wrap",
    guideUrl: `${HOME_URL}/snack-wrap/`,
    categoryUrl: `${HOME_URL}/menu/snack-wrap/`,
    categorySlug: "snack-wrap",
    entityType: "menu_guide",
  },
  sides: {
    silo: "Fries & Sides",
    entityName: "Fries & Sides",
    guideUrl: `${HOME_URL}/fries-sides/`,
    categoryUrl: `${HOME_URL}/menu/fries-sides/`,
    categorySlug: "fries-sides",
    entityType: "menu_guide",
  },
  happy: {
    silo: "Happy Meal",
    entityName: "Happy Meal",
    guideUrl: `${HOME_URL}/happy-meal-menu/`,
    categoryUrl: `${HOME_URL}/menu/happy-meal/`,
    categorySlug: "happy-meal",
    entityType: "menu_guide",
  },
  sweets: {
    silo: "Sweets & Treats",
    entityName: "Sweets & Treats",
    guideUrl: `${HOME_URL}/sweets-treats/`,
    categoryUrl: `${HOME_URL}/menu/sweets-treats/`,
    categorySlug: "sweets-treats",
    entityType: "menu_guide",
  },
  coffee: {
    silo: "McCafe Coffees",
    entityName: "McCafe Coffees",
    guideUrl: `${HOME_URL}/mccafe-menu/`,
    categoryUrl: `${HOME_URL}/menu/mccafe-coffees/`,
    categorySlug: "mccafe-coffees",
    entityType: "menu_guide",
  },
  bev: {
    silo: "Beverages",
    entityName: "Beverages & Drinks",
    guideUrl: `${HOME_URL}/beverage-menu/`,
    categoryUrl: `${HOME_URL}/menu/beverages-drinks/`,
    categorySlug: "beverages-drinks",
    entityType: "menu_guide",
  },
  sauce: {
    silo: "Sauces & Condiments",
    entityName: "Sauces & Condiments",
    guideUrl: `${HOME_URL}/sauces-condiments/`,
    categoryUrl: `${HOME_URL}/menu/sauces-condiments/`,
    categorySlug: "sauces-condiments",
    entityType: "menu_guide",
  },
};

const SUPPORT_ENTITIES = [
  {
    id: "menu-hub",
    name: "McDonald's Menu",
    type: "menu_hub",
    silo: "Brand Core",
    parentId: "",
    parentName: "",
    url: `${HOME_URL}/menu/`,
    pageType: "hub",
    existingPage: true,
    aliases: [
      "mcdonalds menu",
      "mcdonald's menu",
      "mcdonald menu",
      "mcdonald's usa menu",
      "mcdonalds menu with prices",
      "mcdonald's menu with prices",
      "mcdonalds prices",
      "mcdonald's prices",
      "menu mcdonalds usa",
      "mcdonald menu prices",
    ],
  },
  {
    id: "breakfast-hours",
    name: "Breakfast Hours",
    type: "support_page",
    silo: "Breakfast",
    url: `${HOME_URL}/breakfast-hours/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "mcdonalds breakfast hours",
      "mcdonald's breakfast hours",
      "breakfast hours",
      "breakfast time",
      "breakfast times",
      "what time does mcdonalds stop serving breakfast",
      "when does mcdonalds stop serving breakfast",
      "all day breakfast",
      "mcdonalds breakfast until what time",
      "mcdonalds breakfast end time",
    ],
  },
  {
    id: "mcdvoice",
    name: "McDVoice Survey",
    type: "service_page",
    silo: "Customer Journey",
    url: `${HOME_URL}/mcdvoice/`,
    pageType: "service",
    existingPage: false,
    aliases: [
      "mcdvoice",
      "mcvoice",
      "mcdvoice.com",
      "mcdvoice.com survey",
      "mcdvoice survey",
      "mcdonald's survey",
      "mcdonalds survey",
      "survey code",
      "mcdvoice receipt code",
      "www mcdvoice com",
      "www.mcdvoice.com",
    ],
  },
  {
    id: "app-deals",
    name: "McDonald's App Deals",
    type: "service_page",
    silo: "Customer Journey",
    url: `${HOME_URL}/mcdonalds-app-deals/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "mcdonalds app",
      "mcdonald's app",
      "mcdonald app",
      "mcdonalds app deals",
      "mcdonald's app deals",
      "mcdonalds mobile app",
      "mcdonald's mobile app",
    ],
  },
  {
    id: "rewards-guide",
    name: "Rewards Guide",
    type: "service_page",
    silo: "Customer Journey",
    url: `${HOME_URL}/rewards-guide/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "mymcdonalds rewards",
      "mymcdonald's rewards",
      "mcdonalds rewards",
      "mcdonald's rewards",
      "mcdonalds app rewards",
      "mcdonald's app rewards",
      "mcdonalds rewards points",
      "mcdonald's rewards points",
      "mcdonalds rewards sign up",
    ],
  },
  {
    id: "mcdelivery-guide",
    name: "McDelivery Guide",
    type: "service_page",
    silo: "Customer Journey",
    url: `${HOME_URL}/mcdelivery-guide/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "mcdelivery",
      "mcdelivery menu",
      "mcdelivery cost",
      "mcdelivery fees",
      "delivery",
      "delivery guide",
    ],
  },
  {
    id: "nutrition-guide",
    name: "Nutrition, Calories & Allergens",
    type: "support_page",
    silo: "Nutrition",
    url: `${HOME_URL}/mcdonalds-nutrition-calories-allergens/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "mcdonalds nutrition",
      "mcdonald's nutrition",
      "mcdonalds calories",
      "mcdonald's calories",
      "mcdonalds allergens",
      "mcdonald's allergens",
      "mcdonalds allergen guide",
      "mcdonald's allergen guide",
      "mcdonalds ingredients",
      "mcdonald's ingredients",
      "mcdonalds vegan",
      "mcdonald's vegan",
      "mcdonalds vegetarian",
      "mcdonald's vegetarian",
    ],
  },
  {
    id: "calorie-counter",
    name: "Calorie Counter",
    type: "tool_page",
    silo: "Nutrition",
    url: `${HOME_URL}/calorie-counter/`,
    pageType: "tool",
    existingPage: true,
    aliases: [
      "calorie counter",
      "mcdonalds calorie counter",
      "mcdonald's calorie counter",
    ],
  },
  {
    id: "prices-by-state",
    name: "Prices by State",
    type: "support_page",
    silo: "Geographic Pricing",
    url: `${HOME_URL}/mcdonalds-prices-by-state/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "prices by state",
      "price by state",
      "big mac prices by state",
      "mcdonalds prices by state",
      "mcdonald's prices by state",
      "state prices",
    ],
  },
  {
    id: "big-mac-price-usa",
    name: "Big Mac Price USA",
    type: "support_page",
    silo: "Geographic Pricing",
    url: `${HOME_URL}/big-mac-price-usa/`,
    pageType: "support",
    existingPage: true,
    aliases: ["big mac price usa", "big mac price us", "how much is a big mac in the us"],
  },
  {
    id: "big-mac-price-uk",
    name: "Big Mac Price UK",
    type: "support_page",
    silo: "Geographic Pricing",
    url: `${HOME_URL}/big-mac-price-uk/`,
    pageType: "support",
    existingPage: true,
    aliases: ["big mac price uk"],
  },
  {
    id: "deals-mcvalue-guide",
    name: "Deals & McValue Guide",
    type: "support_page",
    silo: "Value & Deals",
    url: `${HOME_URL}/mcdonalds-deals-mcvalue-guide/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "mcdonalds value menu",
      "mcdonald's value menu",
      "mcdonalds dollar menu",
      "mcdonald's dollar menu",
      "dollar menu",
      "mcvalue",
      "meal deals",
      "mcdonalds deals",
      "mcdonald's deals",
    ],
  },
  {
    id: "limited-time-menu",
    name: "Limited Time Menu",
    type: "support_page",
    silo: "Limited Time",
    url: `${HOME_URL}/limited-time-menu/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "limited time menu",
      "what's new",
      "whats new",
      "new mcdonalds menu items",
      "new mcdonald's menu items",
      "new mcdonalds items",
      "mcdonalds new items",
    ],
  },
  {
    id: "vegan-options",
    name: "Vegan Options",
    type: "support_page",
    silo: "Nutrition",
    url: `${HOME_URL}/vegan-options/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "vegan options",
      "is mcdonalds vegan",
      "is mcdonald's vegan",
      "mcdonalds vegan options",
      "mcdonald's vegan options",
      "vegetarian options",
    ],
  },
  {
    id: "shareables-bundles",
    name: "Shareables & Bundles",
    type: "support_page",
    silo: "Value & Deals",
    url: `${HOME_URL}/shareables-bundles/`,
    pageType: "support",
    existingPage: true,
    aliases: [
      "shareables",
      "bundles",
      "family bundle",
      "shareable meal",
      "bundle deal",
      "bundle box",
      "family meal",
      "family box",
      "dinner box",
    ],
  },
];

const EXTRA_FAMILY_ENTITIES = [
  {
    id: "sweets-mcflurry",
    name: "McFlurry",
    type: "product_family",
    silo: "Sweets & Treats",
    parentId: "sweets-treats",
    parentName: "Sweets & Treats",
    url: `${HOME_URL}/sweets-treats/`,
    pageType: "family_section",
    existingPage: true,
    sectionId: "sweets",
    categoryUrl: `${HOME_URL}/menu/sweets-treats/`,
    aliases: ["mcflurry", "mc flurry", "mcdonalds mcflurry", "mcflurry sizes"],
  },
  {
    id: "coffee-frappe",
    name: "Frappe",
    type: "product_family",
    silo: "McCafe Coffees",
    parentId: "mccafe-coffees",
    parentName: "McCafe Coffees",
    url: `${HOME_URL}/mccafe-menu/`,
    pageType: "family_section",
    existingPage: true,
    sectionId: "coffee",
    categoryUrl: `${HOME_URL}/menu/mccafe-coffees/`,
    aliases: ["frappe", "mcdonalds frappe", "mcdonalds frappes"],
  },
  {
    id: "breakfast-mcgriddle",
    name: "McGriddle",
    type: "product_family",
    silo: "Breakfast",
    parentId: "breakfast-menu",
    parentName: "Breakfast Menu",
    url: `${HOME_URL}/breakfast-menu/`,
    pageType: "family_section",
    existingPage: true,
    sectionId: "bfast",
    categoryUrl: `${HOME_URL}/menu/breakfast-menu/`,
    aliases: ["mcgriddle", "mcgriddles", "mcdonalds mcgriddle"],
  },
  {
    id: "breakfast-mcmuffin",
    name: "McMuffin",
    type: "product_family",
    silo: "Breakfast",
    parentId: "breakfast-menu",
    parentName: "Breakfast Menu",
    url: `${HOME_URL}/breakfast-menu/`,
    pageType: "family_section",
    existingPage: true,
    sectionId: "bfast",
    categoryUrl: `${HOME_URL}/menu/breakfast-menu/`,
    aliases: ["mcmuffin", "mcmuffins", "mcdonalds mcmuffin"],
  },
  {
    id: "sweets-cookies",
    name: "Cookies",
    type: "product_family",
    silo: "Sweets & Treats",
    parentId: "sweets-treats",
    parentName: "Sweets & Treats",
    url: `${HOME_URL}/sweets-treats/`,
    pageType: "family_section",
    existingPage: true,
    sectionId: "sweets",
    categoryUrl: `${HOME_URL}/menu/sweets-treats/`,
    aliases: ["cookies", "mcdonalds cookies", "mcdonalds cookie"],
  },
  {
    id: "beverages-smoothie",
    name: "Smoothie",
    type: "product_family",
    silo: "Beverages",
    parentId: "beverages-drinks",
    parentName: "Beverages & Drinks",
    url: `${HOME_URL}/beverage-menu/`,
    pageType: "family_section",
    existingPage: true,
    sectionId: "bev",
    categoryUrl: `${HOME_URL}/menu/beverages-drinks/`,
    aliases: ["smoothie", "smoothies", "mcdonalds smoothie"],
  },
  {
    id: "limited-time-mcrib",
    name: "McRib",
    type: "product_family",
    silo: "Limited Time",
    parentId: "limited-time-menu",
    parentName: "Limited Time Menu",
    url: `${HOME_URL}/limited-time-menu/`,
    pageType: "family_section",
    existingPage: false,
    sectionId: "kpop",
    categoryUrl: `${HOME_URL}/menu/whats-new/`,
    aliases: ["mcrib", "mcdonalds mcrib", "mcdonald's mcrib"],
  },
];

const INTENT_RULES = [
  {
    detail: "comparison",
    intent: "comparison",
    pattern: /\b(vs|versus|difference|compare|comparison|better than|or)\b/,
  },
  {
    detail: "timing_hours",
    intent: "timing_availability",
    pattern:
      /\b(hours|hour|what time|when does|when do|stop serving|serve breakfast|until|closing time|open time|all day)\b/,
  },
  {
    detail: "availability_status",
    intent: "timing_availability",
    pattern:
      /\b(available|availability|back|returning|return|discontinued|gone|seasonal|limited time|release date)\b/,
  },
  {
    detail: "service_survey",
    intent: "service_task",
    pattern: /\b(mcdvoice|survey|receipt code|survey code|validation code)\b/,
  },
  {
    detail: "service_app",
    intent: "service_task",
    pattern: /\b(app|mobile app)\b/,
  },
  {
    detail: "service_rewards",
    intent: "service_task",
    pattern: /\b(reward|rewards|points)\b/,
  },
  {
    detail: "service_delivery",
    intent: "service_task",
    pattern: /\b(delivery|mcdelivery|uber eats|doordash|grubhub)\b/,
  },
  {
    detail: "nutrition_allergens",
    intent: "nutrition",
    pattern: /\b(allergen|allergens|gluten|vegan|vegetarian|halal|kosher)\b/,
  },
  {
    detail: "nutrition_ingredients",
    intent: "nutrition",
    pattern: /\b(ingredients|ingredient|what is in|contains|sauce|recipe)\b/,
  },
  {
    detail: "nutrition_caffeine",
    intent: "nutrition",
    pattern: /\b(caffeine|caffeinated)\b/,
  },
  {
    detail: "nutrition_macros",
    intent: "nutrition",
    pattern:
      /\b(calories|calorie|nutrition|protein|carbs|carbohydrates|fat|sugar|sodium|fiber|cholesterol)\b/,
  },
  {
    detail: "price_value",
    intent: "commercial",
    pattern:
      /\b(price|prices|cost|costs|how much|menu with prices|price list|value menu|dollar menu|deal|deals|meal deal|combo|bundle)\b/,
  },
  {
    detail: "geo_price",
    intent: "geo_pricing",
    pattern:
      /\b(usa|us|uk|california|texas|florida|new york|state|states|city)\b/,
  },
];

const US_STATES = [
  "alabama",
  "alaska",
  "arizona",
  "arkansas",
  "california",
  "colorado",
  "connecticut",
  "delaware",
  "florida",
  "georgia",
  "hawaii",
  "idaho",
  "illinois",
  "indiana",
  "iowa",
  "kansas",
  "kentucky",
  "louisiana",
  "maine",
  "maryland",
  "massachusetts",
  "michigan",
  "minnesota",
  "mississippi",
  "missouri",
  "montana",
  "nebraska",
  "nevada",
  "new hampshire",
  "new jersey",
  "new mexico",
  "new york",
  "north carolina",
  "north dakota",
  "ohio",
  "oklahoma",
  "oregon",
  "pennsylvania",
  "rhode island",
  "south carolina",
  "south dakota",
  "tennessee",
  "texas",
  "utah",
  "vermont",
  "virginia",
  "washington",
  "west virginia",
  "wisconsin",
  "wyoming",
  "district of columbia",
];

const COUNTRIES = ["usa", "us", "uk", "canada", "australia"];

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function stripQuotes(value) {
  return String(value || "").replace(/^"|"$/g, "");
}

function normalizeText(value) {
  return String(value || "")
    .normalize("NFKD")
    .replace(/\p{M}/gu, "")
    .toLowerCase()
    .replace(/[’']/g, "")
    .replace(/&/g, " and ")
    .replace(/[™®]/g, " ")
    .replace(/\btm\b/g, " ")
    .replace(/%/g, " percent ")
    .replace(/[()]/g, " ")
    .replace(/[^\p{L}\p{N}\s/-]+/gu, " ")
    .replace(/[-/]+/g, " ")
    .replace(/\s+/g, " ")
    .trim();
}

function normalizeForSlug(value) {
  return normalizeText(value)
    .replace(/\b(and|the)\b/g, " ")
    .replace(/\s+/g, " ")
    .trim();
}

function slugify(value) {
  return normalizeForSlug(value)
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function titleCase(value) {
  return String(value || "")
    .split(" ")
    .filter(Boolean)
    .map((word) => {
      if (word.toUpperCase() === word && word.length <= 4) {
        return word;
      }
      return word.charAt(0).toUpperCase() + word.slice(1);
    })
    .join(" ");
}

function csvEscape(value) {
  const text = value === null || value === undefined ? "" : String(value);
  if (/[",\n]/.test(text)) {
    return `"${text.replace(/"/g, '""')}"`;
  }
  return text;
}

function writeCsv(filePath, rows, columns) {
  const lines = [columns.map(csvEscape).join(",")];
  for (const row of rows) {
    lines.push(columns.map((column) => csvEscape(row[column])).join(","));
  }
  fs.writeFileSync(filePath, `${lines.join("\n")}\n`, "utf8");
}

function parseKeywordFile(filePath) {
  const raw = fs.readFileSync(filePath, "utf16le").replace(/^\uFEFF/, "");
  const lines = raw.split(/\r?\n/).filter(Boolean);
  const headers = lines[0].split("\t").map(stripQuotes);
  const rows = lines.slice(1).map((line) => {
    const cols = line.split("\t").map(stripQuotes);
    const row = {};
    headers.forEach((header, index) => {
      row[header] = cols[index] || "";
    });
    return row;
  });
  return { headers, rows };
}

function cleanItemName(name) {
  return String(name || "")
    .replace(/\s*\([^)]*\)\s*/g, " ")
    .replace(/\s+/g, " ")
    .trim();
}

function buildCategoryAliases(meta) {
  const base = meta.entityName;
  const aliases = new Set([
    base,
    base.replace(/\bMenu\b/i, "").trim(),
    `${base} prices`,
    `${base} menu`,
    `${base} menu prices`,
    `${base} with prices`,
  ]);
  const normalized = normalizeText(base);
  if (normalized.includes("breakfast")) {
    aliases.add("mcdonalds breakfast");
    aliases.add("mcdonalds breakfast menu");
    aliases.add("mcdonalds breakfast prices");
    aliases.add("desayunos mcdonalds usa");
    aliases.add("desayunos de mcdonalds usa");
    aliases.add("morning menu");
    aliases.add("breakfest menu");
  }
  if (normalized.includes("burgers")) {
    aliases.add("mcdonalds burgers");
    aliases.add("mcdonalds burgers menu");
  }
  if (normalized.includes("mccafe")) {
    aliases.add("mccafe");
    aliases.add("mc cafe");
    aliases.add("mcdonalds coffee");
    aliases.add("mcdonalds mccafe");
    aliases.add("mcdonalds coffee menu");
  }
  if (normalized.includes("beverages")) {
    aliases.add("mcdonalds drinks");
    aliases.add("mcdonalds beverage menu");
    aliases.add("mcdonalds drink menu");
    aliases.add("drinks");
    aliases.add("drink");
    aliases.add("fountain drinks");
    aliases.add("what drinks does mcdonalds have");
  }
  if (normalized.includes("fries")) {
    aliases.add("mcdonalds fries");
    aliases.add("mcdonalds sides");
    aliases.add("fries and sides");
  }
  if (normalized.includes("sauces")) {
    aliases.add("sauces");
    aliases.add("mcdonalds sauces");
    aliases.add("mcdonald sauces");
  }
  if (normalized.includes("extra value")) {
    aliases.add("value meals");
    aliases.add("combo meals");
  }
  if (normalized.includes("sweets")) {
    aliases.add("dessert menu");
    aliases.add("ice cream menu");
    aliases.add("soft serve");
    aliases.add("sundae");
  }
  if (normalized.includes("chicken and fish")) {
    aliases.add("chicken sandwich");
    aliases.add("fish sandwich");
    aliases.add("spicy chicken sandwich");
  }
  if (normalized.includes("mcnuggets")) {
    aliases.add("10 piece nugget");
    aliases.add("20 piece nugget");
    aliases.add("40 piece nugget");
    aliases.add("6 piece nugget");
  }
  if (normalized.includes("value")) {
    aliases.add("mcdonalds value menu");
    aliases.add("mcdonalds dollar menu");
    aliases.add("5 dollar meal");
    aliases.add("$5 meal");
  }
  if (normalized.includes("extra value")) {
    aliases.add("mcdonalds extra value meals");
  }
  return [...aliases];
}

function deriveFamilyName(name, sectionId) {
  let family = cleanItemName(name);
  family = family
    .replace(/\bSmall\b/gi, "")
    .replace(/\bMedium\b/gi, "")
    .replace(/\bLarge\b/gi, "")
    .replace(/\bMini\b/gi, "")
    .replace(/\bRegular\b/gi, "")
    .replace(/\bAny size\b/gi, "")
    .replace(/\bBottle\b/gi, "")
    .replace(/\bJug\b/gi, "")
    .replace(/\bSingle\b/gi, "")
    .replace(/\b2 Pack\b/gi, "")
    .replace(/\b\(1\)\b/gi, "")
    .replace(/\b\(2\)\b/gi, "")
    .replace(/\b\d+\s*pc\b/gi, "")
    .replace(/\b\d+\s*piece\b/gi, "")
    .replace(/\b\d+\s*pieces\b/gi, "")
    .replace(/\bmed\b/gi, "")
    .replace(/\bsm\b/gi, "")
    .replace(/\s+/g, " ")
    .trim()
    .replace(/[-,]\s*$/, "")
    .trim();

  if (sectionId === "nuggets" && /mcnuggets/i.test(name)) {
    return "Chicken McNuggets";
  }
  if (sectionId === "happy") {
    return "Happy Meal";
  }
  if (normalizeText(family) === "soft drink") {
    return "Soft Drink";
  }
  if (/world famous fries/i.test(name)) {
    return "World Famous Fries";
  }
  if (/oreo mcflurry/i.test(name)) {
    return "OREO McFlurry";
  }
  if (/m&m|mms/i.test(normalizeText(name)) && /mcflurry/i.test(name)) {
    return "M&M'S McFlurry";
  }
  if (/mccafe hot chocolate/i.test(name)) {
    return "McCafe Hot Chocolate";
  }
  return family;
}

function addAliasVariants(aliases, value) {
  const text = cleanItemName(value);
  const normalized = normalizeText(text);
  if (!normalized) {
    return;
  }
  aliases.add(text);
  aliases.add(normalized);
  aliases.add(text.replace(/\bMcCafe\b/i, "").trim());
  aliases.add(text.replace(/\bThe\b/i, "").trim());
  aliases.add(text.replace(/\bwith\b/gi, "w").replace(/\s+/g, " ").trim());
  aliases.add(text.replace(/\bwith\b/gi, "").replace(/\s+/g, " ").trim());
  aliases.add(text.replace(/Mc/g, "Mc ").replace(/\s+/g, " ").trim());

  const base = normalizeText(text);
  if (base.includes("mcdouble")) {
    aliases.add("mc double");
  }
  if (base.includes("mcchicken")) {
    aliases.add("mc chicken");
  }
  if (base.includes("filet o fish")) {
    aliases.add("filet-o-fish");
    aliases.add("filet of fish");
  }
  if (base.includes("mcnuggets")) {
    aliases.add(text.replace(/chicken /i, ""));
    aliases.add("nuggets");
  }
  if (base.includes("world famous fries")) {
    aliases.add("fries");
    aliases.add("french fries");
  }
  if (base.includes("hash browns")) {
    aliases.add("hash brown");
  }
  if (base.includes("sweet n sour")) {
    aliases.add("sweet and sour sauce");
  }
  if (base.includes("barbecue sauce")) {
    aliases.add("bbq sauce");
  }
  if (base.includes("m and ms mcflurry") || base.includes("mms mcflurry")) {
    aliases.add("m&m mcflurry");
    aliases.add("m and m mcflurry");
    aliases.add("mms mcflurry");
  }
  if (base.includes("oreo mcflurry")) {
    aliases.add("mcflurry oreo");
  }
  if (base.includes("caramel frappe")) {
    aliases.add("caramel frappe");
  }
  if (base.includes("mocha frappe")) {
    aliases.add("mocha frappe");
  }
  if (base.includes("iced coffee")) {
    aliases.add("iced coffee");
  }
  if (base.includes("premium roast coffee")) {
    aliases.add("mcdonalds coffee");
    aliases.add("hot coffee");
  }
  if (base.includes("soft drink")) {
    aliases.add("coke");
    aliases.add("coca cola");
    aliases.add("sprite");
    aliases.add("dr pepper");
    aliases.add("fanta");
    aliases.add("diet coke");
    aliases.add("hi c");
    aliases.add("hic");
  }
}

function buildItemAliases(name, familyName) {
  const aliases = new Set();
  addAliasVariants(aliases, name);
  const normalized = normalizeText(name);

  const countMatch = normalized.match(/\b(\d+)\s*pc\b/) || normalized.match(/\b(\d+)\s*piece\b/);
  if (countMatch) {
    const count = countMatch[1];
    if (/mcnuggets/.test(normalized)) {
      aliases.add(`${count} piece mcnugget`);
      aliases.add(`${count} piece mcnuggets`);
      aliases.add(`${count} piece chicken mcnuggets`);
      aliases.add(`${count} piece chicken nuggets`);
      aliases.add(`${count} pc nuggets`);
      aliases.add(`${count} nuggets`);
    }
    if (/happy meal/.test(normalized)) {
      aliases.add(`${count} piece happy meal`);
      aliases.add(`${count} pc happy meal`);
    }
  }

  if (/small/.test(normalized) && /fries/.test(normalized)) {
    aliases.add("small fry");
    aliases.add("small fries");
  }
  if (/medium/.test(normalized) && /fries/.test(normalized)) {
    aliases.add("medium fry");
    aliases.add("medium fries");
  }
  if (/large/.test(normalized) && /fries/.test(normalized)) {
    aliases.add("large fry");
    aliases.add("large fries");
  }

  return [...aliases]
    .map((alias) => normalizeText(alias))
    .filter(Boolean);
}

function buildManualFamilyAliases(entityName, sectionId) {
  const aliases = new Set([entityName]);
  const normalized = normalizeText(entityName);

  if (normalized === "breakfast menu") {
    aliases.add("breakfast");
  }
  if (normalized === "world famous fries") {
    aliases.add("fries");
    aliases.add("mcdonalds fries");
    aliases.add("mcdonalds french fries");
  }
  if (normalized === "quarter pounder with cheese") {
    aliases.add("quarter pounder");
  }
  if (normalized === "double quarter pounder with cheese") {
    aliases.add("double quarter pounder");
  }
  if (normalized === "quarter pounder with cheese deluxe") {
    aliases.add("quarter pounder deluxe");
  }
  if (normalized === "chicken mcnuggets") {
    aliases.add("mcnuggets");
    aliases.add("chicken nuggets");
    aliases.add("nuggets");
    aliases.add("mcnugget");
  }
  if (normalized === "soft drink") {
    aliases.add("coke");
    aliases.add("sprite");
    aliases.add("dr pepper");
    aliases.add("fanta");
    aliases.add("diet coke");
    aliases.add("hi c");
  }
  if (normalized === "happy meal") {
    aliases.add("happy meals");
    aliases.add("kids meal");
  }
  if (normalized === "fruit and maple oatmeal") {
    aliases.add("oatmeal");
    aliases.add("mcdonalds oatmeal");
  }
  if (normalized === "vanilla cone") {
    aliases.add("ice cream cone");
    aliases.add("vanilla ice cream cone");
  }
  if (normalized === "mccafe hot chocolate") {
    aliases.add("hot cocoa");
    aliases.add("hot coco");
    aliases.add("hot chocolate");
  }
  if (normalized === "mccrispy strips") {
    aliases.add("chicken strips");
    aliases.add("mcdonalds chicken strips");
  }
  if (normalized === "filet o fish") {
    aliases.add("fish sandwich");
    aliases.add("filet fish");
  }
  if (normalized === "hash browns") {
    aliases.add("hashbrown");
    aliases.add("hashbrowns");
  }
  if (normalized === "big mac") {
    aliases.add("bigmac");
  }
  if (normalized === "mcflurry") {
    aliases.add("mc flurry");
  }
  if (normalized === "baked apple pie") {
    aliases.add("apple pie");
  }
  if (normalized === "spicy buffalo sauce") {
    aliases.add("buffalo sauce");
  }
  if (normalized === "creamy ranch sauce") {
    aliases.add("ranch");
    aliases.add("ranch sauce");
  }
  if (normalized === "unsweetened iced tea") {
    aliases.add("unsweet tea");
    aliases.add("unsweetened tea");
  }
  if (normalized === "minute maid premium orange juice") {
    aliases.add("orange juice");
  }
  if (normalized === "reduced sugar low fat chocolate milk" || normalized === "1 low fat milk") {
    aliases.add("chocolate milk");
    aliases.add("milk");
  }
  if (normalized === "sausage burrito") {
    aliases.add("burrito");
    aliases.add("burritos");
  }
  if (normalized === "hotcakes") {
    aliases.add("hot cakes");
    aliases.add("pancakes");
  }
  if (normalized === "hotcakes and sausage") {
    aliases.add("pancakes");
  }
  if (normalized === "oreo mcflurry") {
    aliases.add("mcflurry");
  }
  if (normalized === "m and ms mcflurry" || normalized === "m and ms mcflurry regular") {
    aliases.add("mcflurry");
  }
  if (normalized.includes("mcgriddles")) {
    aliases.add(normalized.replace("mcgriddles", "mcgriddle"));
  }
  if (normalized.includes("mcmuffin with egg")) {
    aliases.add(normalized.replace("with egg", "and cheese"));
    aliases.add(normalized.replace("with egg", "egg and cheese"));
  }
  if (normalized.includes("bagel")) {
    aliases.add(normalized.replace(/,/g, ""));
  }
  if (normalized === "premium roast coffee") {
    aliases.add("mcdonalds coffee");
    aliases.add("mcdonalds hot coffee");
  }
  if (normalized.includes("mcflurry")) {
    aliases.add("mc flurry");
  }
  if (normalized.includes("filet o fish")) {
    aliases.add("filet of fish");
  }
  if (normalized.includes("mcchicken")) {
    aliases.add("mc chicken");
  }
  if (normalized.includes("mcdouble")) {
    aliases.add("mc double");
  }
  if (normalized.includes("mccafe")) {
    aliases.add(normalized.replace("mccafe ", ""));
  }
  if (sectionId === "bev" && normalized.includes("smoothie")) {
    aliases.add("smoothies");
  }
  if (sectionId === "coffee" && normalized.includes("frappe")) {
    aliases.add("frappe");
    aliases.add("mcdonalds frappe");
  }
  if (sectionId === "sweets" && normalized.includes("cookie")) {
    aliases.add("cookies");
    aliases.add("mcdonalds cookies");
  }
  if (sectionId === "bev" && normalized.includes("frozen")) {
    aliases.add("slushie");
    aliases.add("slushies");
    aliases.add("frozen drinks");
  }
  if (sectionId === "sweets" && normalized.includes("cone")) {
    aliases.add("cone");
  }
  return [...aliases];
}

function buildEntityCatalog(menuData) {
  const entities = new Map();

  function addEntity(config) {
    const id = config.id || slugify(config.name);
    const existing = entities.get(id);
    const aliases = new Set((existing && existing.aliases) || []);
    for (const alias of config.aliases || []) {
      const normalized = normalizeText(alias);
      if (normalized) {
        aliases.add(normalized);
      }
    }
    const entity = {
      id,
      name: config.name,
      type: config.type,
      silo: config.silo,
      parentId: config.parentId || "",
      parentName: config.parentName || "",
      url: config.url || "",
      pageType: config.pageType || "",
      existingPage: config.existingPage !== false,
      sectionId: config.sectionId || "",
      categoryUrl: config.categoryUrl || "",
      aliases: [...aliases],
    };
    entities.set(id, entity);
    return entity;
  }

  for (const support of SUPPORT_ENTITIES) {
    addEntity(support);
  }

  for (const [sectionId, meta] of Object.entries(SECTION_META)) {
    addEntity({
      id: slugify(meta.entityName),
      name: meta.entityName,
      type: meta.entityType,
      silo: meta.silo,
      parentId: "",
      parentName: "",
      url: meta.guideUrl,
      pageType: "guide",
      existingPage: true,
      sectionId,
      categoryUrl: meta.categoryUrl,
      aliases: buildCategoryAliases(meta),
    });
  }

  for (const extra of EXTRA_FAMILY_ENTITIES) {
    addEntity(extra);
  }

  for (const section of menuData) {
    const meta = SECTION_META[section.id];
    if (!meta) {
      continue;
    }
    for (const sub of section.subs || []) {
      for (const row of sub.rows || []) {
        const cleanName = cleanItemName(row.name);
        const familyName = deriveFamilyName(row.name, section.id);
        const familyId = slugify(`${section.id}-${familyName}`);
        const familyUrl =
          familyName === cleanName
            ? `${meta.categoryUrl}${slugify(cleanName)}/`
            : meta.guideUrl;

        addEntity({
          id: familyId,
          name: familyName,
          type: familyName === cleanName ? "product_variant" : "product_family",
          silo: meta.silo,
          parentId: slugify(meta.entityName),
          parentName: meta.entityName,
          url: familyUrl,
          pageType: familyName === cleanName ? "item" : "family_section",
          existingPage: true,
          sectionId: section.id,
          categoryUrl: meta.categoryUrl,
          aliases: buildManualFamilyAliases(familyName, section.id),
        });

        if (familyName !== cleanName) {
          addEntity({
            id: slugify(`${section.id}-${cleanName}`),
            name: cleanName,
            type: "product_variant",
            silo: meta.silo,
            parentId: familyId,
            parentName: familyName,
            url: `${meta.categoryUrl}${slugify(cleanName)}/`,
            pageType: "item",
            existingPage: true,
            sectionId: section.id,
            categoryUrl: meta.categoryUrl,
            aliases: buildItemAliases(cleanName, familyName),
          });
        } else {
          addEntity({
            id: familyId,
            name: cleanName,
            type: "product_variant",
            silo: meta.silo,
            parentId: slugify(meta.entityName),
            parentName: meta.entityName,
            url: `${meta.categoryUrl}${slugify(cleanName)}/`,
            pageType: "item",
            existingPage: true,
            sectionId: section.id,
            categoryUrl: meta.categoryUrl,
            aliases: buildItemAliases(cleanName, familyName),
          });
        }
      }
    }
  }

  return [...entities.values()];
}

function compileAliasIndex(entities) {
  const index = [];
  for (const entity of entities) {
    for (const alias of entity.aliases) {
      if (!alias || alias.length < 2) {
        continue;
      }
      index.push({
        entityId: entity.id,
        alias,
        words: alias.split(" ").length,
        length: alias.length,
      });
    }
  }
  index.sort((a, b) => {
    if (b.words !== a.words) return b.words - a.words;
    return b.length - a.length;
  });
  return index;
}

function containsAlias(keywordNorm, aliasNorm) {
  return (
    keywordNorm === aliasNorm ||
    keywordNorm.startsWith(`${aliasNorm} `) ||
    keywordNorm.endsWith(` ${aliasNorm}`) ||
    keywordNorm.includes(` ${aliasNorm} `)
  );
}

function findEntityMatches(keywordNorm, aliasIndex, entityMap) {
  const bestById = new Map();
  const bestByName = new Map();
  const seen = new Set();
  for (const entry of aliasIndex) {
    if (!containsAlias(keywordNorm, entry.alias)) {
      continue;
    }
    if (seen.has(entry.entityId)) {
      continue;
    }
    seen.add(entry.entityId);
    const entity = entityMap.get(entry.entityId);
    const position = keywordNorm.indexOf(entry.alias);
    let score = entry.words * 100 + entry.length;
    if (keywordNorm === entry.alias) score += 200;
    if (entity.type === "service_page" || entity.type === "tool_page") score += 15;
    if (entity.type === "product_variant") score += 10;
    if (entity.silo === "Value & Deals" && entity.type === "product_variant") score -= 5;
    const candidate = { entity, alias: entry.alias, position, score };
    bestById.set(entry.entityId, candidate);
  }

  for (const candidate of bestById.values()) {
    const key = normalizeText(candidate.entity.name);
    const current = bestByName.get(key);
    if (
      !current ||
      candidate.score > current.score ||
      (candidate.score === current.score && candidate.position < current.position)
    ) {
      bestByName.set(key, candidate);
    }
  }

  const matches = [...bestByName.values()];
  matches.sort((a, b) => {
    if (b.score !== a.score) return b.score - a.score;
    return a.position - b.position;
  });
  return matches;
}

function detectGeo(keywordNorm) {
  for (const country of COUNTRIES) {
    if (containsAlias(keywordNorm, country)) {
      return titleCase(country);
    }
  }
  for (const state of US_STATES) {
    if (containsAlias(keywordNorm, state)) {
      return titleCase(state);
    }
  }
  if (keywordNorm.includes("new york city")) {
    return "New York City";
  }
  return "";
}

function classifyIntent(keywordNorm) {
  for (const rule of INTENT_RULES) {
    if (rule.pattern.test(keywordNorm)) {
      return { intent: rule.intent, detail: rule.detail };
    }
  }
  if (/\b(menu|items|list|breakfast|coffee|drinks|burger|meal|happy meal)\b/.test(keywordNorm)) {
    return { intent: "overview", detail: "overview_menu" };
  }
  return { intent: "overview", detail: "overview_general" };
}

function stripYears(keywordNorm) {
  return keywordNorm.replace(/\b20(2[0-9]|3[0-9])\b/g, "").replace(/\s+/g, " ").trim();
}

function extractComparisonSides(keywordNorm) {
  let match = keywordNorm.match(/^(.*?)\bvs\b(.*)$/);
  if (match) {
    return [match[1].trim(), match[2].trim()];
  }
  match = keywordNorm.match(/^(.*?)\bversus\b(.*)$/);
  if (match) {
    return [match[1].trim(), match[2].trim()];
  }
  match = keywordNorm.match(/difference between (.*?) and (.*)$/);
  if (match) {
    return [match[1].trim(), match[2].trim()];
  }
  match = keywordNorm.match(/difference (.*?) and (.*)$/);
  if (match) {
    return [match[1].trim(), match[2].trim()];
  }
  return null;
}

function choosePrimaryEntity(keywordNorm, matches, intentDetail, geo, entityMap) {
  const byId = (id) => entityMap.get(id) || null;

  if (intentDetail === "service_survey") {
    return matches.find((match) => match.entity.id === "mcdvoice")?.entity || byId("mcdvoice");
  }
  if (intentDetail === "service_app") {
    return matches.find((match) => match.entity.id === "app-deals")?.entity || byId("app-deals");
  }
  if (intentDetail === "service_rewards") {
    return (
      matches.find((match) => match.entity.id === "rewards-guide")?.entity ||
      byId("rewards-guide")
    );
  }
  if (intentDetail === "service_delivery") {
    return (
      matches.find((match) => match.entity.id === "mcdelivery-guide")?.entity ||
      byId("mcdelivery-guide")
    );
  }
  if (intentDetail === "timing_hours" && keywordNorm.includes("breakfast")) {
    return (
      matches.find((match) => match.entity.id === "breakfast-hours")?.entity ||
      byId("breakfast-hours")
    );
  }
  if (intentDetail === "geo_price" && keywordNorm.includes("big mac") && geo === "Usa") {
    return (
      matches.find((match) => match.entity.id === "big-mac-price-usa")?.entity ||
      byId("big-mac-price-usa")
    );
  }
  if (intentDetail === "geo_price" && keywordNorm.includes("big mac") && geo === "Uk") {
    return (
      matches.find((match) => match.entity.id === "big-mac-price-uk")?.entity ||
      byId("big-mac-price-uk")
    );
  }
  if (matches.length) {
    return matches[0].entity;
  }
  if (keywordNorm.includes("oatmeal")) return byId("bfast-fruit-and-maple-oatmeal");
  if (keywordNorm.includes("apple pie")) return byId("sweets-baked-apple-pie");
  if (keywordNorm.includes("fish fillet") || keywordNorm.includes("fish filet") || keywordNorm.includes("filet fish")) {
    return byId("chicken-filet-o-fish");
  }
  if (keywordNorm.includes("mcchickens") || keywordNorm.includes("mac chicken") || keywordNorm.includes("mcchiken")) {
    return byId("chicken-mcchicken");
  }
  if (keywordNorm.includes("mcdoubles")) {
    return byId("burgers-mcdouble");
  }
  if (keywordNorm.includes("mcflurries")) {
    return byId("sweets-mcflurry");
  }
  if (keywordNorm.includes("qpc")) {
    return byId("burgers-quarter-pounder-with-cheese");
  }
  if (keywordNorm.includes("chicken nugget") || keywordNorm.includes("nugget")) {
    return byId("nuggets-chicken-mcnuggets");
  }
  if (keywordNorm.includes("mcvoice") || keywordNorm.includes("voice")) {
    return byId("mcdvoice");
  }
  if (keywordNorm.includes("frappuccino") || keywordNorm.includes("mcfrappe")) {
    return byId("coffee-frappe");
  }
  if (keywordNorm.includes("ice cream") || keywordNorm.includes("soft serve") || keywordNorm.includes("softserve")) {
    return byId("sweets-treats");
  }
  if (keywordNorm.includes("milkshake")) {
    return byId("sweets-treats");
  }
  if (keywordNorm.includes("coffee")) {
    return byId("mccafe-coffees");
  }
  if (keywordNorm.includes("shamrock shake")) {
    return byId("limited-time-menu");
  }
  if (keywordNorm.includes("bundle") || keywordNorm.includes("family pack") || keywordNorm.includes("box")) {
    return byId("shareables-bundles");
  }
  if (keywordNorm.includes("tea") || keywordNorm.includes("orange juice") || keywordNorm.includes("milk")) {
    return byId("beverages-drinks");
  }
  if (keywordNorm.includes("biscuit") || keywordNorm.includes("burrito") || keywordNorm.includes("hot cakes")) {
    return byId("breakfast-menu");
  }
  if (keywordNorm.includes("sandwich")) {
    return byId("chicken-fish-menu");
  }
  if (keywordNorm.includes("breakfast")) return byId("breakfast-hours");
  if (keywordNorm.includes("mcdvoice") || keywordNorm.includes("survey")) {
    return byId("mcdvoice");
  }
  if (keywordNorm.includes("reward")) return byId("rewards-guide");
  if (keywordNorm.includes("delivery")) return byId("mcdelivery-guide");
  if (keywordNorm.includes("allergen") || keywordNorm.includes("vegan") || keywordNorm.includes("nutrition")) {
    return byId("nutrition-guide");
  }
  if (keywordNorm.includes("mcvalue") || keywordNorm.includes("dollar menu") || keywordNorm.includes("meal deal")) {
    return byId("deals-mcvalue-guide");
  }
  return byId("menu-hub");
}

function buildComparisonLabel(matchA, matchB) {
  const ordered = [matchA.entity.name, matchB.entity.name].sort((a, b) =>
    a.localeCompare(b),
  );
  return `${ordered[0]} vs ${ordered[1]}`;
}

function recommendPage(primaryEntity, intentDetail, geo, comparisonLabel) {
  if (comparisonLabel) {
    return {
      recommendation: "Create a comparison page or dedicated comparison section.",
      suggestedUrl: "",
      pageType: "comparison",
    };
  }
  if (!primaryEntity) {
    return {
      recommendation: "Review manually; entity was not confidently detected.",
      suggestedUrl: `${HOME_URL}/menu/`,
      pageType: "manual_review",
    };
  }
  if (geo && intentDetail === "geo_price") {
    if (primaryEntity.url && /big-mac-price-(usa|uk)/.test(primaryEntity.url)) {
      return {
        recommendation: "Optimize the existing geo-specific pricing page.",
        suggestedUrl: primaryEntity.url,
        pageType: "support",
      };
    }
    return {
      recommendation:
        "Route this to the geographic pricing hub or create a state/city-specific expansion page.",
      suggestedUrl: `${HOME_URL}/mcdonalds-prices-by-state/`,
      pageType: "support",
    };
  }
  if (primaryEntity.type === "product_variant") {
    return {
      recommendation: "Optimize the existing item page and strengthen price/nutrition sections.",
      suggestedUrl: primaryEntity.url,
      pageType: "item",
    };
  }
  if (primaryEntity.type === "product_family") {
    return {
      recommendation:
        "Expand the parent category guide with a dedicated family section or create a focused family guide.",
      suggestedUrl: primaryEntity.url || primaryEntity.categoryUrl || "",
      pageType: "family_section",
    };
  }
  if (primaryEntity.type === "service_page" || primaryEntity.type === "tool_page") {
    return {
      recommendation: primaryEntity.existingPage
        ? "Optimize the existing support page."
        : "Create a new support page and interlink it from the main menu hub.",
      suggestedUrl: primaryEntity.url,
      pageType: primaryEntity.pageType || "support",
    };
  }
  return {
    recommendation: "Optimize the existing guide page and strengthen entity coverage.",
    suggestedUrl: primaryEntity.url || "",
    pageType: primaryEntity.pageType || "guide",
  };
}

function clusterKeywords(rows, entityCatalog) {
  const entityMap = new Map(entityCatalog.map((entity) => [entity.id, entity]));
  const aliasIndex = compileAliasIndex(entityCatalog);

  const clustered = rows.map((row) => {
    const keyword = row["Keyword"];
    const keywordNorm = stripYears(normalizeText(keyword));
    const matches = findEntityMatches(keywordNorm, aliasIndex, entityMap);
    const geo = detectGeo(keywordNorm);
    let { intent, detail } = classifyIntent(keywordNorm);
    if (geo && detail === "price_value") {
      intent = "geo_pricing";
      detail = "geo_price";
    }

    let comparisonLabel = "";
    let comparisonA = "";
    let comparisonB = "";
    if (detail === "comparison") {
      const sides = extractComparisonSides(keywordNorm);
      let distinctMatches = [];
      if (sides) {
        const leftMatches = findEntityMatches(sides[0], aliasIndex, entityMap);
        const rightMatches = findEntityMatches(sides[1], aliasIndex, entityMap);
        if (leftMatches.length && rightMatches.length) {
          distinctMatches = [leftMatches[0], rightMatches[0]].filter(
            (match, index, all) =>
              all.findIndex((entry) => normalizeText(entry.entity.name) === normalizeText(match.entity.name)) === index,
          );
        }
      }
      if (distinctMatches.length < 2) {
        const seenNames = new Set();
        for (const match of matches) {
          const key = normalizeText(match.entity.name);
          if (seenNames.has(key)) {
            continue;
          }
          seenNames.add(key);
          distinctMatches.push(match);
          if (distinctMatches.length === 2) {
            break;
          }
        }
      }
      if (distinctMatches.length >= 2) {
        comparisonLabel = buildComparisonLabel(distinctMatches[0], distinctMatches[1]);
        comparisonA = distinctMatches[0].entity.name;
        comparisonB = distinctMatches[1].entity.name;
      }
    }

    let primaryEntity = choosePrimaryEntity(keywordNorm, matches, detail, geo, entityMap);

    if (!primaryEntity && matches.length) {
      primaryEntity = matches[0].entity;
    }

    if (!primaryEntity) {
      primaryEntity = entityMap.get("menu-hub");
    }

    const clusterKey = comparisonLabel
      ? `comparison__${slugify(comparisonLabel)}`
      : `${primaryEntity.id}__${detail}${geo ? `__${slugify(geo)}` : ""}`;

    const clusterLabel = comparisonLabel
      ? comparisonLabel
      : `${primaryEntity.name} - ${detail.replace(/_/g, " ")}`;

    const page = recommendPage(primaryEntity, detail, geo, comparisonLabel);

    return {
      keyword,
      keyword_norm: keywordNorm,
      volume: Number(row["Volume"] || 0),
      kd: Number(row["KD"] || 0),
      cpc: Number(row["CPC"] || 0),
      serp_features: row["SERP features"] || "",
      current_url: row["mcdomenuusa.com/: URL"] || "",
      current_position: row["mcdomenuusa.com/: Organic Position"] || "",
      current_traffic: row["mcdomenuusa.com/: Organic Traffic"] || "",
      competitor_url: row["www.mac-menus.com/: URL"] || "",
      updated: row["Updated"] || "",
      topical_silo: primaryEntity.silo || "Brand Core",
      primary_entity: comparisonLabel || primaryEntity.name,
      entity_id: primaryEntity.id,
      entity_type: comparisonLabel ? "comparison" : primaryEntity.type || "support_page",
      parent_entity: primaryEntity.parentName || "",
      intent,
      intent_detail: detail,
      geo_modifier: geo,
      comparison_entity_a: comparisonA,
      comparison_entity_b: comparisonB,
      cluster_key: clusterKey,
      cluster_label: clusterLabel,
      recommended_page_type: page.pageType,
      recommended_action: page.recommendation,
      suggested_url: page.suggestedUrl,
      entity_existing_page: primaryEntity.existingPage === false ? "No" : "Yes",
      needs_manual_review: primaryEntity.id === "menu-hub" && matches.length === 0 ? "Yes" : "No",
    };
  });

  const clusterStats = new Map();
  for (const row of clustered) {
    const stat = clusterStats.get(row.cluster_key) || {
      cluster_key: row.cluster_key,
      cluster_label: row.cluster_label,
      topical_silo: row.topical_silo,
      primary_entity: row.primary_entity,
      entity_type: row.entity_type,
      parent_entity: row.parent_entity,
      intent: row.intent,
      intent_detail: row.intent_detail,
      geo_modifier: row.geo_modifier,
      recommended_page_type: row.recommended_page_type,
      suggested_url: row.suggested_url,
      recommended_action: row.recommended_action,
      keyword_count: 0,
      total_volume: 0,
      highest_volume: -1,
      canonical_keyword: "",
      keywords_with_current_url: 0,
      current_positions: [],
    };
    stat.keyword_count += 1;
    stat.total_volume += row.volume;
    if (row.volume > stat.highest_volume) {
      stat.highest_volume = row.volume;
      stat.canonical_keyword = row.keyword;
    }
    if (row.current_url) {
      stat.keywords_with_current_url += 1;
      const pos = Number(row.current_position || 0);
      if (pos) stat.current_positions.push(pos);
    }
    clusterStats.set(row.cluster_key, stat);
  }

  const clusterSummary = [...clusterStats.values()]
    .map((stat) => ({
      ...stat,
      average_current_position: stat.current_positions.length
        ? (
            stat.current_positions.reduce((sum, value) => sum + value, 0) /
            stat.current_positions.length
          ).toFixed(2)
        : "",
      current_ranking_coverage_pct: ((stat.keywords_with_current_url / stat.keyword_count) * 100).toFixed(1),
    }))
    .sort((a, b) => b.total_volume - a.total_volume);

  const canonicalMap = new Map(
    clusterSummary.map((cluster) => [cluster.cluster_key, cluster.canonical_keyword]),
  );
  for (const row of clustered) {
    row.canonical_keyword = canonicalMap.get(row.cluster_key) || "";
  }

  const entitySummaryMap = new Map();
  for (const cluster of clusterSummary) {
    const key = cluster.primary_entity;
    const entity = entitySummaryMap.get(key) || {
      topical_silo: cluster.topical_silo,
      primary_entity: cluster.primary_entity,
      entity_type: cluster.entity_type,
      parent_entity: cluster.parent_entity,
      cluster_count: 0,
      keyword_count: 0,
      total_volume: 0,
      dominant_page_type: cluster.recommended_page_type,
      suggested_url: cluster.suggested_url,
      intent_mix: new Set(),
    };
    entity.cluster_count += 1;
    entity.keyword_count += cluster.keyword_count;
    entity.total_volume += cluster.total_volume;
    entity.intent_mix.add(cluster.intent_detail);
    entitySummaryMap.set(key, entity);
  }

  const entitySummary = [...entitySummaryMap.values()]
    .map((entity) => ({
      ...entity,
      intent_mix: [...entity.intent_mix].sort().join(" | "),
    }))
    .sort((a, b) => b.total_volume - a.total_volume);

  return { clustered, clusterSummary, entitySummary };
}

function buildSummaryMarkdown(clustered, clusterSummary, entitySummary) {
  const totalKeywords = clustered.length;
  const totalClusters = clusterSummary.length;
  const totalEntities = entitySummary.length;
  const manualReview = clustered.filter((row) => row.needs_manual_review === "Yes");
  const topSilos = aggregateTopicalSilos(clustered).slice(0, 12);
  const topOpportunities = clusterSummary
    .filter((row) => !row.suggested_url || row.current_ranking_coverage_pct === "0.0")
    .slice(0, 20);

  const lines = [
    "# McDonald's Keyword Clustering Summary",
    "",
    `- Total keywords clustered: ${totalKeywords}`,
    `- Total unique clusters: ${totalClusters}`,
    `- Total entities surfaced: ${totalEntities}`,
    `- Manual review rows: ${manualReview.length}`,
    "",
    "## Topical silos by volume",
    "",
  ];

  for (const silo of topSilos) {
    lines.push(
      `- ${silo.topical_silo}: ${silo.keyword_count} keywords, ${silo.total_volume} total volume`,
    );
  }

  lines.push("", "## Highest-volume cluster opportunities", "");
  for (const row of topOpportunities) {
    lines.push(
      `- ${row.cluster_label}: ${row.total_volume} volume across ${row.keyword_count} keywords`,
    );
  }

  if (manualReview.length) {
    lines.push("", "## Manual review sample", "");
    for (const row of manualReview.slice(0, 30)) {
      lines.push(`- ${row.keyword}`);
    }
  }

  return `${lines.join("\n")}\n`;
}

function aggregateTopicalSilos(clustered) {
  const map = new Map();
  for (const row of clustered) {
    const entry = map.get(row.topical_silo) || {
      topical_silo: row.topical_silo,
      keyword_count: 0,
      total_volume: 0,
    };
    entry.keyword_count += 1;
    entry.total_volume += row.volume;
    map.set(row.topical_silo, entry);
  }
  return [...map.values()].sort((a, b) => b.total_volume - a.total_volume);
}

function main() {
  ensureDir(OUTPUT_DIR);
  const { rows } = parseKeywordFile(INPUT_FILE);
  const menuData = JSON.parse(fs.readFileSync(MENU_JSON, "utf8"));
  const entityCatalog = buildEntityCatalog(menuData);
  const { clustered, clusterSummary, entitySummary } = clusterKeywords(rows, entityCatalog);
  const entityMapRows = entityCatalog
    .map((entity) => ({
      entity_id: entity.id,
      entity_name: entity.name,
      entity_type: entity.type,
      topical_silo: entity.silo,
      parent_entity: entity.parentName,
      page_type: entity.pageType,
      existing_page: entity.existingPage ? "Yes" : "No",
      target_url: entity.url,
      category_url: entity.categoryUrl || "",
      alias_count: entity.aliases.length,
      sample_aliases: entity.aliases.slice(0, 12).join(" | "),
    }))
    .sort((a, b) => {
      if (a.topical_silo === b.topical_silo) {
        return a.entity_name.localeCompare(b.entity_name);
      }
      return a.topical_silo.localeCompare(b.topical_silo);
    });
  const siloSummaryRows = aggregateTopicalSilos(clustered);

  writeCsv(
    path.join(OUTPUT_DIR, "mcdomenuusa-keyword-clusters-2026-05-05.csv"),
    clustered.sort((a, b) => b.volume - a.volume),
    [
      "keyword",
      "canonical_keyword",
      "volume",
      "kd",
      "cpc",
      "serp_features",
      "topical_silo",
      "primary_entity",
      "entity_id",
      "entity_type",
      "parent_entity",
      "intent",
      "intent_detail",
      "geo_modifier",
      "comparison_entity_a",
      "comparison_entity_b",
      "cluster_key",
      "cluster_label",
      "recommended_page_type",
      "recommended_action",
      "suggested_url",
      "entity_existing_page",
      "needs_manual_review",
      "current_url",
      "current_position",
      "current_traffic",
      "competitor_url",
      "updated",
    ],
  );

  writeCsv(
    path.join(OUTPUT_DIR, "mcdomenuusa-cluster-summary-2026-05-05.csv"),
    clusterSummary,
    [
      "canonical_keyword",
      "cluster_key",
      "cluster_label",
      "topical_silo",
      "primary_entity",
      "entity_type",
      "parent_entity",
      "intent",
      "intent_detail",
      "geo_modifier",
      "keyword_count",
      "total_volume",
      "current_ranking_coverage_pct",
      "average_current_position",
      "recommended_page_type",
      "recommended_action",
      "suggested_url",
    ],
  );

  writeCsv(
    path.join(OUTPUT_DIR, "mcdomenuusa-entity-summary-2026-05-05.csv"),
    entitySummary,
    [
      "topical_silo",
      "primary_entity",
      "entity_type",
      "parent_entity",
      "cluster_count",
      "keyword_count",
      "total_volume",
      "dominant_page_type",
      "suggested_url",
      "intent_mix",
    ],
  );

  writeCsv(
    path.join(OUTPUT_DIR, "mcdomenuusa-entity-map-2026-05-05.csv"),
    entityMapRows,
    [
      "entity_id",
      "entity_name",
      "entity_type",
      "topical_silo",
      "parent_entity",
      "page_type",
      "existing_page",
      "target_url",
      "category_url",
      "alias_count",
      "sample_aliases",
    ],
  );

  writeCsv(
    path.join(OUTPUT_DIR, "mcdomenuusa-topical-silo-summary-2026-05-05.csv"),
    siloSummaryRows,
    ["topical_silo", "keyword_count", "total_volume"],
  );

  fs.writeFileSync(
    path.join(OUTPUT_DIR, "mcdomenuusa-topical-map-2026-05-05.md"),
    buildSummaryMarkdown(clustered, clusterSummary, entitySummary),
    "utf8",
  );

  const stats = {
    total_keywords: clustered.length,
    total_clusters: clusterSummary.length,
    total_entities: entitySummary.length,
    manual_review_keywords: clustered.filter((row) => row.needs_manual_review === "Yes").length,
    top_silos: aggregateTopicalSilos(clustered).slice(0, 10),
    top_clusters: clusterSummary.slice(0, 20).map((row) => ({
      canonical_keyword: row.canonical_keyword,
      total_volume: row.total_volume,
      keyword_count: row.keyword_count,
      primary_entity: row.primary_entity,
      intent_detail: row.intent_detail,
    })),
  };

  console.log(JSON.stringify(stats, null, 2));
}

main();
