(function(){
  function ready(fn){
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',fn); else fn();
  }
  ready(function(){
    // Copy button functionality
    document.querySelectorAll('.wbdn-voice-copy').forEach(function(btn){
      btn.addEventListener('click', function(){
        var code = btn.closest('.wbdn-voice-code');
        if(!code) return;
        var text = code.querySelector('code');
        if(!text) return;
        var content = text.innerText;
        navigator.clipboard.writeText(content).then(function(){
          btn.classList.add('is-copied');
          btn.textContent = 'Copied';
          setTimeout(function(){btn.classList.remove('is-copied');btn.textContent='Copy';}, 1200);
        });
      });
    });

    // Preset change handling
    const presetSelect = document.getElementById('wbdn-voice-preset-select');
    if (presetSelect) {
      presetSelect.addEventListener('change', function() {
        const preset = this.value;
        const previewDiv = document.getElementById('wbdn-voice-preset-preview');
        
        // Update preview
        if (previewDiv) {
          const presets = {
            'modern': {
              name: 'Modern',
              desc: 'Clean bars with subtle shadows',
              colors: ['#3b82f6', '#f3f4f6', '#111827'],
              height: 64,
              radius: 2,
              waveStyle: 'bars'
            },
            'minimal': {
              name: 'Minimal',
              desc: 'Thin lines with elegant spacing',
              colors: ['#6b7280', '#ffffff', '#1f2937'],
              height: 48,
              radius: 0,
              waveStyle: 'line'
            },
            'neon': {
              name: 'Neon',
              desc: 'Glowing bars with dark theme',
              colors: ['#10b981', '#0f172a', '#fbbf24'],
              height: 72,
              radius: 4,
              waveStyle: 'bars'
            },
            'wave': {
              name: 'Wave',
              desc: 'Continuous filled waveform without separation',
              colors: ['#3b82f6', '#f3f4f6', '#111827'],
              height: 64,
              radius: 0,
              waveStyle: 'continuous'
            },
            'wave_top': {
              name: 'Wave Top',
              desc: 'Only the upper half of the waveform (filled to center)',
              colors: ['#3b82f6', '#f8fafc', '#111827'],
              height: 64,
              radius: 0,
              waveStyle: 'continuous'
            }
          };
          
          const current = presets[preset];
          previewDiv.innerHTML = `
            <div class="wbdn-voice-preview-card" style="border: 2px solid #e5e7eb; border-radius: 8px; padding: 16px; background: #fff;">
              <h4 style="margin: 0 0 8px 0; color: #111827;">${current.name}</h4>
              <p style="margin: 0 0 12px 0; color: #6b7280; font-size: 14px;">${current.desc}</p>
              <div style="display: flex; gap: 8px; align-items: center;">
                <span style="font-size: 12px; color: #6b7280;">Colors:</span>
                ${current.colors.map(color => `<div style=\"width: 20px; height: 20px; background: ${color}; border-radius: 4px; border: 1px solid #d1d5db;\"></div>`).join('')}
              </div>
              <div style="margin-top: 8px; font-size: 12px; color: #6b7280;">
                Height: ${current.height}px | Radius: ${current.radius}px | Wave: ${current.waveStyle}
              </div>
            </div>
          `;

          // Auto-fill preset values
          const heightField = document.querySelector('input[name="wbdn_voice_player_options[height]"]');
          const radiusField = document.querySelector('input[name="wbdn_voice_player_options[radius]"]');
          const accentField = document.querySelector('input[name="wbdn_voice_player_options[accent]"]');
          const bgField = document.querySelector('input[name="wbdn_voice_player_options[bg]"]');
          const progressField = document.querySelector('input[name="wbdn_voice_player_options[progress]"]');
          const waveStyleSelect = document.getElementById('wbdn-voice-wave-style-select');
          
          if (heightField) heightField.value = current.height;
          if (radiusField) radiusField.value = current.radius;
          if (accentField) accentField.value = current.colors[0];
          if (bgField) bgField.value = current.colors[1];
          if (progressField) progressField.value = current.colors[2];
          if (waveStyleSelect) waveStyleSelect.value = current.waveStyle;
          
          // Trigger color picker updates if they exist
          if (window.jQuery && window.jQuery.fn.wpColorPicker) {
            if (accentField) window.jQuery(accentField).wpColorPicker('color', current.colors[0]);
            if (bgField) window.jQuery(bgField).wpColorPicker('color', current.colors[1]);
            if (progressField) window.jQuery(progressField).wpColorPicker('color', current.colors[2]);
          }
        }
      });
    }
  });
})();

