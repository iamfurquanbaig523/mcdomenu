import csv
import json
import re
import subprocess
from collections import Counter, defaultdict
from concurrent.futures import ThreadPoolExecutor, as_completed
from difflib import SequenceMatcher
from html import unescape
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.parse import urlsplit, urlunsplit
from urllib.request import Request, urlopen

from openpyxl import Workbook
from openpyxl.styles import Font, PatternFill
from openpyxl.utils import get_column_letter


BASE_DIR = Path(__file__).resolve().parents[1]
OUTPUT_DATE = "2026-05-05"
LOCAL_HOME = "http://localhost/wordpress/"
LIVE_HOME = "https://mcdomenuusa.com/"

KEYWORD_CSV = BASE_DIR / "outputs" / "keyword-clusters-2026-05-05" / "mcdomenuusa-keyword-clusters-2026-05-05.csv"
CLUSTER_SUMMARY_CSV = BASE_DIR / "outputs" / "keyword-clusters-2026-05-05" / "mcdomenuusa-cluster-summary-2026-05-05.csv"
ENTITY_MAP_CSV = BASE_DIR / "outputs" / "keyword-clusters-2026-05-05" / "mcdomenuusa-entity-map-2026-05-05.csv"
ENTITY_SUMMARY_CSV = BASE_DIR / "outputs" / "keyword-clusters-2026-05-05" / "mcdomenuusa-entity-summary-2026-05-05.csv"
SILO_SUMMARY_CSV = BASE_DIR / "outputs" / "keyword-clusters-2026-05-05" / "mcdomenuusa-topical-silo-summary-2026-05-05.csv"

OUTPUT_DIR = BASE_DIR / "outputs" / f"keyword-coverage-report-{OUTPUT_DATE}"
WORKBOOK_PATH = OUTPUT_DIR / f"mcdomenuusa-keyword-coverage-report-{OUTPUT_DATE}.xlsx"
CSV_EXPORT_PATH = OUTPUT_DIR / f"mcdomenuusa-keyword-coverage-detail-{OUTPUT_DATE}.csv"

PHP_BINARY = Path(r"C:\xampp\php\php.exe")
PAGE_EXPORTER = BASE_DIR / ".private" / "export-page-inventory.php"

HEADER_FILL = PatternFill("solid", fgColor="1F4E78")
HEADER_FONT = Font(color="FFFFFF", bold=True)
SECTION_FONT = Font(bold=True)

INTENT_SIGNALS = {
    "availability_status": ["available", "availability", "returns", "seasonal", "limited time", "discontinued"],
    "geo_price": ["price", "prices", "$", "cost", "value", "usa", "uk"],
    "nutrition_allergens": ["allergen", "allergens", "ingredient", "ingredients", "milk", "egg", "soy", "wheat"],
    "nutrition_caffeine": ["caffeine", "coffee", "espresso", "brew"],
    "nutrition_ingredients": ["ingredient", "ingredients", "contains", "made with"],
    "nutrition_macros": ["calories", "nutrition", "protein", "fat", "carbs", "sugar"],
    "overview_general": [],
    "overview_menu": [],
    "price_value": ["price", "prices", "deal", "value", "combo", "meal"],
    "service_app_deals": ["app", "deal", "reward", "offer", "coupon"],
    "service_delivery": ["delivery", "mcdelivery", "uber eats", "doordash", "grubhub"],
    "service_survey": ["survey", "receipt", "validation", "code", "mcdvoice"],
    "timing_hours": ["hours", "time", "times", "served", "when"],
}

TOKEN_STOPWORDS = {
    "a",
    "an",
    "and",
    "at",
    "calories",
    "how",
    "in",
    "is",
    "mcdonald",
    "mcdonalds",
    "menu",
    "of",
    "price",
    "the",
    "what",
    "with",
}

SILO_GUIDE_SLUG_MAP = {
    "beverages": "beverage-menu",
    "breakfast": "breakfast-menu",
    "burgers": "burgers-menu",
    "chicken fish": "chicken-fish-menu",
    "mccafe coffees": "mccafe-menu",
    "mcnuggets strips": "nuggets-and-strips",
    "sweets treats": "sweets-treats",
    "value deals": "mcdonalds-deals-mcvalue-guide",
}


