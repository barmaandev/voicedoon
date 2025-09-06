(() => {
  const initializedAttr = 'data-wbdn-voice-initialized';

  function formatTime(seconds) {
    if (!isFinite(seconds)) return '--:--';
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
  }

  function computePeaks(audioBuffer, desiredBars = 120) {
    const channelData = audioBuffer.getChannelData(0);
    const totalSamples = channelData.length;
    const samplesPerBar = Math.max(1, Math.floor(totalSamples / desiredBars));
    const peaks = [];
    for (let i = 0; i < desiredBars; i++) {
      const start = i * samplesPerBar;
      const end = Math.min(start + samplesPerBar, totalSamples);
      let max = 0;
      for (let j = start; j < end; j++) {
        const v = Math.abs(channelData[j]);
        if (v > max) max = v;
      }
      peaks.push(max);
    }
    // Normalize to [0,1]
    const maxPeak = Math.max(0.001, ...peaks);
    return peaks.map(p => p / maxPeak);
  }

  function roundRect(ctx, x, y, w, h, r) {
    const radius = Math.max(0, Math.min(r, Math.min(w, h) / 2));
    if (radius === 0) {
      ctx.fillRect(x, y, w, h);
      return;
    }
    const r2 = radius;
    ctx.beginPath();
    ctx.moveTo(x + r2, y);
    ctx.lineTo(x + w - r2, y);
    ctx.quadraticCurveTo(x + w, y, x + w, y + r2);
    ctx.lineTo(x + w, y + h - r2);
    ctx.quadraticCurveTo(x + w, y + h, x + w - r2, y + h);
    ctx.lineTo(x + r2, y + h);
    ctx.quadraticCurveTo(x, y + h, x, y + h - r2);
    ctx.lineTo(x, y + r2);
    ctx.quadraticCurveTo(x, y, x + r2, y);
    ctx.closePath();
    ctx.fill();
  }

  function initPlayer(container) {
    if (container.getAttribute(initializedAttr) === '1') return;
    container.setAttribute(initializedAttr, '1');

    const src = container.getAttribute('data-src');
    const accent = container.getAttribute('data-accent') || '#3b82f6';
    const bg = container.getAttribute('data-bg') || '#f3f4f6';
    const progressColor = container.getAttribute('data-progress') || '#111827';
    const height = parseInt(container.getAttribute('data-height') || '64', 10);
    const preload = container.getAttribute('data-preload') || 'none';
    const loadOn = (container.getAttribute('data-load-on') || 'view').toLowerCase();
    const radius = Math.max(0, parseInt(container.getAttribute('data-radius') || '0', 10));
    const preset = container.getAttribute('data-preset') || 'modern';

    const playButton = container.querySelector('.wbdn-voice-button');
    const iconSpan = container.querySelector('.wbdn-voice-icon');
    const canvas = container.querySelector('canvas.wbdn-voice-wave');
    const currentEl = container.querySelector('.wbdn-voice-current');
    const durationEl = container.querySelector('.wbdn-voice-duration');

    let peaks = null;
    let duration = 0;
    let rafId = null;
    let destroyed = false;

    const dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
    const ctx = canvas.getContext('2d');

    const audio = new Audio();
    audio.preload = preload;
    audio.crossOrigin = 'anonymous';

    function resizeCanvas() {
      const parentWidth = container.querySelector('.wbdn-voice-wave-wrap').clientWidth;
      const cssWidth = Math.max(200, parentWidth);
      canvas.style.width = cssWidth + 'px';
      canvas.style.height = height + 'px';
      canvas.width = Math.floor(cssWidth * dpr);
      canvas.height = Math.floor(height * dpr);
      draw();
    }

    function drawWaveformBase() {
      if (!peaks) return;
      const width = canvas.width;
      const heightPx = canvas.height;
      const barCount = peaks.length;
      const centerY = heightPx / 2;

      ctx.clearRect(0, 0, width, heightPx);
      
      if (preset === 'minimal') {
        // Minimal: thin lines
        const gap = Math.max(1 * dpr, Math.floor(width / (barCount * 40)));
        const lineWidth = Math.max(1 * dpr, Math.floor((width - (barCount - 1) * gap) / barCount));
        
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, heightPx);
        
        ctx.fillStyle = accent;
        let x = 0;
        for (let i = 0; i < barCount; i++) {
          const h = Math.max(1 * dpr, peaks[i] * heightPx * 0.8);
          const y = centerY - h / 2;
          ctx.fillRect(x, y, lineWidth, h);
          x += lineWidth + gap;
        }
      } else if (preset === 'neon') {
        // Neon: glowing bars with shadows
        const gap = Math.max(2 * dpr, Math.floor(width / (barCount * 25)));
        const barWidth = Math.max(3 * dpr, Math.floor((width - (barCount - 1) * gap) / barCount));
        
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, heightPx);
        
        // Draw shadow bars first
        ctx.fillStyle = 'rgba(16, 185, 129, 0.2)';
        let x = 0;
        for (let i = 0; i < barCount; i++) {
          const h = Math.max(4 * dpr, peaks[i] * heightPx);
          const y = centerY - h / 2;
          roundRect(ctx, x + 2, y + 2, barWidth, h, radius * dpr);
          x += barWidth + gap;
        }
        
        // Draw main bars
        ctx.fillStyle = accent;
        x = 0;
        for (let i = 0; i < barCount; i++) {
          const h = Math.max(4 * dpr, peaks[i] * heightPx);
          const y = centerY - h / 2;
          roundRect(ctx, x, y, barWidth, h, radius * dpr);
          x += barWidth + gap;
        }
      } else if (preset === 'wave') {
        // Wave: continuous filled waveform without separation
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, heightPx);

        const stepX = width / (barCount - 1);
        const amplitude = heightPx * 0.9 / 2; // 90% of height

        // Build top path
        ctx.fillStyle = accent;
        ctx.beginPath();
        ctx.moveTo(0, centerY);
        for (let i = 0; i < barCount; i++) {
          const x = i * stepX;
          const yTop = centerY - Math.max(1 * dpr, peaks[i] * amplitude);
          ctx.lineTo(x, yTop);
        }
        // Build bottom path back
        for (let i = barCount - 1; i >= 0; i--) {
          const x = i * stepX;
          const yBottom = centerY + Math.max(1 * dpr, peaks[i] * amplitude);
          ctx.lineTo(x, yBottom);
        }
        ctx.closePath();
        ctx.fill();
      } else if (preset === 'wave_top') {
        // Wave Top: only the upper half of the waveform filled to center line
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, heightPx);

        const stepX = width / (barCount - 1);
        const amplitude = heightPx * 0.9 / 2;

        ctx.fillStyle = accent;
        ctx.beginPath();
        ctx.moveTo(0, centerY);
        for (let i = 0; i < barCount; i++) {
          const x = i * stepX;
          const yTop = centerY - Math.max(1 * dpr, peaks[i] * amplitude);
          ctx.lineTo(x, yTop);
        }
        ctx.lineTo(width, centerY);
        ctx.lineTo(0, centerY);
        ctx.closePath();
        ctx.fill();
      } else {
        // Modern: default bars
        const gap = Math.max(1 * dpr, Math.floor(width / (barCount * 30)));
        const barWidth = Math.max(2 * dpr, Math.floor((width - (barCount - 1) * gap) / barCount));
        
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, width, heightPx);
        
        ctx.fillStyle = accent;
        let x = 0;
        for (let i = 0; i < barCount; i++) {
          const h = Math.max(2 * dpr, peaks[i] * heightPx);
          const y = centerY - h / 2;
          roundRect(ctx, x, y, barWidth, h, radius * dpr);
          x += barWidth + gap;
        }
      }
    }

    function drawProgressOverlay() {
      if (!peaks) return;
      const width = canvas.width;
      const heightPx = canvas.height;
      const barCount = peaks.length;
      const centerY = heightPx / 2;

      const playedRatio = duration > 0 ? Math.min(1, audio.currentTime / duration) : 0;
      const overlayWidth = Math.floor(width * playedRatio);
      if (overlayWidth <= 0) return;

      ctx.fillStyle = progressColor;
      
      if (preset === 'minimal') {
        // Minimal: thin lines
        const gap = Math.max(1 * dpr, Math.floor(width / (barCount * 40)));
        const lineWidth = Math.max(1 * dpr, Math.floor((width - (barCount - 1) * gap) / barCount));
        
        let x = 0;
        for (let i = 0; i < barCount; i++) {
          const h = Math.max(1 * dpr, peaks[i] * heightPx * 0.8);
          const y = centerY - h / 2;
          const remaining = overlayWidth - x;
          if (remaining <= 0) break;
          const w = Math.min(lineWidth, remaining);
          if (w > 0) {
            ctx.fillRect(x, y, w, h);
          }
          x += lineWidth + gap;
        }
      } else if (preset === 'neon') {
        // Neon: glowing bars
        const gap = Math.max(2 * dpr, Math.floor(width / (barCount * 25)));
        const barWidth = Math.max(3 * dpr, Math.floor((width - (barCount - 1) * gap) / barCount));
        
        let x = 0;
        for (let i = 0; i < barCount; i++) {
          const h = Math.max(4 * dpr, peaks[i] * heightPx);
          const y = centerY - h / 2;
          const remaining = overlayWidth - x;
          if (remaining <= 0) break;
          const w = Math.min(barWidth, remaining);
          if (w > 0) {
            roundRect(ctx, x, y, w, h, radius * dpr);
          }
          x += barWidth + gap;
        }
      } else if (preset === 'wave') {
        // Wave: draw progress by clipping to played width and re-filling the same shape
        const stepX = width / (barCount - 1);
        const amplitude = heightPx * 0.9 / 2;

        ctx.save();
        ctx.beginPath();
        ctx.rect(0, 0, overlayWidth, heightPx);
        ctx.clip();

        ctx.fillStyle = progressColor;
        ctx.beginPath();
        ctx.moveTo(0, centerY);
        for (let i = 0; i < barCount; i++) {
          const x = i * stepX;
          const yTop = centerY - Math.max(1 * dpr, peaks[i] * amplitude);
          ctx.lineTo(x, yTop);
        }
        for (let i = barCount - 1; i >= 0; i--) {
          const x = i * stepX;
          const yBottom = centerY + Math.max(1 * dpr, peaks[i] * amplitude);
          ctx.lineTo(x, yBottom);
        }
        ctx.closePath();
        ctx.fill();
        ctx.restore();
      } else if (preset === 'wave_top') {
        // Wave Top: progress overlay for upper half waveform
        const stepX = width / (barCount - 1);
        const amplitude = heightPx * 0.9 / 2;

        ctx.save();
        ctx.beginPath();
        ctx.rect(0, 0, overlayWidth, heightPx);
        ctx.clip();

        ctx.fillStyle = progressColor;
        ctx.beginPath();
        ctx.moveTo(0, centerY);
        for (let i = 0; i < barCount; i++) {
          const x = i * stepX;
          const yTop = centerY - Math.max(1 * dpr, peaks[i] * amplitude);
          ctx.lineTo(x, yTop);
        }
        ctx.lineTo(width, centerY);
        ctx.lineTo(0, centerY);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
      } else {
        // Modern: default bars
        const gap = Math.max(1 * dpr, Math.floor(width / (barCount * 30)));
        const barWidth = Math.max(2 * dpr, Math.floor((width - (barCount - 1) * gap) / barCount));
        
        let x = 0;
        for (let i = 0; i < barCount; i++) {
          const h = Math.max(2 * dpr, peaks[i] * heightPx);
          const y = centerY - h / 2;
          const remaining = overlayWidth - x;
          if (remaining <= 0) break;
          const w = Math.min(barWidth, remaining);
          if (w > 0) {
            roundRect(ctx, x, y, w, h, radius * dpr);
          }
          x += barWidth + gap;
        }
      }
    }

    function draw() {
      if (!peaks) return;
      drawWaveformBase();
      drawProgressOverlay();
    }

    function updateTimeUi() {
      currentEl.textContent = formatTime(audio.currentTime);
      durationEl.textContent = formatTime(duration);
    }

    function tick() {
      if (destroyed) return;
      draw();
      updateTimeUi();
      if (!audio.paused) {
        rafId = requestAnimationFrame(tick);
      }
    }

    async function buildPeaksAndMaybeSetDuration() {
      if (peaks) return;
      try {
        const res = await fetch(src, { credentials: 'omit' });
        const buf = await res.arrayBuffer();
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const audioBuffer = await audioCtx.decodeAudioData(buf);
        peaks = computePeaks(audioBuffer, 150);
        if (!isFinite(duration) || duration === 0) {
          duration = audioBuffer.duration || 0;
        }
        if (!audio.src) {
          audio.src = src;
        }
        resizeCanvas();
        draw();
        updateTimeUi();
      } catch (e) {
        const width = (canvas.width = canvas.clientWidth);
        const heightPx = (canvas.height = (parseInt(height, 10) || 64) * dpr);
        peaks = new Array(120).fill(0.2);
        if (!audio.src) {
          audio.src = src;
        }
        draw();
      }
    }

    // Initialize based on loadOn behavior
    if (loadOn === 'view') {
      audio.src = src;
      buildPeaksAndMaybeSetDuration();
    } else {
      // click: wait until interaction, but size the canvas early
      resizeCanvas();
    }

    playButton.addEventListener('click', () => {
      if (!audio.src) {
        audio.src = src;
        buildPeaksAndMaybeSetDuration();
      }
      if (audio.paused) {
        audio.play().catch(() => {});
      } else {
        audio.pause();
      }
    });

    audio.addEventListener('play', () => {
      iconSpan.textContent = '❚❚';
      cancelAnimationFrame(rafId);
      rafId = requestAnimationFrame(tick);
    });

    audio.addEventListener('pause', () => {
      iconSpan.textContent = '▶';
      cancelAnimationFrame(rafId);
      draw();
      updateTimeUi();
    });

    audio.addEventListener('loadedmetadata', () => {
      duration = audio.duration || 0;
      updateTimeUi();
    });

    // Seek on click
    canvas.addEventListener('click', (e) => {
      const rect = canvas.getBoundingClientRect();
      const x = (e.clientX - rect.left) * (canvas.width / canvas.clientWidth);
      const ratio = Math.min(1, Math.max(0, x / canvas.width));
      if (duration > 0) {
        audio.currentTime = ratio * duration;
        draw();
      }
    });

    window.addEventListener('resize', resizeCanvas);

    // Cleanup when element is removed (MutationObserver minimal)
    const mo = new MutationObserver(() => {
      if (!document.body.contains(container)) {
        destroyed = true;
        cancelAnimationFrame(rafId);
        window.removeEventListener('resize', resizeCanvas);
        mo.disconnect();
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  }

  function observe() {
    const players = Array.from(document.querySelectorAll('.wbdn-voice-player'));
    if (!('IntersectionObserver' in window)) {
      players.forEach(initPlayer);
      return;
    }
    const io = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          initPlayer(entry.target);
          obs.unobserve(entry.target);
        }
      });
    }, { rootMargin: '200px 0px' });
    players.forEach(p => io.observe(p));
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', observe);
  } else {
    observe();
  }
})();


