#!/usr/bin/env python3
"""
Build playground fixtures by composing real data captured from the live
Foreplay API.

The Foreplay public API is read-only — there are no endpoints to create
boards, track Spyder brands, or save ads to the swipefile. So for accounts
that haven't populated those features in the web UI, the relevant fixtures
come back empty.

This script takes the *real* data we captured (Nike ads, Nike-domain brands,
brand analytics) and composes them into realistic fixtures for the
empty-state endpoints. The result is a sandbox playground where every
SDK method returns non-trivial, Foreplay-shaped data.

Run AFTER bin/capture-fixtures.sh:

    python3 bin/build-playground-fixtures.py

Idempotent — overwrites only the derived/empty fixtures, leaves live
captures alone.
"""

from __future__ import annotations

import copy
import json
import pathlib
import time
import uuid

HERE = pathlib.Path(__file__).resolve().parent
FIX = HERE.parent / "resources" / "fixtures"


def load(name: str) -> dict:
    return json.loads((FIX / f"{name}.json").read_text())


def save(name: str, body: dict) -> None:
    (FIX / f"{name}.json").write_text(json.dumps(body, indent=2))
    print(f"  wrote {name}.json  data:{ _count_data(body) }")


def _count_data(body: dict) -> str:
    d = body.get("data")
    if isinstance(d, list):
        return f"{len(d)} items"
    if isinstance(d, dict):
        return "object"
    return "?"


def now_ms() -> int:
    return int(time.time() * 1000)


def base_paginated_meta(count: int) -> dict:
    return {
        "success": True,
        "message": "Your request has been processed successfully.",
        "status_code": 200,
        "processed_at": now_ms(),
        "cursor": None,
        "filters": {},
        "order": "newest",
        "count": count,
    }


def base_simple_meta() -> dict:
    return {
        "success": True,
        "message": "Your request has been processed successfully.",
        "status_code": 200,
        "processed_at": now_ms(),
    }


# ---------------------------------------------------------------------------
# Source data — pulled from real captures
# ---------------------------------------------------------------------------

ads_source = load("search_discovery_ads")["data"]          # Nike ads (3)
ad_source_alt = load("get_ads_by_brand_ids")["data"]       # Stadium brand ads (3)
brands_source = load("search_discovery_brands")["data"]    # Nike-named brands (5)
brands_domain_source = load("get_brands_by_domain")["data"] # Nike domain brands (3)

all_real_ads = (ads_source or []) + (ad_source_alt or [])
all_real_brands = (brands_source or []) + (brands_domain_source or [])

assert all_real_ads, "no real ads to compose from — capture live fixtures first"
assert all_real_brands, "no real brands to compose from — capture live fixtures first"


# ---------------------------------------------------------------------------
# get_boards — invent 3 plausible boards
# ---------------------------------------------------------------------------

playground_boards = [
    {
        "id": "playground_board_competitor_research",
        "name": "Competitor Research",
        "description": "Saved ads from competitor campaigns for monthly review.",
        "created_at": "2025-09-01T12:00:00",
        "updated_at": "2026-04-22T08:30:00",
        "ad_count": len(all_real_ads),
        "brand_count": len(all_real_brands),
        "owner_id": "user_redacted",
    },
    {
        "id": "playground_board_holiday_campaigns",
        "name": "Holiday Campaigns 2026",
        "description": "Reference creative for Q4 holiday planning.",
        "created_at": "2025-11-15T10:00:00",
        "updated_at": "2026-05-01T14:00:00",
        "ad_count": 8,
        "brand_count": 2,
        "owner_id": "user_redacted",
    },
    {
        "id": "playground_board_winning_hooks",
        "name": "Winning Hooks",
        "description": "Ads with hooks that consistently outperform.",
        "created_at": "2026-01-10T09:30:00",
        "updated_at": "2026-05-12T18:45:00",
        "ad_count": 15,
        "brand_count": 4,
        "owner_id": "user_redacted",
    },
]

