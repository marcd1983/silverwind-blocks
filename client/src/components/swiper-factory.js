import Swiper from 'swiper';
import {
  A11y,
  Autoplay,
  EffectCards,
  EffectCoverflow,
  EffectCreative,
  EffectCube,
  EffectFade,
  EffectFlip,
  FreeMode,
  Keyboard,
  Navigation,
  Pagination,
  Scrollbar,
} from 'swiper/modules';

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Plays the active slide's background video (from its start time, looping
 * back at its end time) and pauses the rest.
 */
function syncVideos(swiper) {
  swiper.slides.forEach((slide, index) => {
    const video = slide.querySelector('video[data-sw-video]');
    if (!video) return;

    if (!video.dataset.swBound) {
      video.dataset.swBound = '1';
      const start = Number(video.dataset.start || 0);
      const end = Number(video.dataset.end || 0);
      video.addEventListener('loadedmetadata', () => {
        if (start) video.currentTime = start;
      });
      if (end > start) {
        video.addEventListener('timeupdate', () => {
          if (video.currentTime >= end) video.currentTime = start;
        });
      }
    }

    if (index === swiper.activeIndex && !reducedMotion()) {
      video.play().catch(() => {});
    } else {
      video.pause();
    }
  });
}

export function createSwiper(element, options, refs, { onProgress } = {}) {
  const config = {
    modules: [A11y, Keyboard],
    ...options,
    keyboard: { enabled: true, onlyInViewport: true },
    a11y: { enabled: true },
    navigation: false,
    pagination: false,
    scrollbar: false,
    on: {
      init: syncVideos,
      slideChangeTransitionEnd: syncVideos,
      autoplayTimeLeft: (swiper, time, progress) => onProgress?.(1 - progress),
    },
  };

  if (options.navigation && refs.next && refs.prev) {
    config.modules.push(Navigation);
    config.navigation = { nextEl: refs.next, prevEl: refs.prev };
  }
  if (options.pagination && refs.pagination) {
    config.modules.push(Pagination);
    config.pagination = {
      el: refs.pagination,
      clickable: true,
      type: options.pagination === 'fraction' ? 'fraction' : 'bullets',
    };
  }
  if (options.scrollbar && refs.scrollbar) {
    config.modules.push(Scrollbar);
    config.scrollbar = { el: refs.scrollbar, draggable: true };
  }
  if (options.autoplay) {
    config.modules.push(Autoplay);
  }
  if (options.freeMode) {
    config.modules.push(FreeMode);
    config.freeMode = { enabled: true };
  }

  const effects = {
    fade: EffectFade,
    cube: EffectCube,
    flip: EffectFlip,
    coverflow: EffectCoverflow,
    creative: EffectCreative,
    cards: EffectCards,
  };
  if (effects[options.effect]) {
    config.modules.push(effects[options.effect]);
  }
  if (options.effect === 'creative') {
    config.creativeEffect = {
      prev: { translate: ['-20%', 0, -1] },
      next: { translate: ['100%', 0, 0] },
    };
  }

  return new Swiper(element, config);
}