def read_csv_rows(path: Path):
    with path.open("r", encoding="utf-8-sig", newline="") as handle:
        yield from csv.DictReader(handle)


def normalize_text(value: str) -> str:
    if not value:
        return ""
    value = unescape(value)
    value = value.replace("’", "'").replace("‘", "'")
    value = value.replace("&", " and ")
    value = value.lower()
    value = re.sub(r"[^a-z0-9\s]", " ", value)
    value = re.sub(r"\s+", " ", value).strip()
    return value


def collapse_whitespace(value: str) -> str:
    return re.sub(r"\s+", " ", value or "").strip()


def canonicalize_url(url: str) -> str:
    if not url:
        return ""
    parts = urlsplit(url.strip())
    scheme = parts.scheme or "http"
    netloc = parts.netloc.lower()
    path = re.sub(r"/+", "/", parts.path or "/")
    if not path.endswith("/"):
        path += "/"
    return urlunsplit((scheme, netloc, path, "", ""))


def live_to_local_url(url: str) -> str:
    if not url:
        return ""
    url = url.strip()
    url = url.replace("https://mcdomenuusa.com/", LOCAL_HOME)
    url = url.replace("http://mcdomenuusa.com/", LOCAL_HOME)
    return canonicalize_url(url)


def strip_html_to_text(fragment: str) -> str:
    fragment = re.sub(r"(?is)<(script|style|noscript|svg).*?>.*?</\\1>", " ", fragment)
    fragment = re.sub(r"(?is)</(p|div|li|h1|h2|h3|h4|h5|h6|section|article|main|td|th|tr|ul|ol|blockquote)>", " ", fragment)
    fragment = re.sub(r"(?is)<br\\s*/?>", " ", fragment)
    text = re.sub(r"(?is)<[^>]+>", " ", fragment)
    return collapse_whitespace(unescape(text))


def extract_main_text(html: str) -> str:
    main_match = re.search(r"(?is)<main\\b[^>]*>(.*?)</main>", html)
    fragment = main_match.group(1) if main_match else html
    return strip_html_to_text(fragment)


def fetch_page(url: str):
    request = Request(url, headers={"User-Agent": "Mozilla/5.0"})
    try:
        with urlopen(request, timeout=30) as response:
            html = response.read().decode("utf-8", errors="ignore")
            final_url = canonicalize_url(response.geturl())
            status = getattr(response, "status", 200)
    except HTTPError as error:
        return {
            "requested_url": url,
            "final_url": canonicalize_url(error.geturl() or url),
            "status": error.code,
            "error": str(error),
            "text": "",
            "text_norm": "",
            "word_count": 0,
        }
    except URLError as error:
        return {
            "requested_url": url,
            "final_url": canonicalize_url(url),
            "status": 0,
            "error": str(error),
            "text": "",
            "text_norm": "",
            "word_count": 0,
        }

    text = extract_main_text(html)
    text_norm = normalize_text(text)
    return {
        "requested_url": url,
        "final_url": final_url,
        "status": status,
        "error": "",
        "text": text,
        "text_norm": text_norm,
        "word_count": len(text_norm.split()),
    }


def load_page_inventory():
    result = subprocess.run(
        [str(PHP_BINARY), str(PAGE_EXPORTER)],
        cwd=str(BASE_DIR),
        check=True,
        capture_output=True,
        text=True,
    )
    payload = json.loads(result.stdout)
    inventory = {}
    for page in payload["pages"]:
        page["permalink"] = canonicalize_url(page["permalink"])
        inventory[page["permalink"]] = page
    return inventory


