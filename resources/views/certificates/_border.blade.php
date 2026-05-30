{{--
    CERTIFICATE BORDER OVERLAY
    Full-page SVG at exact A4 dimensions (595pt × 842pt @ 72dpi).
    Drawn as position:fixed so it overlays every page without
    affecting document flow height.

    Layers (bottom → top):
      1. Cream background fill
      2. Outer gold border (3pt, inset 4pt from edge)
      3. Inner navy border (1pt, inset 10pt from edge)
      4. Gold corner roses (all 4 corners)
      5. Decorative mid-side gold diamonds (left & right)
      6. Subtle dot pattern along borders
--}}
<div class="border-svg">
<svg width="595pt" height="842pt" viewBox="0 0 595 842"
     xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">

    {{-- ── 1. Background fill ── --}}
    <rect width="595" height="842" fill="#FDFCF8"/>

    {{-- ── 2. Outer gold border (3pt stroke, inset 4pt) ── --}}
    <rect x="4" y="4" width="587" height="834"
          fill="none" stroke="#D4AF37" stroke-width="3"/>

    {{-- ── 3. Inner navy border (1pt stroke, inset 11pt) ── --}}
    <rect x="11" y="11" width="573" height="820"
          fill="none" stroke="#1F3A5F" stroke-width="1"/>

    {{-- ── 4. Second gold inner line (0.5pt, inset 14pt) ── --}}
    <rect x="14" y="14" width="567" height="814"
          fill="none" stroke="#D4AF37" stroke-width="0.5" stroke-dasharray="0"/>

    {{-- ══════════════════════════════════════════════════
         CORNER ORNAMENTS — all 4 corners
         Each corner: rose + L-bracket + arc motif
         ══════════════════════════════════════════════════ --}}

    {{-- ── TOP-LEFT corner ── --}}
    <g transform="translate(4,4)">
        {{-- L-bracket outer --}}
        <path d="M0 36 L0 0 L36 0" fill="none" stroke="#D4AF37" stroke-width="3"/>
        {{-- L-bracket inner --}}
        <path d="M7 28 L7 7 L28 7" fill="none" stroke="#1F3A5F" stroke-width="1"/>
        {{-- Arc flourish --}}
        <path d="M7 22 Q7 7 22 7" fill="none" stroke="#D4AF37" stroke-width="1.5"/>
        {{-- Center rose --}}
        <circle cx="7" cy="7" r="4" fill="#D4AF37"/>
        <circle cx="7" cy="7" r="2" fill="#FDFCF8"/>
        <circle cx="7" cy="7" r="0.8" fill="#D4AF37"/>
        {{-- Petal lines --}}
        <line x1="7" y1="3" x2="7" y2="11" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="3" y1="7" x2="11" y2="7" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="4.2" y1="4.2" x2="9.8" y2="9.8" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="9.8" y1="4.2" x2="4.2" y2="9.8" stroke="#D4AF37" stroke-width="0.5"/>
    </g>

    {{-- ── TOP-RIGHT corner ── --}}
    <g transform="translate(591,4) scale(-1,1)">
        <path d="M0 36 L0 0 L36 0" fill="none" stroke="#D4AF37" stroke-width="3"/>
        <path d="M7 28 L7 7 L28 7" fill="none" stroke="#1F3A5F" stroke-width="1"/>
        <path d="M7 22 Q7 7 22 7" fill="none" stroke="#D4AF37" stroke-width="1.5"/>
        <circle cx="7" cy="7" r="4" fill="#D4AF37"/>
        <circle cx="7" cy="7" r="2" fill="#FDFCF8"/>
        <circle cx="7" cy="7" r="0.8" fill="#D4AF37"/>
        <line x1="7" y1="3" x2="7" y2="11" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="3" y1="7" x2="11" y2="7" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="4.2" y1="4.2" x2="9.8" y2="9.8" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="9.8" y1="4.2" x2="4.2" y2="9.8" stroke="#D4AF37" stroke-width="0.5"/>
    </g>

    {{-- ── BOTTOM-LEFT corner ── --}}
    <g transform="translate(4,838) scale(1,-1)">
        <path d="M0 36 L0 0 L36 0" fill="none" stroke="#D4AF37" stroke-width="3"/>
        <path d="M7 28 L7 7 L28 7" fill="none" stroke="#1F3A5F" stroke-width="1"/>
        <path d="M7 22 Q7 7 22 7" fill="none" stroke="#D4AF37" stroke-width="1.5"/>
        <circle cx="7" cy="7" r="4" fill="#D4AF37"/>
        <circle cx="7" cy="7" r="2" fill="#FDFCF8"/>
        <circle cx="7" cy="7" r="0.8" fill="#D4AF37"/>
        <line x1="7" y1="3" x2="7" y2="11" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="3" y1="7" x2="11" y2="7" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="4.2" y1="4.2" x2="9.8" y2="9.8" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="9.8" y1="4.2" x2="4.2" y2="9.8" stroke="#D4AF37" stroke-width="0.5"/>
    </g>

    {{-- ── BOTTOM-RIGHT corner ── --}}
    <g transform="translate(591,838) scale(-1,-1)">
        <path d="M0 36 L0 0 L36 0" fill="none" stroke="#D4AF37" stroke-width="3"/>
        <path d="M7 28 L7 7 L28 7" fill="none" stroke="#1F3A5F" stroke-width="1"/>
        <path d="M7 22 Q7 7 22 7" fill="none" stroke="#D4AF37" stroke-width="1.5"/>
        <circle cx="7" cy="7" r="4" fill="#D4AF37"/>
        <circle cx="7" cy="7" r="2" fill="#FDFCF8"/>
        <circle cx="7" cy="7" r="0.8" fill="#D4AF37"/>
        <line x1="7" y1="3" x2="7" y2="11" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="3" y1="7" x2="11" y2="7" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="4.2" y1="4.2" x2="9.8" y2="9.8" stroke="#D4AF37" stroke-width="0.5"/>
        <line x1="9.8" y1="4.2" x2="4.2" y2="9.8" stroke="#D4AF37" stroke-width="0.5"/>
    </g>

    {{-- ══════════════════════════════════════════════════
         MID-SIDE DIAMOND ORNAMENTS
         Left side center, Right side center
         ══════════════════════════════════════════════════ --}}

    {{-- Left mid-side --}}
    <g transform="translate(7.5,421)">
        <polygon points="0,-8 5,0 0,8 -5,0" fill="#D4AF37"/>
        <polygon points="0,-4.5 3,0 0,4.5 -3,0" fill="#FDFCF8"/>
        <circle cx="0" cy="0" r="1.2" fill="#D4AF37"/>
    </g>

    {{-- Right mid-side --}}
    <g transform="translate(587.5,421)">
        <polygon points="0,-8 5,0 0,8 -5,0" fill="#D4AF37"/>
        <polygon points="0,-4.5 3,0 0,4.5 -3,0" fill="#FDFCF8"/>
        <circle cx="0" cy="0" r="1.2" fill="#D4AF37"/>
    </g>

    {{-- Top mid-side --}}
    <g transform="translate(297.5,7.5)">
        <polygon points="-8,0 0,-5 8,0 0,5" fill="#D4AF37"/>
        <polygon points="-4.5,0 0,-3 4.5,0 0,3" fill="#FDFCF8"/>
        <circle cx="0" cy="0" r="1.2" fill="#D4AF37"/>
    </g>

    {{-- Bottom mid-side --}}
    <g transform="translate(297.5,834.5)">
        <polygon points="-8,0 0,-5 8,0 0,5" fill="#D4AF37"/>
        <polygon points="-4.5,0 0,-3 4.5,0 0,3" fill="#FDFCF8"/>
        <circle cx="0" cy="0" r="1.2" fill="#D4AF37"/>
    </g>

    {{-- ══════════════════════════════════════════════════
         DECORATIVE DOT SERIES along borders
         Small gold dots between corners and mid-ornaments
         ══════════════════════════════════════════════════ --}}

    {{-- Top border dots (left quarter) --}}
    <circle cx="80"  cy="7.5" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="100" cy="7.5" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="120" cy="7.5" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="140" cy="7.5" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="160" cy="7.5" r="1.2" fill="#D4AF37" opacity="0.6"/>

    {{-- Top border dots (right quarter) --}}
    <circle cx="435" cy="7.5" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="455" cy="7.5" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="475" cy="7.5" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="495" cy="7.5" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="515" cy="7.5" r="1.2" fill="#D4AF37" opacity="0.6"/>

    {{-- Bottom border dots (left quarter) --}}
    <circle cx="80"  cy="834.5" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="100" cy="834.5" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="120" cy="834.5" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="140" cy="834.5" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="160" cy="834.5" r="1.2" fill="#D4AF37" opacity="0.6"/>

    {{-- Bottom border dots (right quarter) --}}
    <circle cx="435" cy="834.5" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="455" cy="834.5" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="475" cy="834.5" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="495" cy="834.5" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="515" cy="834.5" r="1.2" fill="#D4AF37" opacity="0.6"/>

    {{-- Left border dots (top quarter) --}}
    <circle cx="7.5" cy="100" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="7.5" cy="130" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="7.5" cy="160" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="7.5" cy="190" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="7.5" cy="220" r="1.2" fill="#D4AF37" opacity="0.6"/>

    {{-- Left border dots (bottom quarter) --}}
    <circle cx="7.5" cy="622" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="7.5" cy="652" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="7.5" cy="682" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="7.5" cy="712" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="7.5" cy="742" r="1.2" fill="#D4AF37" opacity="0.6"/>

    {{-- Right border dots (top quarter) --}}
    <circle cx="587.5" cy="100" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="587.5" cy="130" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="587.5" cy="160" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="587.5" cy="190" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="587.5" cy="220" r="1.2" fill="#D4AF37" opacity="0.6"/>

    {{-- Right border dots (bottom quarter) --}}
    <circle cx="587.5" cy="622" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="587.5" cy="652" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="587.5" cy="682" r="1.2" fill="#D4AF37" opacity="0.6"/>
    <circle cx="587.5" cy="712" r="0.8" fill="#D4AF37" opacity="0.4"/>
    <circle cx="587.5" cy="742" r="1.2" fill="#D4AF37" opacity="0.6"/>

</svg>
</div>

{{-- Watermark cross (behind content) --}}
<div class="wm">
    <svg width="220pt" height="260pt" viewBox="0 0 220 260" fill="none">
        <rect x="94" y="0" width="32" height="260" fill="#1F3A5F"/>
        <rect x="0" y="88" width="220" height="32" fill="#1F3A5F"/>
    </svg>
</div>
