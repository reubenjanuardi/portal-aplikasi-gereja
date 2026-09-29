---
version: 1
slug: "resources-views-welcome-blade-php"
primary_target: "resources/views/welcome.blade.php"
related_targets: []
---

# Surface brief — welcome page (`resources/views/welcome.blade.php`)

Scope: the public landing page at `/` only. Visitor mode: **Persuade** — a
volunteer or staff member of the church must recognise the portal, believe the
church's money is being handled seriously, and get inside.

## Audience, job, action

- **Audience:** internal church staff and volunteers — bendahara, operator
  kasir, administrators, majelis peninjau. Mostly volunteers, not
  professional accountants, working around full-time church duties.
- **Job:** open the portal they use to record and report the church's cash.
- **Action:** one primary action — enter the portal. The existing
  "Akses Dashboard" link and the header "Masuk Portal" are the same intent and
  must not both compete as primary.
- **Proof available:** the church's real 533-account Chart of Accounts
  (`database/seeders/CoA_Hosiana.csv`) and the real report set (buku besar,
  jurnal, jurnal umum, realisasi mingguan; PDF + Excel).
- **Constraints:** Indonesian copy; no public financial report exists, so the
  page must not promise public transparency. The module roster presents the
  finance module as the only live one, with the rest marked `Akan Datang`.
  `Pengaturan Portal` is an administrative back-office screen, not a module of
  the portal, so it is deliberately absent from the list.

## Changes after the first build

- **Module framing narrowed by user decision.** The section is now "Saat ini,
  baru modul keuangan": Keuangan is `Aktif`, the other ten are `Akan Datang`,
  and `Pengaturan Portal` was removed from the list. The earlier
  full-platform-vision framing was superseded.
- **Roles list removed from the close.** "Peran dalam portal" is gone; the
  close now carries only the identity block and a copyright line naming Komisi
  Inforkom, on the same ruled hairline grammar.
- **Back-to-top control added**, top right, in the slot's own gesture.
- **Hero gutter tightened to `lg:gap-x-6`** so the five-column ledger fits its
  5/12 column without overflow. Measured: the table needs 461px and the column
  is 473px. Widening the ledger to 6/12 was tried and rejected — it forced the
  display line onto three lines.

## Chosen direction and memorable moment

**Kotak Persembahan — the offering box, ruled by one crease.** The first
viewport is a full-bleed ink-navy field divided by a single enormous brass arc
that the page never repeats as a straight rule. The memorable moment is the
**proof ledger**: the right column is this church's actual Chart of Accounts and
its running balance, so the mechanism is shown rather than claimed, and no
feature cards appear anywhere.

## Direction contract

**THESIS.** The category always ships a centered hero, a pill badge, and three
bordered feature cards; the predictable opposite is a glowing financial-dashboard
hero with coin icons. Both are refused. This page is the front of a treasury, not
a product page: it opens on the money itself, ruled like the ledger the treasurer
already knows, and its only ornament is one curve.

**OWN-WORLD.** The offering box and the engraved document. Indonesian rupiah and
the GPIB seal are both engraved line work in a single ink, so the world is
engraving: hairline rules, ruled columns, right-aligned tabular figures, a plate
mark. Ground is cool paper white. Ink is the seal's own deep navy. Brass is the
single accent, for the live mark and the slot action. Display is a Didone — the
banknote face. No gradient, mesh, glow, blur, or glass anywhere.

**STORY.** The visitor understands that this church keeps real books, in its own
account codes, and can prove them. They believe the people who will read those
numbers are volunteers who can be held to account. They do one thing: enter.

**FIRST VIEWPORT.** A full-bleed navy field whose content block is at least
`74vh` (`lg:min-h-[74vh]`, vertically centred with `lg:content-center`), exiting
into one enormous brass crease of `lg:h-32` (viewBox `0 0 1440 160`). The
crease's lowest point is measured to land above the fold at 1280×800, 1440×900
and 1600×900 alike, so the ornament is never cut. Top rail: the seal mark on
its paper plate, the church name in tracked caps, the address right. Left seven
columns: the display line at 5.3rem (`xl`) carrying its own weight, one
sub-line at a 65ch measure, then the slot action — a full-width brass hairline
with the label riding it. Right five columns (`lg:col-span-5`): the proof
ledger, a real ruled table of this church's CoA codes with debit, credit, and a
running balance closing on a brass total rule (`border-t-2 border-brass/70`,
with the preceding data row's own rule suppressed so the two do not fight
across the table's collapsed border); its uraian column is `whitespace-nowrap`
so a ruled column never wraps. No badge,
no kicker, no cards. The primary action sits inside the left column, below the
fold-line of the display line.

**CONTAINER.** `max-w-rule` (78rem) fixes the content measure, so 1280, 1440,
1600 and 1920 share identical computed geometry. The first viewport is authored
once and verified at all four, plus 390.

**CREASE, BOTH ENDS.** The hero's crease fills the ink *above* the curve so the
navy exits along it. The close's crease is the mirror: it fills the ink *below*
the curve, so the navy field begins ON the curve instead of under a straight
edge. Both use `vector-effect="non-scaling-stroke"`, so the brass hairline is the
same weight at 390px and at 1920px. A close crease that fills above the curve
produces a navy lens floating over the block — do not do that.

**FORM.** "Kotak Persembahan", position 4 of the grounded list, rolled seed key
`4ef0220d`. Named raises carried into it: from paper-folds challenger 4, *one arc
not a grid*; from toyism challenger 1, *flat ink, no blending*; from shareware
challenger 2, *instrument console*; from teletext challenger 6, *the scaling cell
that never reflows*. Competitive alternates available on request: daylight
section (#3), variable type specimen (#5).

**FINISH.** unreviewed and undocumented is unfinished; this build ends with the
finish review, the verdict, DESIGN.md, and every shipping raster carrying its
provenance

## Unresolved

- Whether `resources/js/Pages/Welcome.vue` (unrouted, superseded by the Blade
  view) should be removed or left alone. Out of scope; untouched.
- Panel entry points `/keuangan` and `/settings` are not surfaced as separate
  CTAs; both remain reachable after login.
