---
name: Kotak Persembahan
description: The engraved-document world of the GPIB Jemaat Hosiana Jakarta portal - ink navy, cool paper, one brass accent, ruled like a ledger.
colors:
  paper: "#F6F5F1"
  paper-deep: "#ECE9E1"
  ink: "#0A1D3D"
  ink-lift: "#132A52"
  brass: "#C0973A"
  brass-deep: "#8A6624"
  brass-rule: "#A97F2E"
  slate: "#5A6A8C"
  mist: "#93A3C0"
typography:
  display:
    fontFamily: "Bodoni Moda, 'Bodoni Moda Fallback', Georgia, serif"
    fontSize: "2.6rem / 3.6rem@sm / 4.6rem@lg / 5.3rem@xl"
    fontWeight: 500
    lineHeight: "1.02"
    letterSpacing: "-0.02em"
  headline:
    fontFamily: "Bodoni Moda, 'Bodoni Moda Fallback', Georgia, serif"
    fontSize: "2.1rem / 2.5rem@sm / 3.1rem@xl"
    fontWeight: 400
    lineHeight: "1.1"
    letterSpacing: "-0.015em"
  title:
    fontFamily: "Archivo, 'Archivo Fallback', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'"
    fontSize: "1.0625rem / 0.9375rem@compact"
    fontWeight: 600
  body:
    fontFamily: "Archivo, 'Archivo Fallback', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'"
    fontSize: "1.0625rem / 0.8125rem@small"
    lineHeight: "1.7"
  label:
    fontFamily: "'Archivo Narrow', Archivo, 'Archivo Fallback', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'"
    fontSize: "0.6875rem"
    fontWeight: 500
    lineHeight: "1.1"
    letterSpacing: "0.18em"
  figure:
    fontFamily: "Archivo, 'Archivo Fallback', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'"
    fontSize: "0.75rem@ledger / 0.8125rem@table"
    fontWeight: 400
    fontFeature: "'tnum' 1"
  app-ui:
    fontFamily: "Figtree, ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Liberation Sans', sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'"
    fontWeight: 400
rounded:
  hairline: "1px"
  full: "9999px"
spacing:
  measure: "78rem"
  slot: "1.35rem"
components:
  slot-action:
    textColor: "{colors.paper}"
    typography: "Bodoni Moda 500 1.75rem/1.1 -0.01em"
    padding: "1.35rem 0"
    width: "100%"
  slot-arrow:
    textColor: "{colors.brass}"
    size: "1.25rem"
  ledger-cell:
    textColor: "{colors.paper}"
    typography: "Archivo 400 0.75rem/1.5 'tnum' 1"
  ledger-total:
    textColor: "{colors.brass}"
    typography: "Archivo 500 0.75rem/1.5 'tnum' 1"
  report-row-title:
    textColor: "{colors.ink}"
    typography: "Archivo 600 1.0625rem"
  report-row-note:
    textColor: "{colors.slate}"
    typography: "Archivo 400 1rem/1.625"
  module-status-active:
    textColor: "{colors.brass-deep}"
    typography: "Archivo Narrow 500 0.6875rem/1.1 0.18em, uppercase"
  plate:
    backgroundColor: "{colors.paper}"
    rounded: "{rounded.full}"
    size: "2.75rem"
  skip-link:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.paper}"
    typography: "Archivo Narrow 500 0.6875rem/1.1 0.18em, uppercase"
    padding: "0.75rem 1rem"
  crease:
    width: "100%"
    height: "8rem"
---

# Design System: Kotak Persembahan

## Overview

**Creative North Star: "Kotak Persembahan — the offering box, ruled by one crease."**

The offering box and the engraved document are the same object. Indonesian rupiah banknotes and the GPIB church seal are both line work cut in a single ink — hatching, guilloche, concentric lettering, a plate mark around the vignette — so this system's material is *engraving*, not interface chrome. Everything here is a consequence of that: hairline rules instead of filled bars, ruled columns that hold their measure, right-aligned figures set in tabular numerals, and a mark that sits on its own paper plate. Density is a ledger's density, not a dashboard's. The reading order is the treasurer's: here is the account code, here is the debit, here is the running balance.

