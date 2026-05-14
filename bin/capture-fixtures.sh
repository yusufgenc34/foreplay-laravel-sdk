#!/usr/bin/env bash
# Captures live Foreplay API responses into resources/fixtures/<operationId>.json.
# Chains discovery → real IDs → per-endpoint fetches so fixtures are realistic.
#
# Usage:
#   FOREPLAY_API_KEY=... bash bin/capture-fixtures.sh
#
# Each fixture is a single endpoint response (data + metadata). Email/user IDs
# from /api/usage are scrubbed before saving — keep this script's output if you
# plan to commit fixtures to a public repo.

set -euo pipefail

KEY="${FOREPLAY_API_KEY:-}"
if [[ -z "$KEY" ]]; then
  echo "FOREPLAY_API_KEY environment variable required." >&2
  exit 1
fi

BASE="${FOREPLAY_BASE_URL:-https://public.api.foreplay.co}"
DIR="$(cd "$(dirname "$0")/.." && pwd)/resources/fixtures"
mkdir -p "$DIR"

req() {
  # $1 = operationId, $2 = path+query
  local op="$1" path="$2"
  echo "  -> $op  ($path)"
  /usr/bin/curl -sk -H "Authorization: $KEY" "$BASE$path" \
    | python3 -c "import sys, json; print(json.dumps(json.load(sys.stdin), indent=2))" \
    > "$DIR/$op.json"
}

pluck() {
  # $1 = file (operationId), $2 = jq-style python path expression
  python3 -c "import json, sys; d=json.load(open('$DIR/$1.json'))
try:
  print($2)
except Exception:
  print('')"
}

echo "== Account =="
req get_user_usage "/api/usage"
# scrub PII from usage fixture
python3 - <<'PY'
import json, pathlib, os
p = pathlib.Path(os.environ["FIXDIR"]) / "get_user_usage.json"
d = json.loads(p.read_text())
if d.get("data", {}).get("user"):
    d["data"]["user"]["id"] = "user_redacted"
    d["data"]["user"]["email"] = "you@example.com"
p.write_text(json.dumps(d, indent=2))
PY

echo "== Discovery =="
req search_discovery_ads      "/api/discovery/ads?query=nike&limit=3"
req search_discovery_brands   "/api/discovery/brands?query=nike&limit=5"
req discover_brands_by_ads    "/api/discovery/brands/explore?live=true&limit=5"

AD_ID=$(pluck search_discovery_ads "d['data'][0]['ad_id']")
BRAND_ID=$(pluck search_discovery_ads "d['data'][0].get('brand_id') or ''")
echo "  picked ad_id=$AD_ID brand_id=$BRAND_ID"

echo "== Ad =="
if [[ -n "$AD_ID" ]]; then
  req get_ad_by_id                 "/api/ad/$AD_ID"
  req get_group_duplicates_by_ad_id "/api/ad/duplicates/$AD_ID"
fi

echo "== Brand =="
req get_brands_by_domain "/api/brand/getBrandsByDomain?domain=nike.com&limit=3"
if [[ -n "$BRAND_ID" ]]; then
  req get_ads_by_brand_ids "/api/brand/getAdsByBrandId?brand_ids=$BRAND_ID&limit=3"
  req get_brands_analytics "/api/brand/analytics?id=$BRAND_ID"
fi

# Pull a page_id from the discovery results if any (via the ad's link or brand lookup)
PAGE_ID=$(pluck search_discovery_ads "next((str(x) for x in [a.get('page_id') for a in d.get('data',[])] if x), '')")
if [[ -z "$PAGE_ID" ]]; then
  # Fall back: brands_by_domain often has ad_library_id (page_id alias)
  PAGE_ID=$(pluck get_brands_by_domain "d['data'][0].get('ad_library_id') or ''")
fi
echo "  picked page_id=$PAGE_ID"
if [[ -n "$PAGE_ID" ]]; then
  req get_brands_ads_by_page_id "/api/brand/getAdsByPageId?page_id=$PAGE_ID&limit=3"
fi

echo "== Boards =="
req get_boards "/api/boards?limit=10"
BOARD_ID=$(pluck get_boards "d['data'][0].get('id') if d.get('data') else ''")
echo "  picked board_id=$BOARD_ID"
if [[ -n "$BOARD_ID" ]]; then
  req get_board_ads        "/api/board/ads?board_id=$BOARD_ID&limit=3"
  req get_brands_by_board_id "/api/board/brands?board_id=$BOARD_ID&limit=10"
fi

echo "== Spyder =="
req get_spyder_brands "/api/spyder/brands?limit=10"
SPYDER_ID=$(pluck get_spyder_brands "d['data'][0].get('id') if d.get('data') else ''")
echo "  picked spyder brand_id=$SPYDER_ID"
if [[ -n "$SPYDER_ID" ]]; then
  req get_spyder_brand     "/api/spyder/brand?brand_id=$SPYDER_ID"
  req get_spyder_brand_ads "/api/spyder/brand/ads?brand_id=$SPYDER_ID&limit=3"
fi

echo "== Swipefile =="
req get_swipefile_ads "/api/swipefile/ads?limit=3"

echo ""
echo "Done. Captured fixtures:"
ls -1 "$DIR"
