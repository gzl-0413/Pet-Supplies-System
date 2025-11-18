document.addEventListener('DOMContentLoaded', () => {
    const captureButton = document.getElementById('captureButton');
    const webcamContainer = document.getElementById('webcamContainer');
    const webcam = document.getElementById('webcam');
    const photoCanvas = document.getElementById('photoCanvas');
    const photoPreview = document.getElementById('photoPreview');
    const takePhotoButton = document.getElementById('takePhoto');
    const photoInput = document.getElementById('photo');

    async function startWebcam() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            webcam.srcObject = stream;
        } catch (error) {
            console.error('Error accessing webcam:', error);
        }
    }

    captureButton.addEventListener('click', () => {
        webcamContainer.style.display = 'block';
        startWebcam();
    });

    takePhotoButton.addEventListener('click', () => {
        const context = photoCanvas.getContext('2d');
        photoCanvas.width = webcam.videoWidth;
        photoCanvas.height = webcam.videoHeight;
        context.drawImage(webcam, 0, 0);

        const photoDataUrl = photoCanvas.toDataURL('image/jpeg');

        const byteString = atob(photoDataUrl.split(',')[1]);
        const mimeString = photoDataUrl.split(',')[0].split(':')[1].split(';')[0];
        const ab = new Uint8Array(byteString.length);

        for (let i = 0; i < byteString.length; i++) {
            ab[i] = byteString.charCodeAt(i);
        }

        const blob = new Blob([ab], { type: mimeString });
        const file = new File([blob], 'webcam_photo.jpg', { type: mimeString });

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);

        const fileInput = document.getElementById('photo');
        fileInput.files = dataTransfer.files;

        photoPreview.src = photoDataUrl;
    });
});