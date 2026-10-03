@php($legalPages = \App\Services\LegalPagesService::pages())
<style>
  .settings-page #tab13 form::before { content: 'Privacy Policy & Terms and Conditions'; }
  .legal-editor { border: 1px solid #dce3ed; border-radius: 8px; background: #fff; overflow: hidden; }
  .legal-editor:focus-within { border-color: var(--sp-primary, #397ef6); box-shadow: 0 0 0 3px rgba(57, 126, 246, .1); }
  .legal-toolbar { display: flex; flex-wrap: wrap; gap: 2px; padding: 6px; border-bottom: 1px solid #edf1f6; background: #f8fafc; }
  .legal-toolbar button { min-width: 32px; height: 30px; padding: 0 8px; color: #44536a; border: 0; border-radius: 5px; background: transparent; font-size: 12px; font-weight: 700; }
  .legal-toolbar button:hover { color: #2e69ce; background: #eaf2ff; }
  .legal-toolbar button.active { color: #fff; background: var(--sp-primary, #397ef6); }
  .legal-toolbar .sep { width: 1px; margin: 4px 4px; background: #dce3ed; }
  .legal-surface, .legal-source { display: block; width: 100%; min-height: 260px; max-height: 520px; overflow-y: auto; padding: 14px 16px; border: 0; color: #33435c; font-size: 14px; line-height: 1.65; outline: none; }
  .legal-surface h2 { font-size: 20px; margin: 1em 0 .5em; } .legal-surface h3 { font-size: 17px; margin: 1em 0 .5em; }
  .legal-surface p { margin: 0 0 .8em; } .legal-surface a { color: #2e69ce; }
  .legal-surface:empty::before { content: attr(data-placeholder); color: #a0aabb; }
  .legal-surface[hidden], .legal-source[hidden] { display: none; }
  .legal-source { font-family: SFMono-Regular, Menlo, monospace; font-size: 12px; resize: vertical; }
  .legal-meta { display: flex; justify-content: space-between; gap: 10px; margin-top: 6px; }
</style>

<div class="tab-pane fade pt-3" id="tab13">
  <form method="POST" action="{{ url('admin/legal-pages-settings') }}" class="legal-pages-form">
    @csrf
    <p class="text-muted mb-4">This content appears on the website (linked from the footer) and in the mobile app menu.</p>

    @foreach(\App\Services\LegalPagesService::PAGES as $key => [$slug, $title])
      @php($page = $legalPages[$key])
      <div class="mb-4">
        <label class="control-label mb-2 d-block">{{ $title }}</label>
        <div class="legal-editor" data-legal-editor>
          <div class="legal-toolbar" role="toolbar" aria-label="{{ $title }} formatting">
            <button type="button" data-cmd="bold" title="Bold"><b>B</b></button>
            <button type="button" data-cmd="italic" title="Italic"><i>I</i></button>
            <button type="button" data-cmd="underline" title="Underline"><u>U</u></button>
            <span class="sep"></span>
            <button type="button" data-block="h2" title="Heading">H2</button>
            <button type="button" data-block="h3" title="Subheading">H3</button>
            <button type="button" data-block="p" title="Paragraph">¶</button>
            <span class="sep"></span>
            <button type="button" data-cmd="insertUnorderedList" title="Bulleted list"><i class="fa fa-list-ul"></i></button>
            <button type="button" data-cmd="insertOrderedList" title="Numbered list"><i class="fa fa-list-ol"></i></button>
            <span class="sep"></span>
            <button type="button" data-link title="Insert link"><i class="fa fa-link"></i></button>
            <button type="button" data-cmd="unlink" title="Remove link"><i class="fa fa-unlink"></i></button>
            <button type="button" data-cmd="removeFormat" title="Clear formatting"><i class="fa fa-eraser"></i></button>
            <span class="sep"></span>
            <button type="button" data-cmd="undo" title="Undo"><i class="fa fa-undo"></i></button>
            <button type="button" data-cmd="redo" title="Redo"><i class="fa fa-redo"></i></button>
            <span class="sep"></span>
            <button type="button" data-source title="Edit HTML">&lt;/&gt;</button>
          </div>
          <div class="legal-surface" contenteditable="true" data-placeholder="Write the {{ $title }} here…">{!! \App\Services\LegalPagesService::sanitize($page->page_content) !!}</div>
          <textarea name="{{ $key }}_content" class="legal-source" hidden>{{ \App\Services\LegalPagesService::sanitize($page->page_content) }}</textarea>
        </div>
        <div class="legal-meta">
          <small class="text-muted">
            Last updated {{ $page->updated_at ? \Carbon\Carbon::parse($page->updated_at)->format('d M Y, h:i a') : '—' }}
          </small>
          <small><a href="{{ url('page/' . $slug) }}" target="_blank" rel="noopener">View page <i class="fa fa-external-link-alt"></i></a></small>
        </div>
      </div>
    @endforeach

    <div class="text-center">
      <button type="submit" class="btn btn-primary">Save legal pages</button>
    </div>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-legal-editor]').forEach(function (editor) {
      const surface = editor.querySelector('.legal-surface');
      const source = editor.querySelector('.legal-source');
      const sourceBtn = editor.querySelector('[data-source]');
      const sync = function () { if (source.hidden) source.value = surface.innerHTML.trim(); };
      const exec = function (cmd, value) { surface.focus(); document.execCommand(cmd, false, value); sync(); };

      document.execCommand('defaultParagraphSeparator', false, 'p');
      surface.addEventListener('input', sync);
      // Paste as plain text so formatting from Word or other sites doesn't leak in
      surface.addEventListener('paste', function (e) {
        e.preventDefault();
        exec('insertText', (e.clipboardData || window.clipboardData).getData('text/plain'));
      });

      editor.querySelectorAll('[data-cmd]').forEach(function (btn) {
        btn.addEventListener('mousedown', e => e.preventDefault());
        btn.addEventListener('click', () => exec(btn.dataset.cmd));
      });
      editor.querySelectorAll('[data-block]').forEach(function (btn) {
        btn.addEventListener('mousedown', e => e.preventDefault());
        btn.addEventListener('click', () => exec('formatBlock', '<' + btn.dataset.block + '>'));
      });
      editor.querySelector('[data-link]').addEventListener('mousedown', e => e.preventDefault());
      editor.querySelector('[data-link]').addEventListener('click', function () {
        const url = prompt('Link address (https://…, mailto:… or /page)', 'https://');
        if (url && url !== 'https://') exec('createLink', url.trim());
      });

      sourceBtn.addEventListener('click', function () {
        const showSource = source.hidden;
        if (showSource) {
          source.value = surface.innerHTML.trim();
        } else {
          surface.innerHTML = source.value;
        }
        source.hidden = !showSource;
        surface.hidden = showSource;
        sourceBtn.classList.toggle('active', showSource);
        editor.querySelectorAll('.legal-toolbar button:not([data-source])').forEach(b => b.disabled = showSource);
      });
    });

    document.querySelectorAll('.legal-pages-form').forEach(function (form) {
      form.addEventListener('submit', function () {
        form.querySelectorAll('[data-legal-editor]').forEach(function (editor) {
          const source = editor.querySelector('.legal-source');
          if (source.hidden) source.value = editor.querySelector('.legal-surface').innerHTML.trim();
        });
      });
    });
  });
</script>