The palette is committed to two grounds and one accent. Deep ink navy owns the first viewport as a full-bleed field; cool paper white — a stock, not a cream — is the document ground the reader is returned to; brass is the single accent, and it is spent like foil: a rule, a status, an arrow, one italic word. There is no third ground and no second accent. Elevation is flat in the strict sense: there is no `box-shadow` anywhere in this system, and depth is carried instead by tonal layering (`ink` against `paper`, `paper-deep`, `slate`, `mist` for recession), by hairline rules, and by the plate.

The category's defaults are refused, and the refusals are the craft floor, not preferences. No centred hero with a pill badge. No three same-size icon + heading + text cards used as page structure. No glowing financial-dashboard hero with coin icons. No gradient, mesh, glow, blur, or glass. No section numbers, no kicker or eyebrow above a heading, no hard offset shadows, no monospace worn as a costume, no unicode or emoji glyphs standing in for icons. Scope is stated honestly: this world governs the redesigned public surface. The application's `fontFamily.sans` is still Figtree and still is what Laravel, Inertia and Filament use; Filament's own primary is still `Color::Indigo` on `/keuangan` and `Color::Slate` on `/settings`; the auth screens are stock Breeze in Figtree. Those are incumbent, and they are not part of this world.

**Key Characteristics:**

- **Flat ink.** Every colour field is a plane. No gradient, mesh, glow, blur, or glass anywhere.
- **One accent, spent like foil.** Brass marks, strokes, or words. It never fills a field.
- **Hairlines, not bars.** Boundaries are 1px. The single 2px rule in the system is a ledger's brass total.
- **Figures never move.** Tabular numerals inside `whitespace-nowrap` cells; a column holds its measure.
- **Square corners.** The only circles in the system are the seal's paper plate and a 6px brass dot.
- **One authored motion,** and it answers only hover or focus.
- **Indonesian copy, set verbatim.** This document is written in English; the interface is not.

## Colors

A two-ground palette — one ink, two papers, one brass — with a cool grey-blue tint pulled from the ink itself rather than from a neutral gray ramp.

### Primary
- **Seal Navy** (`{colors.ink}`): the GPIB seal's own blue, carried onto the page. It is the first viewport, full-bleed, and it returns for the closing field. Body text on paper is the same ink, so the mark and the prose are literally the same colour.
- **Lifted Ink** (`{colors.ink-lift}`): one step off the navy, reserved for a second ink plane when a surface inside the field needs to separate tonally rather than by rule. Declared in `tailwind.config.js`; not yet placed on a shipped surface.

### Secondary
- **Communion Brass** (`{colors.brass}`): the single accent. It reads on the navy field — the total rule and total figures in the ledger, the arrows in the slot and the back-to-top control, the active module's status word, and one italic word in the display line. It is never a large fill.
- **Engraved Brass** (`{colors.brass-deep}`): the text-weight brass, for brass that must be read on paper. It carries the live module status ("Aktif") and nothing else.
- **Crease Brass** (`{colors.brass-rule}`): the hairline-weight brass for non-text boundaries on paper. It is the stroke of the crease, the caret colour, and the focus ring on the ink field.

### Neutral
- **Document Stock** (`{colors.paper}`): the ground. The page background, the ledger's text, and the plate the seal sits on.
- **Foolscap** (`{colors.paper-deep}`): one step down from the stock; the scrollbar track, and the second paper plane when a surface needs to be paper-on-paper.
- **Ledger Slate** (`{colors.slate}`): secondary text and small print on paper — a report's description, a module's scope, a module still to come. It is tinted from the ink, not from gray.
- **Register Mist** (`{colors.mist}`): secondary text and small print on the ink field — the hero's sub-line, the address, the ledger's column heads, the slot's qualifier. It is the same tint as the slate, lifted for the dark ground.

### Named Rules

**The Flat Ink Rule.** Every colour field is a plane. There is no gradient, no mesh, no glow, no blur, no glass, anywhere, including inside the ink field. The single `linear-gradient` in the system is the 3px dotted leader, which is a *drawn* rule, not a tonal blend.

**The One Accent Rule.** Brass is the only accent, and it is always a mark, a stroke, or a word — never a fill. If a brass element is wide enough to read as a surface, it is wrong.

**The Seal's Own Ink Rule.** Ink is the GPIB seal's own blue carried onto the page, and the paper is a cool stock, not a cream. Do not warm the neutrals toward beige and do not re-tint the navy toward a generic blue; the two colours come from the same institution.

## Typography

