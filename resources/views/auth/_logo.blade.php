{{-- Shared auth page logo header --}}
<div class="text-center mb-8">
    <div class="mx-auto mb-4" style="width:84px;height:84px;">
        <img src="{{ asset('images/parish-logo.png') }}"
             id="auth-logo"
             alt="Mary Help of Christians Parish"
             style="width:84px;height:84px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,0.9);box-shadow:0 8px 32px rgba(0,0,0,0.35);display:block;"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <div style="display:none;width:84px;height:84px;border-radius:50%;background:rgba(255,255,255,0.18);border:4px solid rgba(255,255,255,0.6);box-shadow:0 8px 32px rgba(0,0,0,0.35);align-items:center;justify-content:center;font-size:2rem;">
            ⛪
        </div>
    </div>
    <h1 class="text-white text-xl font-bold tracking-tight">Mary Help of Christians Parish</h1>
    @if(isset($subtitle))
        <p class="text-blue-200 text-sm mt-0.5">{{ $subtitle }}</p>
    @else
        <p class="text-blue-200 text-sm mt-0.5">Southville 1, Niugan, Cabuyao, Laguna</p>
    @endif
</div>
