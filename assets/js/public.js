/* Krishna Print — public site JS: live price, proof approve/change, voice input */
(function () {
  'use strict';

  // ---------------------------------------------------------------- product live price
  const priceBox = document.getElementById('livePrice');
  const productForm = document.getElementById('productOrderForm');
  if (productForm && priceBox && window.KP_PRODUCT) {
    const p = window.KP_PRODUCT; // {base_price, pricing_type, slabs:[{min,max,rate}], options:{key:{values:{id:{delta,mode}}}}, tax_percent, min_qty}
    const money = n => '₹' + (Math.round(n * 100) / 100).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    function calc() {
      const qty = Math.max(p.min_qty, parseFloat(productForm.querySelector('[name=qty]').value) || p.min_qty);
      let rate = parseFloat(p.base_price);
      if (p.pricing_type === 'slab') {
        (p.slabs || []).forEach(s => { if (qty >= s.min && (!s.max || qty <= s.max)) rate = parseFloat(s.rate); });
      }
      let add = 0; const mult = [];
      productForm.querySelectorAll('[data-opt-key]').forEach(el => {
        const key = el.getAttribute('data-opt-key');
        let ids = [];
        if (el.type === 'radio' || el.type === 'checkbox') { if (el.checked) ids = [el.value]; }
        else if (el.tagName === 'SELECT' && el.value) ids = [el.value];
        ids.forEach(id => {
          const v = ((p.options[key] || {}).values || {})[id];
          if (!v) return;
          if (v.mode === 'add') add += parseFloat(v.delta);
          else if (v.mode === 'multiply') mult.push(parseFloat(v.delta));
          else if (v.mode === 'replace') rate = parseFloat(v.delta);
        });
      });
      rate += add;
      mult.forEach(m => rate *= m);
      let billed = qty;
      if (p.pricing_type === 'area') {
        const w = parseFloat((productForm.querySelector('[name=opt_width_ft]') || {}).value) || 0;
        const h = parseFloat((productForm.querySelector('[name=opt_height_ft]') || {}).value) || 0;
        if (w > 0 && h > 0) billed = w * h * qty;
      }
      const amount = p.pricing_type === 'fixed' ? rate : rate * billed;
      const tax = amount * (parseFloat(p.tax_percent) || 0) / 100;
      priceBox.innerHTML = '<strong>' + money(amount + tax) + '</strong>' +
        (tax > 0 ? ' <small class="text-muted">(incl. ' + p.tax_percent + '% tax)</small>' : '');
    }
    productForm.addEventListener('input', calc);
    productForm.addEventListener('change', calc);
    calc();
  }

  // ---------------------------------------------------------------- OTP box autofocus
  const otp = document.getElementById('otpInput');
  if (otp) otp.focus();

  // ---------------------------------------------------------------- proof page
  const changeBtn = document.getElementById('btnRequestChange');
  const changeForm = document.getElementById('changeForm');
  if (changeBtn && changeForm) {
    changeBtn.addEventListener('click', () => {
      changeForm.classList.toggle('d-none');
      if (!changeForm.classList.contains('d-none')) {
        changeForm.querySelector('textarea').focus();
        changeForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  // Fullscreen image zoom
  document.querySelectorAll('.proof-image-wrap img').forEach(img => {
    img.addEventListener('click', () => {
      if (img.requestFullscreen) img.requestFullscreen();
      else if (img.webkitRequestFullscreen) img.webkitRequestFullscreen();
    });
  });

  // ---------------------------------------------------------------- voice input
  const micBtn = document.getElementById('micBtn');
  if (micBtn) {
    const textarea = document.getElementById('feedbackText');
    const langSelect = document.getElementById('speechLang');
    const voiceStatus = document.getElementById('voiceStatus');
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    if (SpeechRecognition) {
      // Live transcription path
      let recognition = null;
      let listening = false;
      micBtn.addEventListener('click', () => {
        if (listening) { recognition && recognition.stop(); return; }
        recognition = new SpeechRecognition();
        recognition.lang = langSelect.value || 'gu-IN';
        recognition.continuous = true;
        recognition.interimResults = true;
        let finalSoFar = textarea.value ? textarea.value + ' ' : '';
        recognition.onresult = e => {
          let interim = '';
          for (let i = e.resultIndex; i < e.results.length; i++) {
            if (e.results[i].isFinal) finalSoFar += e.results[i][0].transcript + ' ';
            else interim += e.results[i][0].transcript;
          }
          textarea.value = (finalSoFar + interim).trim();
        };
        recognition.onstart = () => { listening = true; micBtn.classList.add('recording'); voiceStatus.textContent = 'Listening… tap again to stop'; };
        recognition.onend = () => { listening = false; micBtn.classList.remove('recording'); voiceStatus.textContent = ''; };
        recognition.onerror = ev => {
          listening = false; micBtn.classList.remove('recording');
          voiceStatus.textContent = ev.error === 'not-allowed' ? 'Microphone permission denied' : 'Speech error — you can type instead';
        };
        recognition.start();
      });
    } else if (navigator.mediaDevices && window.MediaRecorder) {
      // Fallback: record an audio note (iPhone Safari path)
      let recorder = null;
      let chunks = [];
      voiceStatus.textContent = 'Voice typing not supported — tap the mic to record a voice note instead.';
      micBtn.addEventListener('click', async () => {
        if (recorder && recorder.state === 'recording') { recorder.stop(); return; }
        try {
          const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
          const mime = MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm'
            : (MediaRecorder.isTypeSupported('audio/mp4') ? 'audio/mp4' : '');
          recorder = new MediaRecorder(stream, mime ? { mimeType: mime } : undefined);
          chunks = [];
          recorder.ondataavailable = e => chunks.push(e.data);
          recorder.onstop = () => {
            stream.getTracks().forEach(t => t.stop());
            const blob = new Blob(chunks, { type: recorder.mimeType || 'audio/webm' });
            const ext = (recorder.mimeType || '').includes('mp4') ? 'm4a' : 'webm';
            const file = new File([blob], 'voice-note.' + ext, { type: blob.type });
            const dt = new DataTransfer();
            dt.items.add(file);
            document.getElementById('voiceFile').files = dt.files;
            micBtn.classList.remove('recording');
            voiceStatus.innerHTML = '✅ Voice note recorded (' + Math.round(blob.size / 1024) + ' KB). ' +
              '<audio controls src="' + URL.createObjectURL(blob) + '" style="height:30px;vertical-align:middle"></audio>';
          };
          recorder.start();
          micBtn.classList.add('recording');
          voiceStatus.textContent = 'Recording… tap again to stop';
        } catch (err) {
          voiceStatus.textContent = 'Microphone not available — please type your changes.';
        }
      });
    } else {
      micBtn.style.display = 'none';
      voiceStatus.textContent = 'Voice input not supported on this browser — please type your changes.';
    }
  }
})();