**Display Font:** Bodoni Moda (with `Bodoni Moda Fallback`, then Georgia) — a Didone, the face of banknotes and certificates.
**Body Font:** Archivo (with `Archivo Fallback`) — a grotesque with true tabular figures, the workhorse that sets prose, labels and every number.
**Label/Mono Font:** Archivo Narrow (falling back to Archivo) — letterpress label furniture, uppercase and widely tracked. **There is no monospace face in this system.** Numbers are set in Archivo with `tnum`, never in a mono face for costume.

All twelve faces are self-hosted from `public/fonts/*.woff2` (167.7 KB total, `font-display: swap`), so the page makes no third-party font request. Two metric-matched local fallback faces are declared alongside them — `Bodoni Moda Fallback` (`size-adjust: 96%`, `ascent-override: 92%`, `descent-override: 24%`) and `Archivo Fallback` (`size-adjust: 99%`, `ascent-override: 96%`, `descent-override: 26%`) — so a failed file holds the page's measure instead of reflowing it. The display stack is a Tailwind `fontFamily` utility (`font-display`), not a component class, so the utilities layer owns it; do not redeclare `.font-display` in CSS.

**Character:** a Didone shouting over a grotesque that keeps its nerve. The display face carries the argument and is used at exactly one size per line; everything that has to be *read and compared* is Archivo. Narrow uppercase Archivo is the furniture — column heads, statuses, the slot's qualifier — and it is never set lowercase.

### Hierarchy

Every role is a **responsive step ladder**, not a single size. The `fontSize` values in the frontmatter carry the full ladder and are normative; the prose below names the steps and their jobs.

- **Display** (500, 2.6rem base / 3.6rem at `sm` / 4.6rem at `lg` / 5.3rem at `xl`, line-height 1.02, tracking -0.02em): the argument. One line, at most two, and it owns the field it sits in. The word that carries the turn is set in the 500 italic and given the accent.
- **Headline** (400, 2.1rem base / 2.5rem at `sm` / 2.7rem at `lg` / 3.1rem at `xl`, line-height 1.1, tracking -0.015em, `text-wrap: balance`): the section's claim. The closing headline takes the full ladder; an interior section headline stops at `sm`. Never centred, never numbered, never preceded by a kicker.
- **Title** (600, 1.0625rem for a report name / 0.9375rem for a module name in a ruled row): the name of a report, a module, a row. Archivo semibold, ink on paper.
- **Body** (400, 1.0625rem, line-height 1.7, up to 65ch): prose. On the ink field it drops to `mist`; on paper to `slate`. Small print steps to 0.8125rem, always in `slate` or `mist`.
- **Label** (Archivo Narrow 500, 0.6875rem, tracking 0.18em, line-height 1.1, uppercase): column heads, statuses, export formats, the slot's qualifier, the church's name. Tracking tightens to 0.12em only when a label must hold a narrow column.
- **Figure** (Archivo 400, `font-variant-numeric: tabular-nums` / `font-feature-settings: 'tnum' 1`): every number that is compared vertically — account codes, debit, credit, running balance. Set at 0.75rem inside the ruled ledger and 0.8125rem in the report rows, so a column of figures keeps one size.

### Named Rules

**The Scaling Cell Rule.** A ledger column holds its measure. Figures are tabular and the cells around them are `whitespace-nowrap`, so a column never wraps, re-flows, or re-aligns as its content changes. Below `sm` the ledger drops its Debit and Kredit columns rather than letting a column bend, and folds the figure into the description cell as `D 8.750.000`.

**The Three Voices Rule.** Display, figure, and label are this world's three voices and nothing else joins them. `fontFamily.sans` remains Figtree; this is an additive set of families for a redesigned surface, not an application-wide rebrand. Do not reach for a fourth voice — no serif body, no geometric sans, no mono.

## Layout

Two grounds and one measure. The first viewport is a full-bleed ink field; everything after it sits on the paper ground; the closing field returns to ink. Content is fixed at `max-w-rule` (78rem) with gutters of `px-5` (1.25rem) base, `px-8` (2rem) at `sm`, and `px-10` (2.5rem) at `lg`, so 1280, 1440 and 1600 share identical computed geometry. Breakpoints in use are `sm` (640px), `lg` (1024px) and `xl` (1280px).

