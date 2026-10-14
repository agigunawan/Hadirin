export async function getCurrentCoordinates() {
    if (!navigator.geolocation) {
        throw new Error('Geolocation tidak didukung oleh browser Anda.');
    }

    const tryGetPosition = (options) => {
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude.toFixed(7);
                    const lng = position.coords.longitude.toFixed(7);
                    resolve(`${lat}, ${lng}`);
                },
                (error) => reject(error),
                options
            );
        });
    };

    try {
        // Coba akurasi tinggi terlebih dahulu dengan timeout 4.5 detik
        return await tryGetPosition({
            enableHighAccuracy: true,
            timeout: 4500,
            maximumAge: 120000,
        });
    } catch (highErr) {
        // Jika ditolak oleh user, langsung lempar error izin
        if (highErr.code === highErr.PERMISSION_DENIED) {
            throw new Error('Izin akses lokasi ditolak oleh browser.');
        }

        try {
            // Fallback ke akurasi standar (IP/WiFi) dengan timeout 3.5 detik
            return await tryGetPosition({
                enableHighAccuracy: false,
                timeout: 3500,
                maximumAge: 300000,
            });
        } catch (lowErr) {
            let message = 'Gagal mendeteksi lokasi.';
            if (lowErr.code === lowErr.PERMISSION_DENIED) {
                message = 'Izin akses lokasi ditolak.';
            } else if (lowErr.code === lowErr.TIMEOUT) {
                message = 'Waktu permintaan lokasi habis (timeout).';
            } else if (lowErr.code === lowErr.POSITION_UNAVAILABLE) {
                message = 'Sensor lokasi tidak aktif atau tidak tersedia.';
            }
            throw new Error(message);
        }
    }
}

window.getCurrentCoordinates = getCurrentCoordinates;
