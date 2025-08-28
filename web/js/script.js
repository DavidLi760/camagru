let lastModified = Date.now();

setInterval(() => {
    fetch('js/script.js', { method: 'HEAD' })
        .then(response => {
            let newModified = new Date(response.headers.get('last-modified')).getTime();
            if (newModified > lastModified) {
                lastModified = newModified;
                location.reload(); // recharge la page si le JS a changé
            }
        })
        .catch(err => console.log(err));
}, 1000); // toutes les 1 seconde
