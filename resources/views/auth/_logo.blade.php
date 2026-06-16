{{-- Shared auth page logo header --}}
<div style="text-align:center;margin-bottom:2rem;">
    {{-- Logo circle with overflow:hidden to enforce round crop --}}
    <div style="
        width:88px; height:88px;
        border-radius:50%;
        overflow:hidden;
        margin:0 auto 1rem;
        border:4px solid rgba(255,255,255,0.85);
        box-shadow:0 8px 32px rgba(0,0,0,0.35);
        background:rgba(255,255,255,0.18);
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0;
    " id="logo-wrap">
        <img src="{{ asset('images/parish-logo.png') }}"
             alt="MHC Parish Logo"
             style="
                 width:88px;
                 height:88px;
                 object-fit:cover;
                 display:block;
             "
             onerror="
                 this.style.display='none';
                 document.getElementById('logo-wrap').innerHTML='<span style=\'font-size:2.25rem;\'>⛪</span>';
             ">
    </div>
    <h1 style="color:#fff;font-size:1.25rem;font-weight:800;margin:0 0 4px;letter-spacing:-0.01em;">
        Mary Help of Christians Parish
    </h1>
    @if(isset($subtitle))
        <p style="color:rgba(191,219,254,0.9);font-size:0.875rem;margin:0;">{{ $subtitle }}</p>
    @else
        <p style="color:rgba(191,219,254,0.9);font-size:0.875rem;margin:0;">Southville 1, Niugan, Cabuyao, Laguna</p>
    @endif
</div>
