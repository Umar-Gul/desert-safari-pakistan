@props(['name', 'value' => ''])

@once
    @push('head')
        <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    @endpush
@endonce

<div x-data="{
        init() {
            const quill = new Quill(this.$refs.editor, {
                theme: 'snow',
                modules: { toolbar: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']] },
            });
            quill.root.innerHTML = this.$refs.input.value;
            quill.on('text-change', () => {
                const html = quill.root.innerHTML;
                this.$refs.input.value = html === '<p><br></p>' ? '' : html;
            });
        },
    }">
    <input type="hidden" name="{{ $name }}" x-ref="input" value="{{ $value }}">
    <div x-ref="editor" class="min-h-[14rem] bg-white"></div>
</div>