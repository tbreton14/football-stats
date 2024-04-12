if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/pwa/sw.js')
        .then(function () {console.log('Enregistrement reussi.')})
        .catch(function (e) {console.error(e)});
}