<style>
/* ═══════════════════════════════════════════════════════════════
   MARY HELP OF CHRISTIANS PARISH — PREMIUM CERTIFICATE SYSTEM
   Engine: DomPDF v2.0.8  |  Paper: A4 Portrait (595 × 842 pt)
   @page margin: 0  →  full bleed, all padding on cert-body
   Fixed border overlay + fixed footer = content fills ~742pt

   VERTICAL BUDGET (pt):
   Header (diocese+seal+name+addr+divider) ≈ 120pt
   Title block (label+title+sub+intro)     ≈  80pt
   Recipient block                         ≈  50pt
   Details grid (4 rows × ~18pt)           ≈  80pt
   Issuance statement                      ≈  30pt
   Signature row                           ≈  55pt
   Spacings/margins                        ≈ 327pt
   ─────────────────────────────────────── ≈ 742pt ✓
   ═══════════════════════════════════════════════════════════════ */

@page { size: A4 portrait; margin: 0; }

* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    margin: 0; padding: 0;
    width: 595pt;
    background: #FDFCF8;
    font-family: 'Times New Roman', Georgia, serif;
    color: #1F3A5F;
    font-size: 10pt;
    line-height: 1.4;
}

/* ── Border SVG overlay ── */
.border-svg {
    position: fixed; top: 0; left: 0;
    width: 595pt; height: 842pt;
    z-index: 10; pointer-events: none;
}

/* ── Watermark ── */
.wm {
    position: fixed; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.028; z-index: 0; pointer-events: none;
}

/* ── Fixed footer (QR + cert no + contact) ── */
.cert-footer {
    position: fixed;
    bottom: 14pt; left: 22pt; right: 22pt;
    border-top: 1pt solid rgba(212,175,55,0.7);
    padding-top: 5pt;
    display: table; width: auto;
    z-index: 5; background: #FDFCF8;
}
.ft-left   { display: table-cell; vertical-align: middle; width: 28%; }
.ft-center { display: table-cell; vertical-align: middle; text-align: center; width: 44%; }
.ft-right  { display: table-cell; vertical-align: middle; text-align: right; width: 28%; }
.ft-certno-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; font-weight: bold;
    letter-spacing: 1pt; text-transform: uppercase; color: #D4AF37;
}
.ft-certno-val {
    font-family: 'Courier New', monospace;
    font-size: 6.5pt; color: #6A7A8A; margin-top: 1pt;
}
.ft-contact {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #9AAABB; line-height: 1.6;
}
.ft-qr img { width: 40pt; height: 40pt; display: block; margin-left: auto; }
.ft-qr-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; color: #9AAABB; margin-top: 1.5pt; text-align: right;
}

/* ══════════════════════════════════════════════════════
   MAIN CERT BODY — padding distributes content evenly
   Top 22pt + Bottom 80pt (footer) + sides 22pt
   ══════════════════════════════════════════════════════ */
.cert-body {
    position: relative; z-index: 3; width: 100%;
    padding: 22pt 26pt 80pt 26pt;
}

/* ── Diocese label ── */
.diocese {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; font-weight: bold;
    letter-spacing: 2.5pt; text-transform: uppercase;
    color: #D4AF37; text-align: center;
    margin-bottom: 10pt;
}

/* ── Seal / Logo ── */
.seal-ring {
    width: 68pt; height: 68pt;
    border-radius: 50%; border: 2pt solid #D4AF37;
    box-shadow: 0 0 0 4pt rgba(212,175,55,0.15);
    overflow: hidden;
    margin: 0 auto 7pt;
    background: #fff; display: block; text-align: center;
}
.seal-ring img { width: 68pt; height: 68pt; display: block; }

/* ── Parish name & address ── */
.parish-name {
    font-family: Georgia, serif;
    font-size: 13pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 0.75pt;
    text-align: center; margin-bottom: 2.5pt;
}
.parish-addr {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; color: #7A8A9A;
    text-align: center; margin-bottom: 10pt;
}

