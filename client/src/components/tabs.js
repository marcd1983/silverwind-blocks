/**
 * Accessible tabs (WAI-ARIA tabs pattern, automatic activation).
 * Arrow keys / Home / End move between tabs; a URL hash matching a panel id
 * (e.g. #tab-12) opens that tab.
 */
export default () => ({
  active: null,
  ids: [],

  init() {
    this.ids = [...this.$el.querySelectorAll('[role="tab"][data-tab]')].map((tab) => tab.dataset.tab);
    this.active = this.fromHash() ?? this.ids[0] ?? null;
    this.onHash = () => {
      const id = this.fromHash();
      if (id) this.select(id);
    };
    window.addEventListener('hashchange', this.onHash);
  },

  destroy() {
    window.removeEventListener('hashchange', this.onHash);
  },

  fromHash() {
    const id = decodeURIComponent(window.location.hash.slice(1));
    return this.ids.includes(id) ? id : null;
  },

  isActive(id) {
    return this.active === id;
  },

  select(id, focus = false) {
    this.active = id;
    if (focus) {
      this.$el.querySelector(`[role="tab"][data-tab="${CSS.escape(id)}"]`)?.focus();
    }
  },

  keydown(event) {
    const index = this.ids.indexOf(this.active);
    const last = this.ids.length - 1;
    const target = {
      ArrowRight: index === last ? 0 : index + 1,
      ArrowLeft: index === 0 ? last : index - 1,
      Home: 0,
      End: last,
    }[event.key];

    if (target === undefined) return;
    event.preventDefault();
    this.select(this.ids[target], true);
  },
});
