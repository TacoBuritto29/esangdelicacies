let currentSlide = 0;
    const totalSlides = document.querySelectorAll('.slide').length;

    function moveToSlide(slideIndex) {
        const slides = document.querySelector('.slides');
        const indicators = document.querySelectorAll('.indicator');

        // Wrap around the slides
        if (slideIndex < 0) {
            currentSlide = totalSlides - 1;
        } else if (slideIndex >= totalSlides) {
            currentSlide = 0;
        } else {
            currentSlide = slideIndex;
        }

        // Update slide position
        slides.style.transform = `translateX(-${currentSlide * 100}%)`;

        // Update indicators
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentSlide);
        });
    }

    // Auto-slide functionality
    setInterval(() => {
        moveToSlide(currentSlide + 1);
    }, 5000); // 5 seconds interval