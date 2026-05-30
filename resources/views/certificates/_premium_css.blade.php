<style>
/* ═══════════════════════════════════════════════════════════════
   MARY HELP OF CHRISTIANS PARISH — PREMIUM CERTIFICATE SYSTEM
   Engine: DomPDF v2.0.8  |  Paper: A4 Portrait (595 × 842 pt)
   @page margin: 0  → full bleed, content padded via .cert-body
   Border drawn as a fixed full-page SVG overlay (most reliable
   method in DomPDF — no negative offsets, no clipping issues)
   Palette: Navy #1F3A5F · Gold #D4AF37 · Cream #FDFCF8
   ═══════════════════════════════════════════════════════════════ */

@page {
    size: A4 portrait;
    margin: 0;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    margin: 0;
    padding: 0;
    width: 595pt;
    background: #FDFCF8;
    font-family: 'Times New Roman', Georgia, serif;
    color: #1F3A5F;
    font-size: 10pt;
    line-height: 1.4;
}

/* ══════════════════════════════════════════════════════
   BORDER OVERLAY — full-page SVG, position:fixed
   Draws: outer gold rect, inner navy rect, corner roses,
   decorative side patterns, all at exact A4 dimensions.
   595pt × 842pt = A4 at 72dpi (DomPDF native units)
   ══════════════════════════════════════════════════════ */
.border-svg {
    position: fixed;
    top: 0; left: 0;
    width: 595pt;
    height: 842pt;
    z-index: 10;
    pointer-events: none;
}

/* ══════════════════════════════════════════
   WATERMARK — fixed, centered, behind content
   ══════════════════════════════════════════ */
.wm {
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.028;
    z-index: 0;
    pointer-events: none;
}

/* ══════════════════════════════════════════
   FIXED FOOTER — pinned to bottom of page
   Sits above border (z-index 5) but below
   border SVG overlay (z-index 10) — footer
   content is inside the border area so it
   doesn't need to go above the border SVG.
   ══════════════════════════════════════════ */
.cert-footer {
    position: fixed;
    bottom: 14pt;
    left: 22pt; right: 22pt;
    border-top: 1pt solid rgba(212,175,55,0.7);
    padding-top: 5pt;
    display: table;
    width: auto;
    z-index: 5;
    background: #FDFCF8;
}
.ft-left   { display: table-cell; vertical-align: middle; width: 28%; }
.ft-center { display: table-cell; vertical-align: middle; text-align: center; width: 44%; }
.ft-right  { display: table-cell; vertical-align: middle; text-align: right; width: 28%; }

.ft-certno-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; font-weight: bold;
    letter-spacing: 1pt; text-transform: uppercase;
    color: #D4AF37;
}
.ft-certno-val {
    font-family: 'Courier New', monospace;
    font-size: 6.5pt; color: #6A7A8A; margin-top: 1pt;
}
.ft-contact {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #9AAABB; line-height: 1.6;
}
.ft-qr img {
    width: 40pt; height: 40pt;
    display: block; margin-left: auto;
}
.ft-qr-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; color: #9AAABB;
    margin-top: 1.5pt; text-align: right;
}

/* ══════════════════════════════════════════
   MAIN CONTENT BODY
   Padded to stay inside the border area.
   Left/right: 22pt (border ~14pt + gap 8pt)
   Top: 18pt, Bottom: 70pt (footer reserve)
   ══════════════════════════════════════════ */
.cert-body {
    position: relative;
    z-index: 3;
    width: 100%;
    padding: 18pt 22pt 70pt 22pt;
}

/* ── Diocese label ── */
.diocese {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; font-weight: bold;
    letter-spacing: 2.5pt; text-transform: uppercase;
    color: #D4AF37; text-align: center;
    margin-bottom: 8pt;
}

/* ── Seal / Logo ── */
.seal-ring {
    width: 64pt; height: 64pt;
    border-radius: 50%;
    border: 2pt solid #D4AF37;
    box-shadow: 0 0 0 4pt rgba(212,175,55,0.15);
    overflow: hidden;
    margin: 0 auto 6pt;
    background: #fff;
    display: block; text-align: center;
}
.seal-ring img { width: 64pt; height: 64pt; display: block; }

/* ── Parish name & address ── */
.parish-name {
    font-family: Georgia, serif;
    font-size: 13pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 0.75pt;
    text-align: center; margin-bottom: 2pt;
}
.parish-addr {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; color: #7A8A9A;
    text-align: center; margin-bottom: 8pt;
}

