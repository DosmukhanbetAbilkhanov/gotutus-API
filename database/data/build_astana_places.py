#!/usr/bin/env python3
"""One-off converter: 2GIS Astana Excel -> normalized JSON for AstanaPlacesSeeder.
Raw fields only; activity mapping + working-hours parsing happen in the PHP seeder.
Usage: python3 database/data/build_astana_places.py <xlsx> database/data/astana_places.json
"""
import sys, json, openpyxl

def first_phone(v):
    if not v: return None
    return str(v).split(',')[0].strip() or None

def clean(v):
    if v is None: return None
    s = str(v).strip()
    return s or None

def to_float(v):
    try: return round(float(str(v).strip().replace(',', '.')), 7)
    except (TypeError, ValueError): return None

def main(src, out):
    wb = openpyxl.load_workbook(src, read_only=True, data_only=True)
    ws = wb.active
    rows = []
    for r in ws.iter_rows(min_row=2, values_only=True):
        name = clean(r[0])
        if not name:
            continue
        rows.append({
            'name': name,
            'address': clean(r[2]),       # C
            'website': clean(r[3]),       # D
            'category': clean(r[4]),      # E (used for activity mapping in PHP)
            'hours': clean(r[6]),         # G (parsed by WorkingHoursParserService)
            'phone': first_phone(r[7]),   # H
            'instagram': clean(r[8]),     # I
            'latitude': to_float(r[9]),   # J
            'longitude': to_float(r[10]), # K
        })
    with open(out, 'w', encoding='utf-8') as f:
        json.dump(rows, f, ensure_ascii=False, indent=0)
    print(f'wrote {len(rows)} places -> {out}')

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2])
