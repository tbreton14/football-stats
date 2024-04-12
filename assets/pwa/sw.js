//lors de l'installation
self.addEventListener('install' ,evt =>{
})


//capture des events
self.addEventListener('fetch' ,evt =>{
    console.log('fetch evt sur url' ,evt.request.url);
})