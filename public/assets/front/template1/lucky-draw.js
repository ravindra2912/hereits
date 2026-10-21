/* Lucky Draw Front Master JavaScript - Template 1 */
let audioCtx = null;

function playClickSound(speedRatio = 0.5) {
    try {
        let AudioCtxClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtxClass) return;
        if (!audioCtx) audioCtx = new AudioCtxClass();
        if (audioCtx.state === 'suspended') audioCtx.resume();

        let osc = audioCtx.createOscillator();
        let gain = audioCtx.createGain();

        let freq = 450 + (speedRatio * 350);
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(100, audioCtx.currentTime + 0.035);

        gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.035);

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        osc.start();
        osc.stop(audioCtx.currentTime + 0.035);
    } catch(e) {}
}

function playWinSound() {
    try {
        let AudioCtxClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtxClass) return;
        if (!audioCtx) audioCtx = new AudioCtxClass();
        if (audioCtx.state === 'suspended') audioCtx.resume();

        // Celebratory Win Fanfare (Notes: C5 -> E5 -> G5 -> C6)
        let notes = [523.25, 659.25, 783.99, 1046.50];
        let noteTimes = [0, 0.12, 0.24, 0.40];
        let noteDurations = [0.12, 0.12, 0.16, 0.65];

        noteTimes.forEach((startTime, idx) => {
            let osc = audioCtx.createOscillator();
            let gain = audioCtx.createGain();

            osc.type = (idx === 3) ? 'sine' : 'triangle';
            osc.frequency.setValueAtTime(notes[idx], audioCtx.currentTime + startTime);

            let t = audioCtx.currentTime + startTime;
            gain.gain.setValueAtTime(0.001, t);
            gain.gain.linearRampToValueAtTime(0.35, t + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, t + noteDurations[idx]);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(t);
            osc.stop(t + noteDurations[idx]);
        });
    } catch(e) {}
}

function triggerPointerBounce(pointerId = 'spinnerPointer') {
    let ptr = document.getElementById(pointerId);
    if (ptr) {
        ptr.classList.remove('pointer-tick');
        void ptr.offsetWidth;
        ptr.classList.add('pointer-tick');
    }
}