/* ── Gold divider ── */
.gold-div {
    text-align: center; margin-bottom: 8pt;
    line-height: 0; font-size: 0;
}

/* ── Certificate label ── */
.cert-label {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; font-weight: bold;
    letter-spacing: 3.5pt; text-transform: uppercase;
    color: #D4AF37; text-align: center; margin-bottom: 3pt;
}

/* ── Main title ── */
.cert-title {
    font-family: Georgia, serif;
    font-size: 20pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 2.5pt;
    text-transform: uppercase; text-align: center;
    line-height: 1.15; margin-bottom: 3pt;
}

/* ── Latin subtitle ── */
.cert-sub {
    font-family: 'Times New Roman', serif;
    font-size: 8.5pt; font-style: italic;
    color: #9AAABB; text-align: center; margin-bottom: 8pt;
}

/* ── Intro text ── */
.intro {
    font-family: 'Times New Roman', serif;
    font-size: 9.5pt; color: #4A5A6A;
    text-align: center; line-height: 1.6; margin-bottom: 6pt;
}

/* ── Recipient block ── */
.recipient-block {
    width: 100%; text-align: center;
    border-top: 1pt solid rgba(212,175,55,0.55);
    border-bottom: 1pt solid rgba(212,175,55,0.55);
    padding: 8pt 0; margin: 4pt 0 10pt;
    background: rgba(212,175,55,0.03);
}
.recipient-name {
    font-family: Georgia, serif;
    font-size: 22pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 1pt; line-height: 1.2;
}
.recipient-role {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; font-weight: bold;
    letter-spacing: 2.5pt; text-transform: uppercase;
    color: #D4AF37; margin-top: 3pt;
}

/* ── Details grid — table for DomPDF ── */
.det-grid {
    width: 100%; border-collapse: collapse;
    margin-bottom: 10pt; display: table;
}
.det-col {
    display: table-cell; width: 50%; vertical-align: top;
}
.det-col:first-child {
    padding-right: 12pt;
    border-right: 0.75pt solid rgba(31,58,95,0.15);
}
.det-col:last-child { padding-left: 12pt; }

.det-item {
    margin-bottom: 7pt; padding-bottom: 6pt;
    border-bottom: 0.5pt solid rgba(31,58,95,0.08);
}
.det-item:last-child { border-bottom: none; margin-bottom: 0; }

.det-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; font-weight: bold;
    letter-spacing: 1pt; text-transform: uppercase;
    color: #D4AF37; margin-bottom: 2pt;
}
.det-val {
    font-family: 'Times New Roman', serif;
    font-size: 10.5pt; color: #1F3A5F;
    font-weight: bold; line-height: 1.3;
}
.det-val.na {
    color: #B8C4CC; font-weight: normal;
    font-style: italic; font-size: 9.5pt;
}

/* ── Issuance statement ── */
.issuance {
    font-family: 'Times New Roman', serif;
    font-size: 9.5pt; color: #4A5A6A;
    text-align: center; line-height: 1.7;
    margin-bottom: 10pt; padding: 6pt 0;
    border-top: 0.75pt solid rgba(212,175,55,0.3);
    border-bottom: 0.75pt solid rgba(212,175,55,0.3);
    width: 100%;
}
.issuance b { color: #1F3A5F; }

/* ── Signature row — table ── */
.sig-row {
    width: 100%; display: table;
    border-collapse: collapse;
}
.sig-cell {
    display: table-cell; text-align: center;
    vertical-align: bottom; width: 33.33%;
}
.sig-line {
    border-top: 1.5pt solid #1F3A5F;
    padding-top: 4pt; margin-top: 28pt;
}
.sig-name {
    font-family: 'Times New Roman', serif;
    font-size: 9.5pt; font-weight: bold; color: #1F3A5F;
}
.sig-title {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; letter-spacing: 0.75pt;
    text-transform: uppercase; color: #9AAABB; margin-top: 1.5pt;
}
.seal-cell {
    display: table-cell; text-align: center;
    vertical-align: middle; width: 33.33%;
}
.seal-circ {
    width: 44pt; height: 44pt; border-radius: 50%;
    border: 1.5pt dashed #D4AF37;
    background: rgba(212,175,55,0.04);
    margin: 0 auto 2pt; display: block;
    text-align: center; line-height: 44pt;
}
.seal-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #D4AF37;
    letter-spacing: 0.5pt; text-transform: uppercase;
}
</style>