Inside the measure, a 12-column grid with a 2.5rem column gap. The field splits 7 / 5 — the argument and its one action on the left, the proof ledger on the right. The paper sections split 4 / 8 — a statement and its note on the left, a ruled index on the right. Vertical rhythm comes from Tailwind's default scale; the surface adds only two values of its own, the 78rem measure and the slot's 1.35rem vertical padding. Sections are separated by `pb-24` (6rem) stepping to `pb-32` (8rem) at `lg`, and by the crease.

The first viewport is authored to hold: at `lg` the field's content block is at least `74vh` and vertically centred, so the crease's lowest point lands above the fold rather than being cut. Below `lg` the page is a single column in source order — identity rail, display line, sub-line, the action, its qualifier, then the ledger. The ledger is the only dense thing on the page, and it earns that density.

## Elevation & Depth

This system has no shadow vocabulary, because it has no `box-shadow` at all. Depth is tonal and structural. The ink field sits on the page as a plane, the paper ground is the plane beneath it, `paper-deep` is the plane beneath that, and `slate` and `mist` are the two tints that let a line recede without a line being drawn. Boundaries are made of hairlines — 14% ink on paper (`.hairline`), 10% to 16% paper on ink (`.hairline-invert`) — and the only heavier rule in the system is a ledger's 2px brass total, which is a *semantic* weight, not an elevation cue. The one object that appears to lift off a plane is the plate: a disc of paper carrying the seal.

### Shadow Vocabulary

None. The vocabulary is empty by design, not by omission. Depth comes from tone, rule, and the plate.

### Named Rules

**The No Shadow Rule.** There is no `box-shadow` in this system and no token to add one to. If a surface appears to need lifting, it needs a different tone of plane, a hairline, or a plate — not a shadow.

**The One Arc, Not a Grid Rule.** No section boundary is a straight rule. Planes part company on a single sweeping brass crease — a 1.5px stroke with `vector-effect="non-scaling-stroke"`, so the line is the same weight at 375px and at 1600px. The crease is the only ornament the system allows itself.

**The Single Interaction Rule.** Exactly one thing moves: the slot's `::after` scales from `scaleX(0)` to `scaleX(1)` and its arrow travels 0.5rem, both over 620ms on `cubic-bezier(0.16, 1, 0.3, 1)` — an exponential ease-out, no bounce, no overshoot. Nothing animates on arrival; the resting state is already the resting state. `prefers-reduced-motion: reduce` collapses every animation and transition duration to 0.01ms and sets `scroll-behavior: auto`, and the slot's motion is the only thing that has to honour it.

## Shapes

Square corners. `border-radius: 0` is the default and is not revisited. The only two radii in the system are the 1px corner on the focus ring — which keeps a hard ring from fusing with the rule it sits on — and the full circle (9999px) on the plate and the back-to-top control. There are no rounded rectangles, no pills, no badges, no icon tiles.

Strokes are hairlines: 1px by default, 1.5px for the crease, 2px only for a ledger's total. The slot's resting rule is 1px brass at 45% alpha, so the accent is present before it is earned; the crease draws the full-strength line over it on hover or focus. The ledger's internal row rules step down to 5% paper so the table reads as a field of rules rather than a stack of them.

The dotted leader is a 1px-tall element whose dots are drawn with a 1px gradient stop on a 3px pitch, repeated on the x-axis and positioned at 50%. It is a drawn rule in the manner of a printed index, and it is the one place a `linear-gradient` is legitimate.

The crease is an inline SVG at `viewBox="0 0 1440 160"` with `preserveAspectRatio="none"`, so the arc stretches to the container instead of letterboxing. The field crease is two paths — a filled path in ink that lets the navy field terminate on the curve rather than on a straight edge, and the brass stroke over it. The closing crease is stroke-only, on the same non-scaling stroke.

## Components

This document is written in English so that agents extending the system can read it; **every user-facing string in this system is Indonesian and is set verbatim** — "Masuk Portal", "Saldo berjalan", "Contoh pencatatan", "Aktif", "Segera", "Lewati ke konten utama". Do not translate, anglicise, or "improve" product copy, and do not move a label into a kicker or an eyebrow.

### The Slot (the page's one action)

The portal entry is a machine with a labelled field, not a lifestyle button. It is a full-width anchor whose label rides a brass hairline.