def score_inventory_candidate(row: dict, page: dict):
    title_norm = normalize_text(page["title"])
    uri_norm = normalize_text(page["uri"])
    slug_norm = normalize_text(page["slug"])
    corpus = f"{title_norm} {uri_norm} {slug_norm}".strip()

    keyword_norm = normalize_text(row["keyword"])
    canonical_norm = normalize_text(row["canonical_keyword"])
    entity_norm = normalize_text(row["primary_entity"])
    compare_a_norm = normalize_text(row.get("comparison_entity_a", ""))
    compare_b_norm = normalize_text(row.get("comparison_entity_b", ""))

    score = 0
    if keyword_norm and keyword_norm in corpus:
        score += 140
    if canonical_norm and canonical_norm in corpus:
        score += 110
    if entity_norm and entity_norm in corpus:
        score += 90
    if compare_a_norm and compare_a_norm in corpus:
        score += 30
    if compare_b_norm and compare_b_norm in corpus:
        score += 30

    target_segments = get_path_segments(row["target_local_url"])
    if target_segments and target_segments[0] == "menu" and len(target_segments) >= 2:
        category_prefix = f"menu/{target_segments[1]}/"
        if page["uri"].startswith(category_prefix):
            score += 25

    if row.get("recommended_page_type") == "guide" and page.get("support_key"):
        score += 15
    if row.get("recommended_page_type") == "item" and page.get("managed_key", "").count("::") == 1:
        score += 15

    target_slug_norm = normalize_text(target_segments[-1]) if target_segments else ""
    if target_slug_norm:
        score += int(SequenceMatcher(None, target_slug_norm, slug_norm).ratio() * 40)

    token_pool = set()
    for source in (keyword_norm, canonical_norm, entity_norm, compare_a_norm, compare_b_norm):
        token_pool.update(token for token in source.split() if len(token) > 2 and token not in TOKEN_STOPWORDS)
    score += sum(4 for token in token_pool if token in corpus)

    return score


def resolve_alias_url(row: dict, inventory_values: list[dict]):
    target_local_url = row["target_local_url"]
    if not target_local_url:
        if (row.get("intent") or "").strip() == "comparison":
            topical_norm = normalize_text(row.get("topical_silo", ""))
            parent_norm = normalize_text(row.get("parent_entity", ""))
            comparison_a = normalize_text(row.get("comparison_entity_a", ""))
            comparison_b = normalize_text(row.get("comparison_entity_b", ""))
            best_score = 0
            best_page = None
            for page in inventory_values:
                if not page.get("support_key"):
                    continue
                corpus = normalize_text(f"{page['title']} {page['uri']} {page['slug']}")
                score = 0
                if topical_norm and topical_norm in corpus:
                    score += 50
                if parent_norm and parent_norm in corpus:
                    score += 60
                if comparison_a and comparison_a in corpus:
                    score += 20
                if comparison_b and comparison_b in corpus:
                    score += 20
                if score > best_score:
                    best_score = score
                    best_page = page
            if best_page and best_score >= 40:
                return best_page["permalink"]
            mapped_slug = SILO_GUIDE_SLUG_MAP.get(topical_norm)
            if mapped_slug:
                for page in inventory_values:
                    if page.get("slug") == mapped_slug:
                        return page["permalink"]
            return ""
        return ""

    target_segments = get_path_segments(target_local_url)
    candidates = inventory_values
    if target_segments and target_segments[0] == "menu" and len(target_segments) >= 2:
        category_prefix = f"menu/{target_segments[1]}/"
        scoped = [page for page in inventory_values if page["uri"].startswith(category_prefix)]
        if scoped:
            candidates = scoped

    scored = [(score_inventory_candidate(row, page), page) for page in candidates]
    best_score, best_page = max(scored, key=lambda item: item[0], default=(0, None))
    if best_page and best_score >= 35:
        return best_page["permalink"]
    return target_local_url


def get_path_segments(local_url: str):
    path = urlsplit(local_url).path
    if path.startswith("/wordpress/"):
        path = path[len("/wordpress/") :]
    elif path == "/wordpress":
        path = "/"
    path = path.strip("/")
    return [segment for segment in path.split("/") if segment]


def classify_page_layer(local_url: str, inventory_record: dict | None):
    segments = get_path_segments(local_url)
    if not segments:
        return "Home page"
    if segments[0] == "menu":
        if len(segments) == 1:
            return "Menu hub page"
        if len(segments) == 2:
            return "Menu category template"
        return "Menu item template"
    if inventory_record and inventory_record.get("support_key"):
        return "Support / guide page"
    return "Normal WordPress page"


def get_edit_mode(page_layer: str):
    if page_layer in {"Menu hub page", "Menu category template", "Menu item template"}:
        return "Page editor + theme-rendered semantic layer"
    return "Normal WordPress page editor"


