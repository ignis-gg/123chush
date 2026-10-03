#!/usr/bin/env python3
"""Checks RU translations (assets/ru/tr/*.json) against the UA source
(assets/ru/src/*.json): same JSON shape, untouched non-text values, same
HTML/Gutenberg markup skeleton, no leftover Ukrainian letters, valid
ru_slug. Usage: ru-validate.py [file.json ...]  (default: every tr file).
Exit code 1 if any error.
"""
import json
import re
import sys
from pathlib import Path

BASE = Path(__file__).resolve().parent / 'assets' / 'ru'
CYR = re.compile(r'[Ѐ-ӿ]')
UA_ONLY = re.compile(r"[іїєґІЇЄҐ]")
# Proper names that legitimately stay Ukrainian in the RU text.
UA_ALLOWED = ['Шлях до мрії О.К.', 'Шлях До Мрії О.К.', 'Шлях до мрії']
TAG = re.compile(r'<(/?)([a-zA-Z][a-zA-Z0-9-]*)([^>]*)>')
WP_COMMENT = re.compile(r'<!--\s*/?wp:[^>]*?-->')
TEXT_ATTRS = re.compile(r'\s(alt|title|aria-label|placeholder|content|data-tip|value)="[^"]*"')


def skeleton(html):
    tags = []
    for m in TAG.finditer(html):
        attrs = TEXT_ATTRS.sub('', m.group(3)).strip()
        tags.append(m.group(1) + m.group(2).lower() + (' ' + attrs if attrs else ''))
    return tags


def wp_comments(html):
    # Block attribute JSON can hold text (e.g. placeholders) — compare
    # block names only, plus the attribute JSON when it has no Cyrillic.
    out = []
    for c in WP_COMMENT.findall(html):
        out.append(re.sub(r'"[^"]*[Ѐ-ӿ][^"]*"', '"…"', c))
    return out


def walk(src, tr, path, errors, warnings):
    if isinstance(src, dict):
        if not isinstance(tr, dict):
            errors.append(f'{path}: expected object')
            return
        if set(src) != set(tr) - ({'ru_slug'} if path == '$' else set()):
            missing = set(src) - set(tr)
            extra = set(tr) - set(src) - ({'ru_slug'} if path == '$' else set())
            errors.append(f'{path}: keys differ, missing={sorted(missing)} extra={sorted(extra)}')
        for k in src:
            if k in tr:
                walk(src[k], tr[k], f'{path}.{k}', errors, warnings)
        return
    if isinstance(src, list):
        if not isinstance(tr, list) or len(src) != len(tr):
            errors.append(f'{path}: list length differs')
            return
        for i, (a, b) in enumerate(zip(src, tr)):
            walk(a, b, f'{path}[{i}]', errors, warnings)
        return
    if not isinstance(src, str):
        if src != tr:
            errors.append(f'{path}: non-text value changed {src!r} -> {tr!r}')
        return
    if not isinstance(tr, str):
        errors.append(f'{path}: expected string')
        return
    if path in ('$.slug', '$.type', '$.id', '$.taxonomy'):
        if src != tr:
            errors.append(f'{path}: must stay unchanged')
        return
    if not CYR.search(src):
        if src != tr:
            errors.append(f'{path}: value without Ukrainian text must stay unchanged: {src[:80]!r} -> {tr[:80]!r}')
        return
    if src.strip() and src == tr and UA_ONLY.search(src):
        errors.append(f'{path}: not translated: {src[:80]!r}')
    rest = tr
    for name in UA_ALLOWED:
        rest = rest.replace(name, '')
    m = UA_ONLY.search(rest)
    if m:
        ctx = rest[max(0, m.start() - 30): m.end() + 30].replace('\n', ' ')
        warnings.append(f'{path}: Ukrainian letter left: …{ctx}…')
    if '<' in src:
        if skeleton(src) != skeleton(tr):
            s, t = skeleton(src), skeleton(tr)
            diff = next((i for i, (a, b) in enumerate(zip(s, t)) if a != b), min(len(s), len(t)))
            errors.append(f'{path}: HTML markup differs at tag #{diff}: src={s[diff:diff+2]} tr={t[diff:diff+2]} (src {len(s)} tags, tr {len(t)})')
        if wp_comments(src) != wp_comments(tr):
            errors.append(f'{path}: Gutenberg block comments differ')
    for url in re.findall(r'href="([^"]*)"', src):
        if url not in tr:
            errors.append(f'{path}: link {url} missing/changed')


def check(tr_path):
    src_path = BASE / 'src' / tr_path.name
    errors, warnings = [], []
    try:
        tr = json.loads(tr_path.read_text(encoding='utf-8'))
    except json.JSONDecodeError as e:
        return [f'invalid JSON: {e}'], []
    src = json.loads(src_path.read_text(encoding='utf-8'))
    walk(src, tr, '$', errors, warnings)
    if isinstance(src, dict) and 'id' in src and 'type' in src:
        slug = tr.get('ru_slug', '')
        if not re.fullmatch(r'[a-z0-9]+(-[a-z0-9]+)*', slug or ''):
            errors.append(f'$.ru_slug: missing or not latin-lowercase-hyphen: {slug!r}')
    return errors, warnings


def main():
    files = [Path(a) for a in sys.argv[1:]] or sorted((BASE / 'tr').glob('*.json'))
    bad = 0
    for f in files:
        f = f if f.is_absolute() or f.exists() else BASE / 'tr' / f.name
        errors, warnings = check(f)
        status = 'OK' if not errors else 'FAIL'
        if errors:
            bad += 1
        print(f'{status} {f.name}' + (f' ({len(warnings)} warnings)' if warnings else ''))
        for e in errors:
            print('  ERROR', e)
        for w in warnings:
            print('  warn ', w)
    missing = sorted({p.name for p in (BASE / 'src').glob('*.json')} - {p.name for p in (BASE / 'tr').glob('*.json')})
    if not sys.argv[1:] and missing:
        print('NOT TRANSLATED YET:', ' '.join(missing))
    sys.exit(1 if bad else 0)


if __name__ == '__main__':
    main()
