/*
 * Front-end behaviour for silverwind-blocks. Loaded as a module script
 * after the theme's, so it registers its Alpine components before the theme
 * starts Alpine (on DOMContentLoaded).
 */
import tabs from './components/tabs.js';
import lightbox from './components/lightbox.js';
import swiper from './components/swiper.js';
import { initModals } from './components/modals.js';

document.addEventListener('alpine:init', () => {
  window.Alpine.data('swTabs', tabs);
  window.Alpine.data('swLightbox', lightbox);
  window.Alpine.data('swSwiper', swiper);
});

initModals();