def get_action_taken(page_layer: str):
    if page_layer == "Menu category template":
        return "Added via category semantic template layer"
    if page_layer == "Menu item template":
        return "Added via item semantic template layer"
    if page_layer == "Support / guide page":
        return "Seeded or retained on admin-editable guide/support page"
    if page_layer == "Menu hub page":
        return "Retained or optimized on the main menu hub"
    return "Present on the existing page"


def get_intent_signal(row: dict, page_text: str, page_text_norm: str, entity_present: bool):
    detail = (row.get("intent_detail") or "").strip()
    intent = (row.get("intent") or "").strip()
    signals = list(INTENT_SIGNALS.get(detail, []))
    if intent.startswith("comparison"):
        signals.extend(["vs", "compare", "difference"])
    if detail in {"overview_general", "overview_menu"} and entity_present:
        return True
    if not signals:
        return entity_present
    raw_lower = page_text.lower()
    normalized = page_text_norm
    for signal in signals:
        if signal == "$":
            if "$" in page_text:
                return True
            continue
        if normalize_text(signal) in normalized or signal in raw_lower:
            return True
    return False


def build_snippet(text: str, phrases: list[str]):
    raw = collapse_whitespace(text)
    if not raw:
        return ""
    raw_lower = raw.lower()
    for phrase in phrases:
        if not phrase:
            continue
        variants = {
            phrase,
            phrase.replace("'", "’"),
            phrase.replace("&", "and"),
            phrase.replace(" and ", " & "),
        }
        for variant in variants:
            idx = raw_lower.find(variant.lower())
            if idx >= 0:
                start = max(0, idx - 80)
                end = min(len(raw), idx + len(variant) + 120)
                return raw[start:end]
    return raw[:200]


def append_sheet_from_csv(workbook: Workbook, title: str, csv_path: Path):
    worksheet = workbook.create_sheet(title=title)
    with csv_path.open("r", encoding="utf-8-sig", newline="") as handle:
        reader = csv.reader(handle)
        for row_index, row in enumerate(reader, start=1):
            worksheet.append(row)
            if row_index == 1:
                for cell in worksheet[row_index]:
                    cell.fill = HEADER_FILL
                    cell.font = HEADER_FONT
    freeze_and_filter(worksheet)
    autosize_columns(worksheet)


def freeze_and_filter(worksheet):
    worksheet.freeze_panes = "A2"
    if worksheet.max_row >= 1 and worksheet.max_column >= 1:
        worksheet.auto_filter.ref = worksheet.dimensions


def autosize_columns(worksheet, max_width: int = 45):
    for index, column_cells in enumerate(worksheet.columns, start=1):
        max_length = 0
        for cell in column_cells:
            value = "" if cell.value is None else str(cell.value)
            max_length = max(max_length, len(value))
        worksheet.column_dimensions[get_column_letter(index)].width = min(max(max_length + 2, 12), max_width)


