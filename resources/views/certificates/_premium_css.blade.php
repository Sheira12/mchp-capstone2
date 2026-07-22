<style>
/* ═══════════════════════════════════════════════════════════════
   MARY HELP OF CHRISTIANS PARISH — DIOCESAN CERTIFICATE FINAL
   Engine  : DomPDF v2.x  |  A4 Portrait  |  @page margin:0
   ───────────────────────────────────────────────────────────────
   BORDER STRATEGY (proven for DomPDF):
   • position:fixed on a zero-content div draws the border frame
     as a PDF annotation layer — it does NOT add to page height
     or create extra pages. The content below renders normally.
   • Two fixed divs = outer gold frame + inner navy frame
   • Content flows naturally, single page guaranteed
   ═══════════════════════════════════════════════════════════════ */

@page { size: A4 portrait; margin: 0; }

* { margin: 0; padding: 0; box-sizing: border-box; }

html, body {
    margin: 0; padding: 0;
    background: #FDFCF8;
    font-family: 'Times New Roman', Georgia, serif;
    color: #1F3A5F;
    font-size: 10pt;
    line-height: 1.4;
    width: 595pt;
}

/* ── FIXED BORDER OVERLAYS ─────────────────────────────────
   DomPDF position:fixed: specify all 4 edges (top/left/right/bottom)
   instead of width/height — this ensures all 4 border sides render.
   ──────────────────────────────────────────────────────── */

/* Outer gold border — 5pt, full A4, all 4 sides */
.border-outer {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    border: 5pt solid #D4AF37;
    background: transparent;
    z-index: 999;
}

/* Inner navy border — 1.2pt, inset 7pt */
.border-navy {
    position: fixed;
    top: 7pt; left: 7pt; right: 7pt; bottom: 7pt;
    border: 1.2pt solid #1F3A5F;
    background: transparent;
    z-index: 998;
}

/* Thin gold inner line — 0.5pt, inset 10pt */
.border-gold {
    position: fixed;
    top: 10pt; left: 10pt; right: 10pt; bottom: 10pt;
    border: 0.5pt solid rgba(212,175,55,0.7);
    background: transparent;
    z-index: 997;
}

/* ── CONTENT WRAPPER ─────────────────────────────────────
   Padding keeps content away from border edges.
   z-index:1 ensures content renders above border layers.
   ──────────────────────────────────────────────────────── */
.cert-content {
    position: relative;
    z-index: 1;
    padding: 16pt 26pt 14pt 26pt;
    background: #FDFCF8;
}

/* ═══════════════════════════════
   HEADER
   ═══════════════════════════════ */
.cert-header { text-align: center; margin-bottom: 0; }
.diocese-bar {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; font-weight: bold;
    letter-spacing: 3pt; text-transform: uppercase;
    color: #D4AF37;
    border-top: 1.5pt solid #D4AF37;
    border-bottom: 1.5pt solid #D4AF37;
    padding: 3pt 0;
    margin-bottom: 10pt;
    display: block;
}
.seal-ring {
    width: 66pt; height: 66pt;
    border-radius: 50%;
    border: 2pt solid #D4AF37;
    background: #fff;
    overflow: hidden;
    margin: 0 auto 6pt;
    display: block;
}
.seal-ring img { width: 66pt; height: 66pt; display: block; }
.parish-name {
    font-family: Georgia, serif;
    font-size: 14pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 0.5pt;
    display: block; margin-bottom: 2pt;
}
.parish-addr {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6pt; color: #8A9AB0;
    display: block; margin-bottom: 8pt;
}
.gold-orn    { display: block; text-align: center; line-height: 0; font-size: 0; }
.gold-orn-sm { display: block; text-align: center; line-height: 0; font-size: 0; margin: 3pt 0; }

/* ═══════════════════════════════
   TITLE
   ═══════════════════════════════ */
.cert-title-wrap { text-align: center; padding: 0 14pt 8pt; }
.cert-label {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 7pt; font-weight: bold;
    letter-spacing: 4pt; text-transform: uppercase;
    color: #D4AF37; display: block; margin-bottom: 3pt;
}
.cert-title {
    font-family: Georgia, serif;
    font-size: 21pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 1.5pt;
    text-transform: uppercase; line-height: 1.15;
    display: block; margin-bottom: 3pt;
}
.cert-sub {
    font-family: 'Times New Roman', serif;
    font-size: 8pt; font-style: italic;
    color: #9AAABB; display: block; margin-bottom: 5pt;
}
.cert-intro {
    font-family: 'Times New Roman', serif;
    font-size: 9.5pt; color: #4A5A6A; line-height: 1.6;
    display: block; padding: 0 14pt;
}

/* ═══════════════════════════════
   RECIPIENT NAMEPLATE
   ═══════════════════════════════ */
