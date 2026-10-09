(function () {
    document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => {
        const thermal = button.dataset.print === 'thermal';
        document.body.classList.toggle('receipt-thermal', thermal);
        document.getElementById('wtbm-print-page').textContent = thermal ? '@page{size:auto;margin:4mm}' : '@page{size:A4;margin:14mm}';
        const images = Array.from(document.images).filter(image => !image.complete);
        Promise.all(images.map(image => new Promise(resolve => { image.addEventListener('load', resolve, {once:true}); image.addEventListener('error', resolve, {once:true}); }))).then(() => window.print());
    }));
})();
