---
description: Pixel-perfect Blade template reproduction from a reference PDF. Iterates autonomously comparing screenshots against source until matched.
allowed-tools: Bash, Read, Write, Edit, Browser, Screenshot
---

# Ralph Loop — Pixel-Perfect PDF Reproduction

Convert the PDF at `./reference-docs/Foord Equity Fund Class A at 2026-01-31.pdf` into the template at `fund-manager/resources/views/funds/show-equity.blade.php` as a pixel-perfect reproduction, keeping all current Blade/Laravel functionality intact.

Study the design references at `./design-reference/` — they are the guideline for fonts, spacing conventions, and colour palette.

## LOGIN

- URL: http://fund-manager.test/funds/11
- PDF: http://fund-manager.test/funds/11/pdf
- Username: guy.spriggs@spriggabyte.co.za
- Password: 123654789

## ITERATION TRACKING — DO THIS FIRST, EVERY TIME

Before doing ANY work, run this:

```bash
count=$(cat ./ralph-iteration-count 2>/dev/null || echo 0)
count=$((count + 1))
echo $count > ./ralph-iteration-count
echo "=== RALPH ITERATION $count of 20 ==="
if [ "$count" -gt 20 ]; then echo "MAX ITERATIONS REACHED — STOPPING"; fi
```

**If the count is greater than 20, STOP. Do not continue. Write a final summary to `./conversion-notes.md` and then run:**
```bash
echo "done" > ./ralph-complete
```
**Then stop working. Say "Ralph complete — max iterations reached." and nothing else.**

To reset for a fresh run: `rm -f ./ralph-iteration-count ./ralph-complete`

## WORKFLOW FOR EACH ITERATION

### Step 1 — Study the source
Read the reference PDF and study every visual detail — layout, spacing, fonts, colours, alignment, borders, tables, images, charts. On the first iteration, create `./pdf-spec.md` with extracted hex colours, font sizes/weights, column widths, padding, margins, and border styles.

### Step 2 — Implement
Update `fund-manager/resources/views/funds/show-equity.blade.php` to match the PDF. Use inline styles or a scoped `<style>` block. Keep it as a single self-contained file where possible. Embed any images as base64 data URIs if needed.

**Do NOT remove or hardcode any dynamic Blade variables** — keep all `{{ }}`, `@foreach`, `@if`, component includes, and model references.

### Step 3 — Compare the web view
1. Open http://fund-manager.test/funds/11 in the browser and take a full-page screenshot
2. Compare the screenshot against the original PDF page by page
3. List EVERY difference you find — no matter how small (wrong margin, font weight, line height, colour, alignment, spacing, border, padding, etc.)

### Step 4 — Compare the PDF output
1. Download the generated PDF from http://fund-manager.test/funds/11/pdf
2. Compare it against the source PDF — same systematic check as Step 3
3. Pay extra attention to: page margins, font substitution, colour shifts, page breaks, scale, and any header/footer the PDF engine injects
4. The PDF output is the FINAL deliverable — if you must choose between web view and PDF perfection, **prioritise the PDF**

### Step 5 — Fix and decide
- If differences exist on either output, fix them and go back to Step 3
- If the same issue has persisted for 5+ iterations, document what's blocking in `./conversion-notes.md` and move on to other differences
- If you cannot find ANY visual differences on BOTH outputs, run:
  ```bash
  echo "done" > ./ralph-complete
  ```
  Then commit your changes and say "Ralph complete — pixel perfect." and **stop working**.

### Step 6 — Commit
After each iteration, commit your changes:
```bash
git add -A
git commit -m "ralph iteration $(cat ./ralph-iteration-count): [describe what you fixed]"
```

## RULES

- Match exact colours — use an eyedropper approach on the PDF, extract hex values
- Match font sizes, weights, and line heights exactly
- Preserve all whitespace, padding, and margins precisely
- The HTML must look identical at the same viewport width as the PDF page width
- Do NOT settle for "close enough" — keep iterating
- Do NOT change any routes, controllers, or model logic — this is a VIEW-ONLY task
- If the PDF engine config needs adjusting (margins, scale, viewport), document the exact file and setting you changed in `./conversion-notes.md`
- Run autonomously — do not stop to ask questions. If something is ambiguous, make a judgement call and document your reasoning in `./conversion-notes.md`
- When you write `echo "done" > ./ralph-complete`, STOP WORKING. Do not output anything else after that.