.recipient-wrap {
    text-align: center;
    border-top: 2pt solid #D4AF37;
    border-bottom: 2pt solid #D4AF37;
    background: rgba(212,175,55,0.06);
    padding: 10pt 14pt;
    margin: 8pt 0;
}
.recipient-name {
    font-family: Georgia, serif;
    font-size: 25pt; font-weight: bold;
    color: #1F3A5F; letter-spacing: 0.5pt; line-height: 1.15;
    display: block;
}
.recipient-name.lg { font-size: 19pt; }
.recipient-name.xl { font-size: 14pt; }
.recipient-role {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; font-weight: bold;
    letter-spacing: 2.5pt; text-transform: uppercase;
    color: #D4AF37; margin-top: 3pt; display: block;
}
.couple-and {
    font-family: 'Times New Roman', serif; font-size: 7.5pt;
    font-style: italic; color: #D4AF37;
    letter-spacing: 1.5pt; display: block; margin: 1.5pt 0;
}

/* ═══════════════════════════════
   DETAILS GRID
   ═══════════════════════════════ */
.details-wrap { padding: 6pt 0 8pt; }
.details-tbl  { width: 100%; border-collapse: collapse; }
.det-left  { width: 47%; vertical-align: top; padding-right: 12pt; border-right: 0.75pt solid rgba(31,58,95,0.15); }
.det-right { width: 47%; vertical-align: top; padding-left: 12pt; }
.det-gap   { width: 6%; }
.det-item  { border-bottom: 0.4pt solid rgba(31,58,95,0.08); padding-bottom: 6pt; margin-bottom: 6pt; }
.det-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.det-lbl {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 5.5pt; font-weight: bold;
    letter-spacing: 1pt; text-transform: uppercase;
    color: #D4AF37; display: block; margin-bottom: 1.5pt;
}
.det-val {
    font-family: 'Times New Roman', serif;
    font-size: 10.5pt; font-weight: bold;
    color: #1F3A5F; line-height: 1.2; display: block;
}
.det-val.na { color: #BBCCD8; font-weight: normal; font-style: italic; font-size: 9.5pt; }

/* ═══════════════════════════════
   ISSUANCE
   ═══════════════════════════════ */
.issuance-wrap {
    text-align: center;
    border-top: 0.75pt solid rgba(212,175,55,0.4);
    border-bottom: 0.75pt solid rgba(212,175,55,0.4);
    padding: 7pt 0;
    margin: 4pt 0 14pt;
    font-family: 'Times New Roman', serif;
    font-size: 9.5pt; color: #4A5A6A; line-height: 1.75;
}
.issuance-wrap b { color: #1F3A5F; }

/* ═══════════════════════════════
   SIGNATURES
   ═══════════════════════════════ */
.sig-wrap { padding: 0 0 8pt; }
.sig-tbl  { width: 100%; border-collapse: collapse; }
.sig-cell { width: 33.33%; text-align: center; vertical-align: bottom; }
.sig-line { border-top: 1.5pt solid #1F3A5F; padding-top: 4pt; margin: 0 auto; width: 88%; }
.sig-name { font-family: 'Times New Roman', serif; font-size: 10pt; font-weight: bold; color: #1F3A5F; }
.sig-title { font-family: Arial, Helvetica, sans-serif; font-size: 6pt; letter-spacing: 0.75pt; text-transform: uppercase; color: #9AAABB; margin-top: 2pt; }
.seal-circle {
    width: 52pt; height: 52pt; border-radius: 50%;
    border: 1.5pt dashed #D4AF37;
    background: rgba(212,175,55,0.04);
    margin: 0 auto 2pt; display: block; text-align: center;
}
.seal-label { font-family: Arial, Helvetica, sans-serif; font-size: 5.5pt; color: #D4AF37; text-transform: uppercase; letter-spacing: 0.8pt; }

/* ═══════════════════════════════
   FOOTER
   ═══════════════════════════════ */
.footer-wrap { padding: 6pt 0 8pt; border-top: 1pt solid rgba(212,175,55,0.55); margin-top: 4pt; }
.ft-tbl    { width: 100%; border-collapse: collapse; }
.ft-left   { width: 28%; vertical-align: top; }
.ft-center { width: 44%; vertical-align: top; text-align: center; }
.ft-right  { width: 28%; vertical-align: top; text-align: right; }
.ft-certno-lbl { font-family: Arial, sans-serif; font-size: 5pt; font-weight: bold; letter-spacing: 1pt; text-transform: uppercase; color: #D4AF37; display: block; }
.ft-certno-val { font-family: 'Courier New', monospace; font-size: 6.5pt; color: #4A5A6A; font-weight: bold; display: block; margin-top: 1pt; }
.ft-issued { font-family: Arial, sans-serif; font-size: 4.5pt; color: #BBCCD8; font-style: italic; display: block; margin-top: 2pt; }
.ft-contact-name { font-family: Arial, sans-serif; font-size: 6pt; font-weight: bold; color: #1F3A5F; display: block; }
.ft-contact-detail { font-family: Arial, sans-serif; font-size: 5pt; color: #9AAABB; line-height: 1.55; display: block; }
.ft-verify-note { font-family: Arial, sans-serif; font-size: 4pt; color: #BBCCD8; font-style: italic; display: block; margin-top: 1.5pt; }
.ft-qr img { width: 40pt; height: 40pt; display: block; margin-left: auto; }
.ft-qr-lbl { font-family: Arial, sans-serif; font-size: 4.5pt; color: #9AAABB; margin-top: 1.5pt; text-align: right; display: block; }
</style>
