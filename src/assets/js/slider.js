class Slider {

  /**
   * @param {HTMLDivElement} el
   */
  constructor(el) {
    this.nextButton = el.querySelector('[data-slider-next]')
    this.prevButton = el.querySelector('[data-slider-prev]')
    this.wrapper = el.querySelector('[data-slider-wrapper]')
    if (this.nextButton !== null && this.prevButton !== null) {
      this.nextButton.addEventListener('click', () => this.move(1))
      this.prevButton.addEventListener('click', () => this.move(-1))
      this.wrapper.addEventListener('scrollend', () => this.updateUI())
      this.updateUI()
    }
  }

  /**
  * Utilise la variable --items pour déterminer le nombre d'élément visible
  **/
  get itemsToScroll () {
    return parseInt(window.getComputedStyle(this.wrapper).getPropertyValue('--items'), 10);
  }

  /**
  * Nombre total de "pages" dans notre carrousel
  * @returns {number}
  **/
  get pages () {
    return Math.ceil(this.wrapper.children.length / this.itemsToScroll)
  }

  /**
  * Page courante
  * @returns {number}
  **/
  get page () {
    return Math.ceil(this.wrapper.scrollLeft / this.wrapper.offsetWidth)
  }

  /**
  * Affiche / Masque les boutons de navigation
  **/
  updateUI () {
    if (this.page === 0) {
      this.prevButton.setAttribute('disabled', 'disabled')
    } else {
      this.prevButton.removeAttribute('disabled')
    }
    if (this.page === this.pages - 1) {
      this.nextButton.setAttribute('disabled', 'disabled')
    } else {
      this.nextButton.removeAttribute('disabled')
    }
  }

  /**
   * Déplace le carousel de n pages
   * @param {number} n
   */
  move (n) {
    let newPage = this.page + n

    if (newPage < 0) {
      newPage = 0;
    }

    if (newPage >= this.pages) {
      newPage = this.pages - 1
    }

    this.wrapper.scrollTo({
      left: this.wrapper.children[newPage * this.itemsToScroll].offsetLeft,
      behavior: 'smooth'
    })
  }

}

// On branche notre comportement à tous les éléments
Array.from(document.querySelectorAll("[data-slider]"))
    .map(el => new Slider(el))
