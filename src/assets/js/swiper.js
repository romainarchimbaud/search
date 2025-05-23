import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay, EffectFade } from 'swiper/modules';

Swiper.use([Navigation, Pagination, Autoplay, EffectFade]);

document.addEventListener('DOMContentLoaded', () => {

  /**
   * Swiper for events posts (Agenda)
   */
  new Swiper('.swiper-events-posts', {
    loop: true,
    spaceBetween: 30,
    slidesPerView: 1.1,
    /* autoplay: {
      delay: 5000,
    }, */
    speed: 500,
    navigation: {
      nextEl: '.swiper-events-next',
      prevEl: '.swiper-events-prev'
    },
    breakpoints: {
      768: {
        slidesPerView: 2,
        slidesPerGroup: 2,
      },
    }
  });

  /**
   * Swiper for partners
   */
  new Swiper('.swiper-partners', {
    loop: true,
    slidesPerView: 1,
    pagination: {
      el: '.swiper-pagination',
      //clickable: true,
    },
    autoplay: {
      delay: 5000,
    },
    speed: 500,
    breakpoints: {
      640: {
        slidesPerView: 2,
        spaceBetween: 30,
      },
      768: {
        slidesPerView: 2,
        spaceBetween: 30,
      },
      1024: {
        loop: false,
        slidesPerView: 4,
        spaceBetween: 30,
      },
    }
  });

  /**
   * Swiper for Featured Posts
   */
  new Swiper('.swiper-featured-posts', {
    loop: true,
    centeredSlides: true,
    spaceBetween: 10,
    slidesPerView: 1.5,
    slidesPerGroup: 1,
    speed: 500,
    autoplay: {
      delay: 5000,
    },
    pagination: {
      el: '.swiper-pagination',
      //clickable: true,
      dynamicBullets: true,
    },
    /* navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev'
    }, */
    breakpoints: {
      640: {
        slidesPerView: 2,
        slidesPerGroup: 1,
      },
      768: {
        slidesPerView: 2.1,
        slidesPerGroup: 1,
        centeredSlides: true,
        spaceBetween: 12,
      },
      1200: {
        slidesPerView: 4.5,
        slidesPerGroup: 2,
        spaceBetween: 30,
        centeredSlides: true,
      }
    }
  });

  /**
   * Swiper for city categories
   */
  new Swiper('.swiper-cities', {
    loop: true,
    centeredSlides: false,
    spaceBetween: 10,
    slidesPerView: 1.2,
    slidesPerGroup: 1,
    speed: 500,
    /* autoplay: {
      delay: 2000,
    }, */
    pagination: {
      el: '.swiper-pagination',
      //clickable: true,
      //dynamicBullets: true,
    },
    /* navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev'
    }, */
    breakpoints: {
      /* 640: {
        slidesPerView: 2,
        slidesPerGroup: 1,
      },*/
      768: {
        slidesPerView: 2.1,
        slidesPerGroup: 1,
        centeredSlides: false,
        spaceBetween: 12,
      },
      1024: {
        slidesPerView: 2.5,
        spaceBetween: 15,
        centeredSlides: false,
      },
      1200: {
        slidesPerView: 3.3,
        //slidesPerGroup: 3,
        spaceBetween: 30,
        centeredSlides: false,
      }
    }
  });

  /**
   * Swiper top5
   */
  new Swiper('.swiper-top5', {
    loop: true,
    effect: 'fade',
    fadeEffect: {
      crossFade: true
    },
    pagination: {
      el: '.swiper-pagination',
      clickable: true,
      renderBullet: function (index, className) {
        return '<span class="' + className + '">' + (index + 1) + "</span>";
      },
    },
    navigation: {
      nextEl: '.swiper-top5-next',
      prevEl: '.swiper-top5-prev'
    },
    /* autoplay: {
      delay: 5000,
    },
    speed: 750, */

  });

  /**
   * Swiper for city categories
   */
  new Swiper('.swiper-shop', {
    loop: true,
    centeredSlides: false,
    spaceBetween: 10,
    slidesPerView: 1.2,
    slidesPerGroup: 1,
    speed: 500,
    /* autoplay: {
      delay: 2000,
    }, */
    pagination: {
      el: '.swiper-pagination',
    },
    /* navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev'
    }, */
    breakpoints: {
      /* 640: {
        slidesPerView: 2,
        slidesPerGroup: 1,
      },*/
      768: {
        slidesPerView: 2.1,
        slidesPerGroup: 1,
        centeredSlides: false,
        spaceBetween: 12,
      },
      1024: {
        slidesPerView: 2.5,
        spaceBetween: 15,
        centeredSlides: false,
      },
      1200: {
        slidesPerView: 3.3,
        //slidesPerGroup: 3,
        spaceBetween: 30,
        centeredSlides: false,
      }
    }
  });

});