/* ── Gold divider ── */
.gold-div { text-align: center; margin-bottom: 10pt; line-height: 0; font-size: 0; }

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
    font-size: 21pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 2.5pt;
    text-transform: uppercase; text-align: center;
    line-height: 1.15; margin-bottom: 3pt;
}

/* ── Latin subtitle ── */
.cert-sub {
    font-family: 'Times New Roman', serif;
    font-size: 9pt; font-style: italic;
    color: #9AAABB; text-align: center; margin-bottom: 10pt;
}

/* ── Intro text ── */
.intro {
    font-family: 'Times New Roman', serif;
    font-size: 10pt; color: #4A5A6A;
    text-align: center; line-height: 1.7; margin-bottom: 8pt;
}

/* ── Recipient block ── */
.recipient-block {
    width: 100%; text-align: center;
    border-top: 1pt solid rgba(212,175,55,0.55);
    border-bottom: 1pt solid rgba(212,175,55,0.55);
    padding: 10pt 0; margin: 5pt 0 14pt;
    background: rgba(212,175,55,0.03);
}
.recipient-name {
    font-family: Georgia, serif;
    font-size: 24pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 1pt; line-height: 1.2;
}
.recipient-role {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; font-weight: bold;
    letter-spacing: 2.5pt; text-transform: uppercase;
    color: #D4AF37; margin-top: 4pt;
}

/* ── Details grid ── */
.det-grid {
    width: 100%; border-collapse: collapse;
    margin-bottom: 14pt; display: table;
}
.det-col {
    display: table-cell; width: 50%; vertical-align: top;
}
.det-col:first-child { padding-right: 14pt; border-right: 0.75pt solid rgba(31,58,95,0.15); }
.det-col:last-child  { padding-left: 14pt; }

.det-item {
    margin-bottom: 9pt; padding-bottom: 8pt;
    border-bottom: 0.5pt solid rgba(31,58,95,0.08);
}
.det-item:last-child { border-bottom: none; margin-bottom: 0; }

.det-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; font-weight: bold;
    letter-spacing: 1pt; text-transform: uppercase;
    color: #D4AF37; margin-bottom: 2.5pt;
}
.det-val {
    font-family: 'Times New Roman', serif;
    font-size: 11pt; color: #1F3A5F;
    font-weight: bold; line-height: 1.35;
}
.det-val.na {
    color: #B8C4CC; font-weight: normal;
    font-style: italic; font-size: 10pt;
}

/* ── Issuance statement ── */
.issuance {
    font-family: 'Times New Roman', serif;
    font-size: 10pt; color: #4A5A6A;
    text-align: center; line-height: 1.8;
    margin-bottom: 16pt; padding: 8pt 10pt;
    border-top: 0.75pt solid rgba(212,175,55,0.3);
    border-bottom: 0.75pt solid rgba(212,175,55,0.3);
    width: 100%;
}
.issuance b { color: #1F3A5F; }

/* ── Signature row ── */
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
    padding-top: 5pt; margin-top: 36pt;
}
.sig-name {
    font-family: 'Times New Roman', serif;
    font-size: 10pt; font-weight: bold; color: #1F3A5F;
}
.sig-title {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; letter-spacing: 0.75pt;
    text-transform: uppercase; color: #9AAABB; margin-top: 2pt;
}
.seal-cell {
    display: table-cell; text-align: center;
    vertical-align: middle; width: 33.33%;
}
.seal-circ {
    width: 50pt; height: 50pt; border-radius: 50%;
    border: 1.5pt dashed #D4AF37;
    background: rgba(212,175,55,0.04);
    margin: 0 auto 2pt; display: block;
    text-align: center; line-height: 50pt;
}
.seal-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #D4AF37;
    letter-spacing: 0.5pt; text-transform: uppercase;
}
</style>