- **Shape:** square, no radius, no fill of its own; it inherits the ink field it stands on.
- **Structure:** `display: flex`, `justify-content: space-between`, `align-items: center`, `gap: 1.5rem`, `padding: 1.35rem 0`, `width: 100%`.
- **Label:** the display face at 500, 1.6rem stepping to 1.75rem at `sm`, tracking -0.01em, in `paper`. The qualifier ("Akun gereja") is a `.label-cap` in `mist`, hidden below `sm`.
- **Arrow:** a 20px inline SVG at `stroke-width: 1.5`, `stroke-linecap: round`, in `brass`. Not an icon font, not a glyph.
- **Rule:** `border-top: 1px solid rgb(192 151 58 / 0.45)` at rest; a `::after` pseudo-element carries the full-strength brass and draws itself across on hover or focus.
- **Hover / Focus:** the crease scales X from 0 to 1 and the arrow translates 0.5rem, both 620ms `cubic-bezier(0.16, 1, 0.3, 1)`. The focus ring flips to brass inside `[data-surface='ink']` so it still meets 3:1 on navy. The second instance of the slot, in the closing field, softens its resting rule to `border-t-white/20`; the motion is identical.

### The Ruled Ledger (proof table)

A real table, not a card grid. It carries the account code, the description, debit, kredit, and the running balance, and it closes on a brass total.

- **Frame:** 1px `rgb(255 255 255 / 0.10)` on the ink field, with a matching rule under the header band and above the caption. Square, no fill, no shadow.
- **Head band:** `.label-cap` in `mist` on the left, the report's name in `brass` on the right, `px-5 py-4`, `items-baseline`.
- **Body:** 0.75rem, `.num` (tabular), `whitespace-nowrap` on every cell, `px-3` on the first and last column and `px-1.5` between, `py-3`. Codes and amounts in `mist` and `paper`; amounts right-aligned.
- **Row rules:** 1px `rgb(255 255 255 / 0.05)`. The last data row drops its own rule so it does not fight the total's under a collapsed border.
- **Total:** `border-top: 2px solid rgb(192 151 58 / 0.70)`, label and figure at weight 500 in `brass`, figure right-aligned in tabular numerals.
- **Caption:** 0.75rem in `mist`, under its own 1px rule, and it says in Indonesian that the figures are an illustration. Demonstration data is always labelled as demonstration data.
- **States:** none. Rows do not tint, lift, or highlight on hover; this world has one interaction and it is the slot.

### The Ruled Row (report list row)

A two-column row under a shared hairline: name and description on the left, a `.label-cap` export tag right-aligned. Title 600 at 1.0625rem in `ink`; description 1rem, `leading-relaxed`, up to 65ch, in `slate`; tag `.label-cap` in `slate`, `shrink-0`. Padding `py-6`, `gap-x-6`, `items-baseline`, and the row's bottom rule is `.hairline`. Below `sm` the tag drops under the description.

### The Module Index Row

Four fields in one flex-wrap row, baseline-aligned, `gap-x-5`, `py-4`, `.hairline` above and below. The module name is 500 at 0.9375rem in `ink`; the scope is 0.8125rem in `slate`, capped at 19rem and dropped to its own line below `sm`; a `.leader` fills the space between them and is hidden below `sm`; the status is `.label-cap`, pushed right — `brass-deep` for "Aktif", `slate` for "Segera". A live status is the one place `brass-deep` appears as text.

### The Label Cap

The system's most repeated piece of furniture: Archivo Narrow 500, `text-transform: uppercase`, `letter-spacing: 0.18em`, 0.6875rem, `line-height: 1.1`. It labels a column head, a status, an export format, a section's qualifier, and the church's own name. Its colour is always a tint of the ground it sits on: `mist` on ink, `slate` on paper, `brass` when it names the live report, `paper/90` for identity.

### The Plate

A full circle of `paper` (9999px) that the seal sits on, 2.75rem in the top rail and 3.5rem in the closing field, with `object-contain` and `overflow: hidden`. The plate is how a mark is allowed to be a mark without being ornament.

### The Crease

A 1.5px brass arc, inline SVG, `preserveAspectRatio="none"`, `vector-effect="non-scaling-stroke"`, `aria-hidden`. Height steps 4rem → 6rem → 8rem across the breakpoints for the field crease, 3rem → 4rem for the closing one.

The fill direction is what makes the crease work, and it differs by end. The field crease fills the ink **above** the curve, so the navy plane *exits* along it. The closing crease is the mirror: it fills the ink **below** the curve, so the navy plane *begins* on it. Filling above on the closing crease leaves a navy lens floating above a straight navy block — the curve stops being the boundary and becomes a decoration. Measure the result: the crease's lowest painted pixel must clear the fold at 1280×800.