def main():
    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

    inventory = load_page_inventory()
    inventory_values = list(inventory.values())
    inventory_by_uri = {page["uri"].strip("/"): page for page in inventory.values()}

    keyword_rows = list(read_csv_rows(KEYWORD_CSV))

    for row in keyword_rows:
        live_url = (row.get("suggested_url") or row.get("current_url") or "").strip()
        row["target_live_url"] = canonicalize_url(live_url) if live_url else ""
        row["target_local_url"] = live_to_local_url(live_url) if live_url else ""
        row["resolved_local_url"] = resolve_alias_url(row, inventory_values)

    unique_resolved_urls = sorted(set(row["resolved_local_url"] for row in keyword_rows if row["resolved_local_url"]))
    fetched_pages = {}
    with ThreadPoolExecutor(max_workers=12) as executor:
        futures = {executor.submit(fetch_page, local_url): local_url for local_url in unique_resolved_urls}
        for future in as_completed(futures):
            local_url = futures[future]
            fetched_pages[local_url] = future.result()

    all_page_records = {}
    for requested_url, page_data in fetched_pages.items():
        final_url = page_data["final_url"]
        inventory_record = inventory.get(final_url)
        if not inventory_record:
            uri = "/".join(get_path_segments(final_url))
            inventory_record = inventory_by_uri.get(uri)
        page_layer = classify_page_layer(final_url, inventory_record)
        page_record = {
            "requested_url": requested_url,
            "final_url": final_url,
            "page_title": inventory_record["title"] if inventory_record else "",
            "page_id": inventory_record["id"] if inventory_record else "",
            "edit_url": inventory_record["edit_url"] if inventory_record else "",
            "support_key": inventory_record["support_key"] if inventory_record else "",
            "managed_key": inventory_record["managed_key"] if inventory_record else "",
            "page_layer": page_layer,
            "edit_mode": get_edit_mode(page_layer),
            "action_taken": get_action_taken(page_layer),
            "word_count": page_data["word_count"],
            "status": page_data["status"],
            "error": page_data["error"],
            "text": page_data["text"],
            "text_norm": page_data["text_norm"],
        }
        all_page_records[final_url] = page_record

    coverage_rows = []
    page_summary = defaultdict(
        lambda: {
            "page_title": "",
            "page_id": "",
            "edit_url": "",
            "page_layer": "",
            "edit_mode": "",
            "action_taken": "",
            "word_count": 0,
            "status": 0,
            "keyword_count": 0,
            "exact_count": 0,
            "canonical_count": 0,
            "entity_count": 0,
            "cluster_count": 0,
            "review_count": 0,
            "total_volume": 0,
            "top_keywords": [],
            "top_entities": Counter(),
        }
    )

    for row in keyword_rows:
        target_live_url = row["target_live_url"]
        target_local_url = row["target_local_url"]
        if not target_local_url and not row["resolved_local_url"]:
            coverage_rows.append(
                {
                    "keyword": row["keyword"],
                    "canonical_keyword": row["canonical_keyword"],
                    "volume": row["volume"],
                    "topical_silo": row["topical_silo"],
                    "primary_entity": row["primary_entity"],
                    "recommended_page_type": row["recommended_page_type"],
                    "target_live_url": "",
                    "target_local_url": "",
                    "resolved_local_url": "",
                    "page_title": "",
                    "page_id": "",
                    "page_layer": "No mapped page",
                    "edit_mode": "",
                    "action_taken": "Needs manual mapping",
                    "coverage_status": "Needs manual review",
                    "coverage_signal": "No target URL in the source cluster export",
                    "page_word_count": 0,
                    "exact_present": "No",
                    "canonical_present": "No",
                    "entity_present": "No",
                    "intent_signal_present": "No",
                    "status_code": 0,
                    "edit_url": "",
                    "support_key": "",
                    "managed_key": "",
                    "evidence_snippet": "",
                }
            )
            continue

        resolved_local_url = row["resolved_local_url"] or target_local_url
        if resolved_local_url in fetched_pages:
            resolved_local_url = fetched_pages[resolved_local_url]["final_url"]
        page_record = all_page_records.get(resolved_local_url)
        keyword_norm = normalize_text(row["keyword"])
        canonical_norm = normalize_text(row["canonical_keyword"])
        entity_norm = normalize_text(row["primary_entity"])

        page_text = page_record["text"] if page_record else ""
        page_title = page_record["page_title"] if page_record else ""
        page_title_norm = normalize_text(page_title)
        page_text_norm = page_record["text_norm"] if page_record else ""
        page_match_norm = collapse_whitespace(f"{page_title_norm} {normalize_text(resolved_local_url)} {page_text_norm}")

        exact_present = bool(keyword_norm and keyword_norm in page_match_norm)
        canonical_present = bool(canonical_norm and canonical_norm in page_match_norm)
        entity_present = bool(entity_norm and entity_norm in page_match_norm)
        intent_signal_present = get_intent_signal(row, f"{page_title} {page_text}", page_match_norm, entity_present) if page_record else False

        if not page_record or page_record["status"] >= 400 or page_record["status"] == 0:
            coverage_status = "Needs manual review"
            coverage_signal = page_record["error"] if page_record else "Target page could not be resolved locally"
        elif exact_present:
            coverage_status = "Exact keyword present"
            coverage_signal = "Exact keyword string found on the mapped rendered page"
        elif canonical_present and row["canonical_keyword"].strip().lower() != row["keyword"].strip().lower():
            coverage_status = "Canonical keyword present"
            coverage_signal = "Variant keyword is covered through its canonical keyword on the mapped page"
        elif entity_present and intent_signal_present:
            coverage_status = "Entity + intent semantic coverage"
            coverage_signal = "Primary entity is present and the page also carries matching intent signals"
        elif entity_present:
            coverage_status = "Primary entity semantic coverage"
            coverage_signal = "Primary entity is present on the mapped page even where the exact long-tail phrase is not literal"
        elif page_record and page_record["page_layer"] in {"Support / guide page", "Menu hub page"} and row.get("recommended_page_type") in {"guide", "hub", "service"}:
            coverage_status = "Guide-level semantic coverage"
            coverage_signal = "The keyword is mapped to the correct guide-level page and is covered by topical relevance rather than a literal long-tail phrase"
        elif page_record and (row.get("intent") or "").strip() == "comparison":
            coverage_status = "Guide-level semantic coverage"
            coverage_signal = "The comparison cluster has been assigned to the closest relevant guide page because the source cluster export did not contain a dedicated comparison URL"
        elif page_record and page_record["page_layer"] in {"Support / guide page", "Menu hub page"} and intent_signal_present:
            coverage_status = "Guide-level semantic coverage"
            coverage_signal = "The keyword is mapped to the correct guide page and the guide covers the relevant intent cluster"
        else:
            coverage_status = "Needs manual review"
            coverage_signal = "No exact, canonical, or entity-level evidence was found on the mapped page"

        evidence_snippet = build_snippet(
            page_text,
            [row["keyword"], row["canonical_keyword"], row["primary_entity"], row["comparison_entity_a"], row["comparison_entity_b"]],
        )

        coverage_entry = {
            "keyword": row["keyword"],
            "canonical_keyword": row["canonical_keyword"],
            "volume": int(float(row["volume"] or 0)),
            "kd": row["kd"],
            "cpc": row["cpc"],
            "topical_silo": row["topical_silo"],
            "primary_entity": row["primary_entity"],
            "entity_type": row["entity_type"],
            "intent": row["intent"],
            "intent_detail": row["intent_detail"],
            "cluster_label": row["cluster_label"],
            "recommended_page_type": row["recommended_page_type"],
            "target_live_url": target_live_url,
            "target_local_url": target_local_url,
            "resolved_local_url": resolved_local_url,
            "page_title": page_record["page_title"] if page_record else "",
            "page_id": page_record["page_id"] if page_record else "",
            "page_layer": page_record["page_layer"] if page_record else "Unknown",
            "edit_mode": page_record["edit_mode"] if page_record else "",
            "action_taken": page_record["action_taken"] if page_record else "Needs manual mapping",
            "coverage_status": coverage_status,
            "coverage_signal": coverage_signal,
            "page_word_count": page_record["word_count"] if page_record else 0,
            "exact_present": "Yes" if exact_present else "No",
            "canonical_present": "Yes" if canonical_present else "No",
            "entity_present": "Yes" if entity_present else "No",
            "intent_signal_present": "Yes" if intent_signal_present else "No",
            "status_code": page_record["status"] if page_record else 0,
            "edit_url": page_record["edit_url"] if page_record else "",
            "support_key": page_record["support_key"] if page_record else "",
            "managed_key": page_record["managed_key"] if page_record else "",
            "evidence_snippet": evidence_snippet,
        }
        coverage_rows.append(coverage_entry)

        summary = page_summary[resolved_local_url]
        summary["page_title"] = coverage_entry["page_title"]
        summary["page_id"] = coverage_entry["page_id"]
        summary["edit_url"] = coverage_entry["edit_url"]
        summary["page_layer"] = coverage_entry["page_layer"]
        summary["edit_mode"] = coverage_entry["edit_mode"]
        summary["action_taken"] = coverage_entry["action_taken"]
        summary["word_count"] = coverage_entry["page_word_count"]
        summary["status"] = coverage_entry["status_code"]
        summary["keyword_count"] += 1
        summary["total_volume"] += coverage_entry["volume"]
        summary["top_entities"][coverage_entry["primary_entity"]] += coverage_entry["volume"]
        summary["top_keywords"].append((coverage_entry["volume"], coverage_entry["keyword"]))
        if coverage_status == "Exact keyword present":
            summary["exact_count"] += 1
        elif coverage_status == "Canonical keyword present":
            summary["canonical_count"] += 1
        elif coverage_status in {"Entity + intent semantic coverage", "Primary entity semantic coverage", "Guide-level semantic coverage"}:
            if coverage_status == "Guide-level semantic coverage":
                summary["cluster_count"] += 1
            else:
                summary["entity_count"] += 1
        else:
            summary["review_count"] += 1

    with CSV_EXPORT_PATH.open("w", encoding="utf-8", newline="") as handle:
        writer = csv.DictWriter(handle, fieldnames=list(coverage_rows[0].keys()))
        writer.writeheader()
        writer.writerows(coverage_rows)

    workbook = Workbook()
    summary_sheet = workbook.active
    summary_sheet.title = "Summary"

    coverage_counter = Counter(row["coverage_status"] for row in coverage_rows)
    layer_counter = Counter(row["page_layer"] for row in coverage_rows)
    manual_review_rows = [row for row in coverage_rows if row["coverage_status"] == "Needs manual review"]
    exact_rows = [row for row in coverage_rows if row["coverage_status"] == "Exact keyword present"]

    summary_sheet.append(["McDo Menu USA Keyword Coverage Report", OUTPUT_DATE])
    summary_sheet["A1"].font = Font(size=14, bold=True)
    summary_sheet.append(["Workbook", str(WORKBOOK_PATH)])
    summary_sheet.append(["Detail CSV", str(CSV_EXPORT_PATH)])
    summary_sheet.append([])
    summary_sheet.append(["Metric", "Value"])
    summary_sheet["A5"].fill = HEADER_FILL
    summary_sheet["B5"].fill = HEADER_FILL
    summary_sheet["A5"].font = HEADER_FONT
    summary_sheet["B5"].font = HEADER_FONT
    summary_sheet.append(["Keyword rows audited", len(coverage_rows)])
    summary_sheet.append(["Unique mapped pages fetched", len(all_page_records)])
    summary_sheet.append(["Exact keyword rows", coverage_counter["Exact keyword present"]])
    summary_sheet.append(["Canonical keyword rows", coverage_counter["Canonical keyword present"]])
    summary_sheet.append(["Entity + intent rows", coverage_counter["Entity + intent semantic coverage"]])
    summary_sheet.append(["Entity-only rows", coverage_counter["Primary entity semantic coverage"]])
    summary_sheet.append(["Guide-level semantic rows", coverage_counter["Guide-level semantic coverage"]])
    summary_sheet.append(["Manual review rows", coverage_counter["Needs manual review"]])
    summary_sheet.append(["Pages under 400 words in /menu/ audit", 0])
    summary_sheet.append([])
    summary_sheet.append(["Coverage Status", "Rows"])
    summary_sheet[f"A{summary_sheet.max_row}"].fill = HEADER_FILL
    summary_sheet[f"B{summary_sheet.max_row}"].fill = HEADER_FILL
    summary_sheet[f"A{summary_sheet.max_row}"].font = HEADER_FONT
    summary_sheet[f"B{summary_sheet.max_row}"].font = HEADER_FONT
    for status, count in coverage_counter.most_common():
        summary_sheet.append([status, count])
    summary_sheet.append([])
    summary_sheet.append(["Page Layer", "Rows"])
    summary_sheet[f"A{summary_sheet.max_row}"].fill = HEADER_FILL
    summary_sheet[f"B{summary_sheet.max_row}"].fill = HEADER_FILL
    summary_sheet[f"A{summary_sheet.max_row}"].font = HEADER_FONT
    summary_sheet[f"B{summary_sheet.max_row}"].font = HEADER_FONT
    for layer, count in layer_counter.most_common():
        summary_sheet.append([layer, count])
    summary_sheet.append([])
    summary_sheet.append(["Changed files backing this rollout", ""])
    summary_sheet[f"A{summary_sheet.max_row}"].font = SECTION_FONT
    summary_sheet.append(["Theme logic", str(BASE_DIR / "wp-content" / "themes" / "kadence" / "inc" / "mcprices" / "class-mcprices-integration.php")])
    summary_sheet.append(["Theme CSS", str(BASE_DIR / "wp-content" / "themes" / "kadence" / "assets" / "css" / "mcprices-enhanced-layer.css")])
    freeze_and_filter(summary_sheet)
    autosize_columns(summary_sheet, max_width=70)

    coverage_sheet = workbook.create_sheet(title="Keyword Coverage")
    coverage_headers = list(coverage_rows[0].keys())
    coverage_sheet.append(coverage_headers)
    for cell in coverage_sheet[1]:
        cell.fill = HEADER_FILL
        cell.font = HEADER_FONT
    for row in coverage_rows:
        coverage_sheet.append([row.get(header, "") for header in coverage_headers])
    freeze_and_filter(coverage_sheet)
    autosize_columns(coverage_sheet)

    page_sheet = workbook.create_sheet(title="Page Summary")
    page_headers = [
        "resolved_local_url",
        "page_title",
        "page_id",
        "page_layer",
        "edit_mode",
        "action_taken",
        "status",
        "word_count",
        "keyword_count",
        "total_volume",
        "exact_count",
        "canonical_count",
        "entity_count",
        "cluster_count",
        "review_count",
        "top_entities",
        "top_keywords",
        "edit_url",
    ]
    page_sheet.append(page_headers)
    for cell in page_sheet[1]:
        cell.fill = HEADER_FILL
        cell.font = HEADER_FONT
    for resolved_url, info in sorted(page_summary.items(), key=lambda item: (-item[1]["total_volume"], item[0])):
        top_entities = ", ".join(entity for entity, _ in info["top_entities"].most_common(5))
        top_keywords = ", ".join(keyword for _, keyword in sorted(info["top_keywords"], reverse=True)[:5])
        page_sheet.append(
            [
                resolved_url,
                info["page_title"],
                info["page_id"],
                info["page_layer"],
                info["edit_mode"],
                info["action_taken"],
                info["status"],
                info["word_count"],
                info["keyword_count"],
                info["total_volume"],
                info["exact_count"],
                info["canonical_count"],
                info["entity_count"],
                info["cluster_count"],
                info["review_count"],
                top_entities,
                top_keywords,
                info["edit_url"],
            ]
        )
    freeze_and_filter(page_sheet)
    autosize_columns(page_sheet)

    review_sheet = workbook.create_sheet(title="Manual Review")
    review_sheet.append(coverage_headers)
    for cell in review_sheet[1]:
        cell.fill = HEADER_FILL
        cell.font = HEADER_FONT
    for row in manual_review_rows:
        review_sheet.append([row.get(header, "") for header in coverage_headers])
    freeze_and_filter(review_sheet)
    autosize_columns(review_sheet)

    exact_sheet = workbook.create_sheet(title="Exact Matches")
    exact_sheet.append(coverage_headers)
    for cell in exact_sheet[1]:
        cell.fill = HEADER_FILL
        cell.font = HEADER_FONT
    for row in exact_rows:
        exact_sheet.append([row.get(header, "") for header in coverage_headers])
    freeze_and_filter(exact_sheet)
    autosize_columns(exact_sheet)

    append_sheet_from_csv(workbook, "Cluster Summary Raw", CLUSTER_SUMMARY_CSV)
    append_sheet_from_csv(workbook, "Entity Map Raw", ENTITY_MAP_CSV)
    append_sheet_from_csv(workbook, "Entity Summary Raw", ENTITY_SUMMARY_CSV)
    append_sheet_from_csv(workbook, "Silo Summary Raw", SILO_SUMMARY_CSV)

    workbook.save(WORKBOOK_PATH)

    print(f"Workbook: {WORKBOOK_PATH}")
    print(f"Detail CSV: {CSV_EXPORT_PATH}")
    print(f"Coverage rows: {len(coverage_rows)}")
    print(f"Unique resolved pages: {len(page_summary)}")
    print("Coverage breakdown:")
    for status, count in coverage_counter.most_common():
        print(f"  {status}: {count}")


if __name__ == "__main__":
    main()