save("get_boards", {
    "metadata": base_simple_meta(),
    "data": playground_boards,
    "error": None,
})


# ---------------------------------------------------------------------------
# get_board_ads — ads tagged with the first playground board
# ---------------------------------------------------------------------------

board_ads_body = {
    "metadata": {**base_paginated_meta(len(all_real_ads)), "filters": {
        "board_id": playground_boards[0]["id"],
    }},
    "data": all_real_ads,
    "error": None,
}
save("get_board_ads", board_ads_body)


# ---------------------------------------------------------------------------
# get_brands_by_board_id — brands tracked inside the first board
# ---------------------------------------------------------------------------

board_brands_body = {
    "metadata": {**base_simple_meta()},
    "data": brands_domain_source,
    "error": None,
}
# the GET /board/brands endpoint uses offset pagination but the metadata
# shape from real captures only has the simple meta. Match that.
save("get_brands_by_board_id", board_brands_body)


# ---------------------------------------------------------------------------
# get_spyder_brands — the user's "tracked" brands
# Re-use the brand list, tag avatars so they look intentional.
# ---------------------------------------------------------------------------

spyder_brands = []
for b in brands_source[:4]:
    spyder_brands.append({**b})

save("get_spyder_brands", {
    "metadata": base_simple_meta(),
    "data": spyder_brands,
    "error": None,
})


# ---------------------------------------------------------------------------
# get_spyder_brand — the first tracked brand on its own
# ---------------------------------------------------------------------------

save("get_spyder_brand", {
    "metadata": base_simple_meta(),
    "data": spyder_brands[0] if spyder_brands else {},
    "error": None,
})


# ---------------------------------------------------------------------------
# get_spyder_brand_ads — ads attributed to the first Spyder brand
# ---------------------------------------------------------------------------

spyder_brand_id = spyder_brands[0]["id"] if spyder_brands else "playground_spyder_brand"
spyder_ads = copy.deepcopy(all_real_ads)
for a in spyder_ads:
    a["brand_id"] = spyder_brand_id

save("get_spyder_brand_ads", {
    "metadata": {**base_paginated_meta(len(spyder_ads)), "filters": {
        "brand_id": spyder_brand_id,
    }},
    "data": spyder_ads,
    "error": None,
})


# ---------------------------------------------------------------------------
# get_swipefile_ads — a curated saved-ads collection
# ---------------------------------------------------------------------------

swipefile_ads = copy.deepcopy(all_real_ads)
# Reverse so the most-recently-saved feels different from chronological order.
swipefile_ads.reverse()

save("get_swipefile_ads", {
    "metadata": {**base_paginated_meta(len(swipefile_ads)), "order": "saved_newest"},
    "data": swipefile_ads,
    "error": None,
})


# ---------------------------------------------------------------------------
# get_group_duplicates_by_ad_id — sibling ads sharing the same creative
# Real fixture came back empty (no detected duplicates for the seed ad).
# Compose with a couple of ads from the same brand as a realistic proxy.
# ---------------------------------------------------------------------------

seed_ad = ads_source[0] if ads_source else None
duplicates = []
if seed_ad and ad_source_alt:
    # Take ads from get_ads_by_brand_ids that share the seed brand if any,
    # otherwise fall back to the first 2 ads from that list.
    seed_brand = seed_ad.get("brand_id")
    siblings = [a for a in ad_source_alt if a.get("brand_id") == seed_brand]
    duplicates = (siblings or ad_source_alt)[:2]
    # Ensure the seed ad itself appears first in its own duplicate group.
    duplicates = [copy.deepcopy(seed_ad)] + duplicates

save("get_group_duplicates_by_ad_id", {
    "metadata": {**base_simple_meta()},
    "data": duplicates,
    "error": None,
})


print()
print(f"Playground rebuilt — fixtures in {FIX}")
