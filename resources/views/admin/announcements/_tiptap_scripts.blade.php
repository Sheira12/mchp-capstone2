{{--
    TipTap rich-text editor — loaded from jsDelivr CDN (no npm needed).
    $existingContent: optional HTML string to pre-load (for edit form).
--}}
@push('styles')
<style>
/* TipTap toolbar buttons */
.tip-btn {
    display:inline-flex; align-items:center; justify-content:center;
    padding:2px 7px; border-radius:5px; font-size:0.78rem; font-weight:600;
    color:#374151; background:transparent; border:none; cursor:pointer;
    transition:background 0.12s;
}
.tip-btn:hover { background:#e5e7eb; }
.tip-btn.active { background:#dbeafe; color:#1d4ed8; }

/* Editor prose styles */
#tiptap-editor { min-height:160px; cursor:text; }
#tiptap-editor p { margin:0 0 8px; }
#tiptap-editor h1 { font-size:1.4em; font-weight:800; margin:12px 0 4px; }
#tiptap-editor h2 { font-size:1.2em; font-weight:700; margin:10px 0 4px; }
#tiptap-editor ul,#tiptap-editor ol { padding-left:1.4em; margin:6px 0; }
#tiptap-editor blockquote { border-left:3px solid #d1d5db; padding-left:10px; color:#6b7280; margin:6px 0; }
#tiptap-editor strong { font-weight:700; }
#tiptap-editor em { font-style:italic; }
#tiptap-editor u  { text-decoration:underline; }
</style>
@endpush

@push('scripts')
{{-- TipTap core + extensions from jsDelivr --}}
<script src="https://cdn.jsdelivr.net/npm/@tiptap/core@2.4.0/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/starter-kit@2.4.0/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-underline@2.4.0/dist/index.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-placeholder@2.4.0/dist/index.umd.min.js"></script>

<script>
(function () {
    const { Editor } = window['@tiptap/core'];
    const StarterKit   = window['@tiptap/starter-kit']?.StarterKit ?? window['@tiptap/starter-kit'];
    const Underline    = window['@tiptap/extension-underline']?.Underline ?? window['@tiptap/extension-underline'];

    if (!Editor || !StarterKit) {
        // TipTap failed to load (e.g. offline) — show plain textarea fallback
        const hidden = document.getElementById('tiptap-hidden');
        const editor = document.getElementById('tiptap-editor');
        const toolbar = document.getElementById('tiptap-toolbar');
        if (hidden && editor && toolbar) {
            toolbar.style.display = 'none';
            editor.style.display  = 'none';
            hidden.classList.remove('hidden');
            hidden.style.minHeight = '160px';
            hidden.style.width     = '100%';
        }
        return;
    }

    const existingContent = {!! json_encode($existingContent ?? '') !!};
    const hiddenInput     = document.getElementById('tiptap-hidden');
    const editorEl        = document.getElementById('tiptap-editor');

    const editor = new Editor({
        element: editorEl,
        extensions: [
            StarterKit,
            Underline,
        ],
        content:   existingContent || hiddenInput.value || '',
        editorProps: {
            attributes: { style: 'outline:none;min-height:140px;' },
        },
        onUpdate({ editor }) {
            hiddenInput.value = editor.getHTML();
        },
    });

    // Sync on form submit as well
    document.getElementById('ann-form').addEventListener('submit', () => {
        hiddenInput.value = editor.getHTML();
    });

    // Toolbar button handlers
    document.querySelectorAll('.tip-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const cmd = btn.dataset.cmd;
            switch (cmd) {
                case 'toggleBold':         editor.chain().focus().toggleBold().run(); break;
                case 'toggleItalic':       editor.chain().focus().toggleItalic().run(); break;
                case 'toggleUnderline':    editor.chain().focus().toggleUnderline().run(); break;
                case 'toggleHeading1':     editor.chain().focus().toggleHeading({ level: 1 }).run(); break;
                case 'toggleHeading2':     editor.chain().focus().toggleHeading({ level: 2 }).run(); break;
                case 'toggleBulletList':   editor.chain().focus().toggleBulletList().run(); break;
                case 'toggleOrderedList':  editor.chain().focus().toggleOrderedList().run(); break;
                case 'toggleBlockquote':   editor.chain().focus().toggleBlockquote().run(); break;
                case 'clearNodes':         editor.chain().focus().clearNodes().unsetAllMarks().run(); break;
            }
            updateToolbarState();
        });
    });

    function updateToolbarState() {
        document.querySelectorAll('.tip-btn').forEach(btn => {
            const cmd = btn.dataset.cmd;
            let isActive = false;
            switch (cmd) {
                case 'toggleBold':        isActive = editor.isActive('bold'); break;
                case 'toggleItalic':      isActive = editor.isActive('italic'); break;
                case 'toggleUnderline':   isActive = editor.isActive('underline'); break;
                case 'toggleHeading1':    isActive = editor.isActive('heading', { level: 1 }); break;
                case 'toggleHeading2':    isActive = editor.isActive('heading', { level: 2 }); break;
                case 'toggleBulletList':  isActive = editor.isActive('bulletList'); break;
                case 'toggleOrderedList': isActive = editor.isActive('orderedList'); break;
                case 'toggleBlockquote':  isActive = editor.isActive('blockquote'); break;
            }
            btn.classList.toggle('active', isActive);
        });
    }

    editor.on('selectionUpdate', updateToolbarState);
    editor.on('transaction',     updateToolbarState);
})();
</script>
@endpush
