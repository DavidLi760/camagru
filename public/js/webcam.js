const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const snapBtn = document.getElementById('snapBtn');
const photoInput = document.getElementById('photoInput');

// Accéder à la webcam
navigator.mediaDevices.getUserMedia({ video: true })
  .then(stream => { video.srcObject = stream; })
  .catch(err => { console.error("Webcam inaccessible", err); });

// Activer le bouton quand un sticker est choisi
document.querySelectorAll('.sticker').forEach(sticker => {
  sticker.addEventListener('click', () => {
    snapBtn.disabled = false;
  });
});

// Capture photo
snapBtn.addEventListener('click', () => {
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  canvas.getContext('2d').drawImage(video, 0, 0);

  // Convertir en base64
  photoInput.value = canvas.toDataURL('image/png');

  // Soumettre le formulaire
  document.getElementById('uploadForm').submit();
});

