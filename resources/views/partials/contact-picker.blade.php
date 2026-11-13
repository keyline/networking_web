{{-- "Pick from contacts" (Contact Picker API). Buttons with [data-contact-pick] stay hidden unless the
     browser supports it (Chrome / Samsung Internet / Edge on Android, over HTTPS). The picked contact
     fills name, phone and email inside the button's closest <form>. Only that one contact is shared. --}}
<style>
.contact-pick{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;min-height:46px;margin:0 0 12px;padding:0 16px;border:1.5px dashed #b9c7dd;border-radius:14px;background:#f6f9ff;color:#2a5fd0;font:inherit;font-size:15px;font-weight:700;cursor:pointer}
.contact-pick:hover{border-color:#397ef6;background:#eef4ff}
.contact-pick svg{flex:0 0 20px;width:20px;height:20px;fill:currentColor}
.contact-pick[hidden]{display:none!important}
.contact-pick-note{margin:-6px 0 12px;color:#138a50;font-size:13px;font-weight:600}
</style>
<script>
(() => {
  const supported = 'contacts' in navigator && 'ContactsManager' in window;
  // 10-digit Indian mobile from any format: +91 98765 43210, 098765-43210, 91987654321 0…
  const tenDigits = raw => { let d = String(raw || '').replace(/\D+/g, ''); if (d.length > 10) d = d.slice(-10); return d; };
  const pickNumber = list => { const nums = (list || []).map(tenDigits).filter(Boolean); return nums.find(n => /^[6-9]\d{9}$/.test(n)) || nums[0] || ''; };
  const fill = (form, name, value) => { const el = form.querySelector(`[name="${name}"]`); if (el && value) { el.value = value; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); } };
  document.querySelectorAll('[data-contact-pick]').forEach(btn => {
    if (!supported) return; // stays hidden on iPhone / desktop
    btn.hidden = false;
    btn.addEventListener('click', async () => {
      const form = btn.closest('form'); if (!form) return;
      try {
        const props = await navigator.contacts.getProperties();
        const want = ['name', 'tel', 'email'].filter(p => props.includes(p));
        const [contact] = await navigator.contacts.select(want, { multiple: false });
        if (!contact) return;
        fill(form, 'name', (contact.name || [])[0]);
        fill(form, 'phone', pickNumber(contact.tel));
        fill(form, 'email', (contact.email || [])[0]);
        let note = form.querySelector('.contact-pick-note');
        if (!note) { note = document.createElement('p'); note.className = 'contact-pick-note'; btn.after(note); }
        note.textContent = '✓ Filled from your contacts. Please check before sending.';
      } catch (e) { /* user cancelled or permission denied: leave the form as it is */ }
    });
  });
})();
</script>
