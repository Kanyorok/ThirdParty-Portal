#!/usr/bin/env python3
import re
from pathlib import Path

root = Path('resources/views')
if not root.exists():
    print('resources/views not found; run from repo root')
    raise SystemExit(1)

# Replacement patterns ordered longest-first
replacements = [
    (re.compile(r"format\((['\"])d/m/Y H:i:s\1\)"), r"format(\1d M Y H:i:s\1)"),
    (re.compile(r"format\((['\"])d/m/Y H:i\1\)"), r"format(\1d M Y H:i\1)"),
    (re.compile(r"format\((['\"])d/m/Y\1\)"), r"format(\1d M Y\1)"),
    (re.compile(r"altFormat:\s*(['\"])d/m/Y\1"), r"altFormat: \1d M Y\1"),
    # Also handle bare occurrences in double/single quoted contexts (less-structured)
    (re.compile(r"(['\"])d/m/Y H:i:s(['\"])"), r"\1d M Y H:i:s\2"),
    (re.compile(r"(['\"])d/m/Y H:i(['\"])"), r"\1d M Y H:i\2"),
    (re.compile(r"(['\"])d/m/Y(['\"])"), r"\1d M Y\2"),
]

modified_files = []
for p in root.rglob('*.blade.php'):
    text = p.read_text(encoding='utf-8')
    original = text
    for pattern, repl in replacements:
        text = pattern.sub(repl, text)
    # Revert any accidental double-spacing around format tokens
    if text != original:
        # write backup
        backup = p.with_suffix(p.suffix + '.bak')
        backup.write_text(original, encoding='utf-8')
        p.write_text(text, encoding='utf-8')
        modified_files.append(str(p))

# Also modify the single exception of strtoupper around Carbon format if present
approval_file = Path('resources/views/procurement/procurementplan/departmentneeds/approval/index.blade.php')
if approval_file.exists():
    t = approval_file.read_text(encoding='utf-8')
    t_orig = t
    # Remove strtoupper( ... ) around Carbon::parse(...)->format('d M Y')
    t = re.sub(r"strtoupper\((\s*Carbon::parse\([^)]*\)->format\((['\"])d M Y\2\)\s*)\)", r"\1", t)
    if t != t_orig:
        backup = approval_file.with_suffix(approval_file.suffix + '.bak')
        backup.write_text(t_orig, encoding='utf-8')
        approval_file.write_text(t, encoding='utf-8')
        modified_files.append(str(approval_file))

print('Modified files:')
for f in modified_files:
    print(f)
print('Done')