### The Dotted Leader

1px tall, `flex: 1`, minimum 1rem, translated up 0.3em onto the text's baseline, drawn as a 3px-pitch dot rule in `rgb(10 29 61 / 0.28)`. It is how a printed index fills the space between an entry and its status.

### The Back to Top

A 2.75rem ink circle with a 1px brass hairline and a brass arrow, fixed 1.5rem from the top-right. It is the slot's gesture, shrunk: the hairline goes solid on hover/focus and the arrow travels 0.25rem upward, on the same 620ms `cubic-bezier(0.16, 1, 0.3, 1)`.

Revealed by an `IntersectionObserver` on the first viewport rather than a scroll listener, so it costs nothing per scroll event. It is hidden with `visibility: hidden` and `pointer-events: none` — not `opacity: 0` alone — so while it is invisible it is genuinely out of the tab order. The click honours `prefers-reduced-motion` explicitly, because the global `scroll-behavior: smooth` would otherwise be overridden by the JS `behavior` option.

### The Skip Link

`Lewati ke konten utama`, visually hidden until focused, then fixed at 1.5rem from the top-left on `ink` in `paper`, `px-4 py-3`, with a brass focus ring. `.label-cap`, because the keyboard path is styled like the rest of the system.

### Focus & Selection (global, from `app.css`)

The focus ring is a 2px solid ink outline with `outline-offset: 3px` and a 1px radius, overridden to brass inside `[data-surface='ink']` so it meets 3:1 on either ground. `::selection` is brass background on ink text. `caret-color` is `brass-rule` and the scrollbar is ink thumb on `paper-deep` track. Links and `<u>` take a 1px underline at `text-underline-offset: 0.22em`.

## Do's and Don'ts

### Do:
- **Do** carry the ledger before the claim. Any figure shown is set in `.num` inside a `whitespace-nowrap` ruled column, and anything that is an illustration says so in Indonesian underneath the table.
- **Do** part two planes on a crease — one inline SVG arc at 1.5px with `preserveAspectRatio="none"` and `vector-effect="non-scaling-stroke"`, never a straight section rule.
- **Do** spend brass as a mark: a rule, a status, an arrow, a dot, or one italic word. It never becomes a fill.
- **Do** keep the focus ring legible on whichever ground it lands: 2px solid ink with `outline-offset: 3px` on paper, brass inside `[data-surface='ink']`.
- **Do** set every label in `.label-cap` and keep the church's own Indonesian copy verbatim.
- **Do** author at `max-w-rule` (78rem) and let 1280, 1440 and 1600 share identical computed geometry.
- **Do** reach for depth by changing tone — a different plane, `slate` or `mist`, a hairline, or the plate.

### Don't:
- **Don't** add a shadow. There is no shadow vocabulary to extend, and none should be created.
- **Don't** use a gradient, mesh, glow, blur, or glass as atmosphere. The only `linear-gradient` allowed is the 3px dotted leader, which is a drawn rule.
- **Don't** centre a hero, hang a pill badge above it, put a kicker or eyebrow above a heading, or number the sections.
- **Don't** build a page out of three same-size icon + heading + text feature cards, or a row of stat tiles.
- **Don't** reach for the glowing financial-dashboard motif, coin icons, or chart chrome. This is the front of a treasury, not a product page.
- **Don't** set a UI number in a monospace face for costume. Tabular figures come from Archivo's `tnum`, and no mono face exists in this system.
- **Don't** use a unicode or emoji glyph as an icon. Inline an SVG at 1.5px stroke, or use nothing.
- **Don't** animate anything on arrival. The only motion in the system answers hover or focus, on the slot alone, at 620ms `cubic-bezier(0.16, 1, 0.3, 1)`, and it must honour `prefers-reduced-motion`.
- **Don't** let a ruled column wrap, and don't let a cell's figures change width as its value changes.
- **Don't** round a corner. The only radii in the system are the 1px focus-ring corner and the full circle of the plate and the brass dots.
- **Don't** rebrand the application. `fontFamily.sans` is still Figtree, Filament's primary is still `Color::Indigo` on `/keuangan` and `Color::Slate` on `/settings`, and the auth screens are stock Breeze in Figtree. Those are incumbent, outside this world.
