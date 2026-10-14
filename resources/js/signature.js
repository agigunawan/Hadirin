import SignaturePad from 'signature_pad';

export function initSignaturePad(canvasElement, options = {}) {
    if (!canvasElement) return null;

    canvasElement.style.touchAction = 'none';

    const pad = new SignaturePad(canvasElement, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: '#0f172a',
        minWidth: 1.5,
        maxWidth: 3.5,
        ...options,
    });

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvasElement.getBoundingClientRect();
        const width = Math.round(rect.width || canvasElement.offsetWidth || 300);
        const height = Math.round(rect.height || canvasElement.offsetHeight || 176);

        if (width === 0 || height === 0) return;

        if (canvasElement.width !== width * ratio || canvasElement.height !== height * ratio) {
            const data = pad.toData();
            canvasElement.width = width * ratio;
            canvasElement.height = height * ratio;
            const ctx = canvasElement.getContext('2d');
            ctx.scale(ratio, ratio);
            pad.clear();
            if (data && data.length > 0) {
                pad.fromData(data);
            }
        }
    }

    window.addEventListener('resize', resizeCanvas);
    requestAnimationFrame(resizeCanvas);

    pad.resize = resizeCanvas;
    return pad;
}

window.initSignaturePad = initSignaturePad;
