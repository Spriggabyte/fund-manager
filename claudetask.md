PDF Parity Task
Goal
Make the Blade-rendered PDF visually identical to a reference PDF. The current output is ~90% there; close the remaining gap through iterative visual comparison until the two PDFs are indistinguishable at normal viewing scale.
Inputs

Reference PDF: </Users/gsadmin/Downloads/fund-10-2026-05-03.pdf>
Blade template: <fund-manager/resources/views/funds/pdf.blade.php> (and any partials/CSS it pulls in)
Reference templates for specifics: fund-manager/reference-docs
PDF generation command: <COMMAND_THAT_PRODUCES_THE_PDF> — e.g. php artisan pdf:generate or a route hit via curl. The output should land at <http://foord-funds.test/funds/10/pdf>.
Working directory for diffs: storage/app/pdf-diff/ (create if missing)

Workflow — repeat until parity
1. Generate the current output
Run the generation command. Confirm the output PDF exists and has the expected page count. If page count differs from the reference, that is the first thing to fix before going further.
2. Rasterise both PDFs page by page
Use pdftoppm at 150 DPI (install via apt-get install poppler-utils if missing):
bashmkdir -p storage/app/pdf-diff/ref storage/app/pdf-diff/gen storage/app/pdf-diff/diff
pdftoppm -r 150 -png <REFERENCE_PDF> storage/app/pdf-diff/ref/page
pdftoppm -r 150 -png <GENERATED_PDF> storage/app/pdf-diff/gen/page
3. Generate a per-page diff image
Use ImageMagick to highlight pixel differences (install imagemagick if missing). For each page:
bashcompare -metric AE -fuzz 5% \
  storage/app/pdf-diff/ref/page-N.png \
  storage/app/pdf-diff/gen/page-N.png \
  storage/app/pdf-diff/diff/page-N.png
The AE (absolute error) count printed to stderr is the number of differing pixels per page. Log these counts — they're your regression signal across iterations.
4. Visually inspect the diff
Use the view tool on each diff/page-N.png to see exactly where the differences sit. Then view the corresponding ref/page-N.png and gen/page-N.png side by side to understand what the difference is, not just where.
Categorise findings into:

Layout — element position, margins, padding, page breaks, column widths
Typography — font family, weight, size, line-height, letter-spacing, alignment
Colour — fills, strokes, backgrounds, opacity
Spacing — gaps between elements, list indentation, table cell padding
Borders & rules — thickness, style, colour, presence/absence
Images & vectors — size, position, aspect ratio, source asset
Text content — typos, missing/extra strings, dynamic data formatting (dates, numbers, currency)

5. Fix highest-impact differences first
Edit the Blade template and its CSS. Prefer:

Fixing structural/layout issues before cosmetic ones (a misaligned container often resolves several downstream diffs at once)
One logical change per iteration so regressions are easy to attribute
CSS over inline styles unless the template already uses inline (PDF renderers like dompdf, wkhtmltopdf, and Browsershot each have quirks — match the existing convention)

Be aware of renderer-specific gotchas:

dompdf: limited CSS3 support, no flexbox, quirky float behaviour, requires @page rules for margins
wkhtmltopdf / Snappy: stronger CSS support but webkit-old; watch for page-break-* rules and header/footer templates
Browsershot / Puppeteer: modern Chromium — most CSS works, but emulate print media and check @page size matches reference

6. Regenerate and re-diff
Re-run steps 1–3. The total AE pixel count across all pages should drop. If it goes up on a page you didn't touch, you've introduced a regression — investigate before continuing.
7. Stop when

Every page's AE count is below ~0.1% of total pixels (small anti-aliasing differences are unavoidable), AND
Visual inspection of each diff image shows no meaningful structural, typographic, or colour differences — only faint edge noise

If you hit a difference you genuinely can't close (e.g. a font the reference uses isn't installed, or the reference has an embedded raster that can't be reproduced from data), stop and report it explicitly with the page number, what's different, and what you tried.
Reporting
After each iteration, output a short status:

Total pixel diff before → after
Pages changed and which categories of fix were applied
Any new regressions and how they were resolved
Remaining known differences with rationale

Constraints

Don't change the data sources or the controller — only the template, partials, and styles
Don't introduce new dependencies without flagging it first
Preserve any dynamic Blade logic (@if, @foreach, variable interpolation) — the goal is visual parity on the current dataset, not hard-coding the reference content