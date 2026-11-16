/**
 * Gallery lightbox on a native <dialog>: Escape/backdrop close, arrow keys
 * and swipe to move, focus returns to the thumbnail on close.
 */
const SWIPE_THRESHOLD = 40;

export default () => ({
  items: [],
  index: 0,
  opener: null,
  touchX: null,

  get current() {
    return this.items[this.index] ?? { src: '', alt: '', caption: '' };
  },

  init() {
    this.items = [...this.$el.querySelectorAll('[data-lightbox]')].map((link) => ({
      link,
      src: link.getAttribute('href'),
      alt: link.dataset.alt || '',
      caption: link.dataset.caption || '',
    }));
  },

  open(link) {
    this.index = Math.max(0, this.items.findIndex((item) => item.link === link));
    this.opener = link;
    this.$refs.dialog.showModal();
  },

  closed() {
    this.opener?.focus();
  },

  next() {
    this.index = (this.index + 1) % this.items.length;
  },

  prev() {
    this.index = (this.index - 1 + this.items.length) % this.items.length;
  },

  counter() {
    return this.items.length > 1 ? `${this.index + 1} / ${this.items.length}` : '';
  },

  touchStart(event) {
    this.touchX = event.changedTouches[0].clientX;
  },

  touchEnd(event) {
    if (this.touchX === null) return;
    const delta = event.changedTouches[0].clientX - this.touchX;
    this.touchX = null;
    if (Math.abs(delta) < SWIPE_THRESHOLD) return;
    delta < 0 ? this.next() : this.prev();
  },
});
