document.addEventListener('DOMContentLoaded', function() {
 const images = document.querySelectorAll('.fade-in-prints');
 const observer = new IntersectionObserver((entries) => {
   entries.forEach(entry => {
     if (entry.isIntersecting) {
       entry.target.classList.add('visible');
     }
   });
 }, { threshold: 0.1 });

 images.forEach(image => {
   observer.observe(image);
 });
});