/*==============================================
  Image Lazy Loading and Optimization
===============================================*/

(function() {
    'use strict';

    // Lazy Loading Images
    function lazyLoadImages() {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('fade-in');
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                    
                    img.onload = function() {
                        this.classList.add('loaded');
                    };
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // Native lazy loading fallback
    function setupNativeLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img:not([loading])');
            images.forEach(img => {
                // Skip carousel images as they should load immediately
                if (!img.closest('.carousel')) {
                    img.loading = 'lazy';
                }
            });
        } else {
            // Fallback for browsers that don't support native lazy loading
            lazyLoadImages();
        }
    }

    // Optimize image display
    function optimizeImages() {
        const images = document.querySelectorAll('img:not([width]):not([height])');
        
        images.forEach(img => {
            // Add aspect ratio for images without dimensions
            img.addEventListener('load', function() {
                if (!this.hasAttribute('width') && !this.hasAttribute('height')) {
                    const aspectRatio = this.naturalHeight / this.naturalWidth;
                    this.style.aspectRatio = `${this.naturalWidth} / ${this.naturalHeight}`;
                }
            });
        });
    }

    // Progressive image loading for carousel
    function loadCarouselImages() {
        const carouselImages = document.querySelectorAll('.carousel-image');
        
        carouselImages.forEach((img, index) => {
            // Load first image immediately, others progressively
            if (index === 0) {
                img.loading = 'eager';
            } else {
                img.loading = 'lazy';
            }
        });
    }

    // Handle image load errors
    function handleImageErrors() {
        document.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG') {
                // console.warn('Image failed to load:', e.target.src);
                // Optionally add a placeholder or retry logic
                e.target.classList.add('image-error');
            }
        }, true);
    }

    // Add smooth fade-in effect to images
    function addImageTransitions() {
        const allImages = document.querySelectorAll('img');
        
        allImages.forEach(img => {
            if (img.complete && img.naturalHeight !== 0) {
                img.classList.add('loaded');
            } else {
                img.addEventListener('load', function() {
                    this.classList.add('loaded', 'fade-in');
                });
            }
        });
    }

    // Responsive image quality based on connection speed
    function adaptiveImageLoading() {
        if ('connection' in navigator) {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            
            if (connection && connection.effectiveType) {
                const slowConnections = ['slow-2g', '2g', '3g'];
                
                if (slowConnections.includes(connection.effectiveType)) {
                    // Add class to body for CSS to target
                    document.body.classList.add('slow-connection');
                    // console.info('Slow connection detected, optimizing image loading');
                }
            }
        }
    }

    // Initialize all image optimizations
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setupNativeLazyLoading();
                optimizeImages();
                loadCarouselImages();
                handleImageErrors();
                addImageTransitions();
                adaptiveImageLoading();
            });
        } else {
            setupNativeLazyLoading();
            optimizeImages();
            loadCarouselImages();
            handleImageErrors();
            addImageTransitions();
            adaptiveImageLoading();
        }
    }

    // Start initialization
    init();

    // Expose utility function for manual lazy loading if needed
    window.initLazyLoading = lazyLoadImages;

})();
