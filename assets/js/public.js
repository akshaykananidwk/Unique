/* Krishna Print — public site JS: live price, proof approve/change, voice input */
(function () {
  'use strict';

  // Public site is order-only — no prices are shown to customers.

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
