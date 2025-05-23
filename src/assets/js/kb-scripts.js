//import '@popperjs/core';
import * as bootstrap from 'bootstrap';

const btnUp = document.querySelector('.btn-up');
btnUp.addEventListener('click', (event) => {
  event.preventDefault();
  window.scrollTo({ top: 0, behavior: 'smooth' });
});

const header = document.getElementById('masthead');
const filters = document.getElementById('kb-filters');
const content = document.getElementById('content');
const toggleClass = 'fixed-top';
document.addEventListener('DOMContentLoaded', function () {
  window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    const headerHeight = header.offsetHeight;
    const headerTop = parseFloat(getComputedStyle(header).top);
    const filtersTopValue = headerHeight + headerTop - 1;

    if (currentScroll > 150) {
      header.classList.add(toggleClass);
      content.style.paddingTop = `${headerHeight}px`;
      if (filters && currentScroll > 300) {
        filters.style.top = `${filtersTopValue}px`;
        filters.classList.add(toggleClass);
      }
    } else {
      header.classList.remove(toggleClass);
      content.style.paddingTop = '0px';
      if (filters) {
        filters.classList.remove(toggleClass);
        filters.style.paddingTop = '0px';
      }
    }
  });
});

const myOffcanvas = document.getElementById('KbOffCanvasNavigation');
myOffcanvas.addEventListener('hidden.bs.offcanvas', (event) => {
  const accordionItems = myOffcanvas.querySelectorAll('.accordion-item');
  accordionItems.forEach((item) => {
    const button = item.querySelector('.accordion-button');
    const collapse = item.querySelector('.accordion-collapse');
    button.classList.add('collapsed');
    collapse.classList.remove('show');
  });
});
myOffcanvas.addEventListener('show.bs.offcanvas', (event) => {
  const currentMenuItem = myOffcanvas.querySelector('.current-menu-item');
  if (currentMenuItem) {
    const parentAccordionCollapse = currentMenuItem.closest('.accordion-collapse');
    const parentAccordionButton = currentMenuItem.closest('.accordion-item').querySelector('.accordion-button');
    if (parentAccordionCollapse) {
      parentAccordionCollapse.classList.add('show');
      if (parentAccordionButton) {
        parentAccordionButton.classList.remove('collapsed');
      }
    }
  }
});

let msnry = null;

window.addEventListener('DOMContentLoaded', () => {
  const masonryGrid = document.querySelector('.masonry-grid');
  if (masonryGrid) {
    msnry = new Masonry(masonryGrid, {
      itemSelector: '.shop-item',
      percentPosition: true,
      Animation: false,
    });
  }
});

/* document.body.addEventListener('is.post-load', function () {
    const masonryGrid = document.querySelector('.masonry-grid');
    if (!masonryGrid || !msnry) return;

    // Déplacer les nouveaux shop-item dans la grille
    document.querySelectorAll('.infinite-wrap').forEach(wrap => {
        const items = wrap.querySelectorAll('.shop-item');
        items.forEach(item => {
            masonryGrid.appendChild(item);
        });
        // Supprimer le conteneur temporaire
        wrap.remove();
    });

    // Réorganiser Masonry
    msnry.reloadItems();
    msnry.layout();
});
document.body.addEventListener('resize', () => {
	if (msnry) {
    msnry.reloadItems();
    msnry.layout();
  }
}); */
/* let infiniteCount = 0;

document.addEventListener('post-load', () => {
  infiniteCount += 1;
  const container = document.querySelector('#content');
  const selector = document.querySelector('#infinite-view-' + infiniteCount);
  if (selector && container) {
    const elements = selector.querySelectorAll('.infinite-wrap');
    if (msnry) {
      msnry.reloadItems();
      msnry.layout();
    }
  }
}); */
