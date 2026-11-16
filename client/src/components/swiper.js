/**
 * Alpine wrapper around Swiper for sliders and carousels.
 *
 * Options come from the block (data-options JSON); the controls are found by
 * x-ref (prev, next, pagination, scrollbar). Swiper itself is imported on
 * demand, so pages without a slider never download it. Visitors who prefer
 * reduced motion get no autoplay and no video playback.
 */
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export default () => ({
  swiper: null,
  autoplay: false,
  paused: false,
  progress: 0,

  async init() {
    const options = JSON.parse(this.$el.dataset.options || '{}');
    if (reducedMotion()) {
      delete options.autoplay;
    }
    this.autoplay = Boolean(options.autoplay);

    const { createSwiper } = await import('./swiper-factory.js');
    this.swiper = createSwiper(this.$refs.swiper, options, this.$refs, {
      onProgress: (progress) => {
        this.progress = progress;
      },
    });
  },

  destroy() {
    this.swiper?.destroy(true, true);
  },

  toggleAutoplay() {
    if (!this.swiper?.autoplay) return;
    if (this.paused) {
      this.swiper.autoplay.start();
    } else {
      this.swiper.autoplay.stop();
    }
    this.paused = !this.paused;
  },
});
