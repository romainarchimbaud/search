// assets/js/modules/kb-search-page.js

/**
 * Module jQuery pour la gestion de la page de résultats de recherche KindaBreak (search.php).
 * Responsable du chargement initial des résultats via AJAX (basé sur les paramètres de l'URL),
 * de l'affichage de ces résultats, et de la gestion de l'infinite scroll.
 *
 * Dépend de jQuery et des données `searchData` injectées globalement.
 */
export function initializeSearchPageJQuery(searchDataConfig) {
  // S'assurer que jQuery est disponible.
  if (typeof jQuery === 'undefined') {
    // console.warn('KB Search Page: jQuery is not available. Module not initialized.');
    return;
  }
  const $ = jQuery; // Alias local pour jQuery.

  // Utiliser les données de configuration globales.
  const searchData = searchDataConfig || window.searchData;
  if (typeof searchData === 'undefined' || !searchData.ajax_url || !searchData.nonce || !searchData.labels) {
    // console.warn('KB Search Page: Essential searchData properties are missing.');
    return;
  }

  // --- SÉLECTEURS JQUERY POUR LES ÉLÉMENTS DE LA PAGE DE RÉSULTATS ---
  const $resultsContainer = $('#kb_infinite_scroll'); // Conteneur principal où les posts sont affichés.
  const $totalResultsSpan = $('#search-total-results'); // Span pour afficher le nombre total de résultats.
  const $loader = $('#search-loader'); // Indicateur de chargement AJAX.
  const $debugUi = $('#search-debug-ui'); // Conteneur pour les informations de débogage (optionnel).
  const $appliedCriteriaCode = $('#applied-criteria-json'); // Zone pour afficher le JSON des critères (optionnel).
  const $loadMoreSentinel = $('#load-more-sentinel'); // Élément observé pour déclencher l'infinite scroll.

  // Variables d'état pour la pagination et le chargement sur search.php.
  let searchCurrentPage = 1;
  let searchIsLoading = false;
  let searchMaxPages = 1;
  let currentSearchXHR = null; // Pour stocker l'objet jqXHR et pouvoir l'annuler.

  /**
   * Détermine si la page actuelle est une page de résultats de recherche WordPress.
   * Basé sur la présence de classes dans le body ou du paramètre 's' dans l'URL.
   * @returns {boolean}
   */
  function isOnSearchPage() {
    return (
      $('body').hasClass('search-results') ||
      $('body').hasClass('search') ||
      new URLSearchParams(window.location.search).has('s')
    );
  }

  // Exécuter la logique de ce module seulement si on est sur search.php et que les éléments DOM nécessaires sont présents.
  if (isOnSearchPage() && $resultsContainer.length) {
    // console.log("KB Search Page (jQuery): Initializing for search results page (search.php).");

    /**
     * Construit un objet URLSearchParams à partir des paramètres de l'URL actuelle.
     * Ces paramètres sont ceux envoyés par la soumission native du formulaire de recherche global.
     * @returns {URLSearchParams}
     */
    function getSearchParamsFromUrlForAjax() {
      const urlParams = new URLSearchParams(window.location.search);
      const ajaxParams = new URLSearchParams(); // Utiliser URLSearchParams pour la cohérence, sera converti en objet pour $.ajax

      // Clés valides attendues par le backend pour la recherche.
      const validKeys = [
        's',
        'category',
        'subcategory',
        'tag',
        'ville',
        'geoloc',
        'user_lat',
        'user_lng',
        'keyword',
        'post_type',
      ];

      urlParams.forEach((value, key) => {
        if (validKeys.includes(key)) {
          // Gérer le cas où 'keyword' est utilisé dans l'URL au lieu de 's'.
          if (key === 'keyword' && !urlParams.has('s')) {
            ajaxParams.append('s', value);
          } else if (key !== 'keyword' || (key === 's' && value)) {
            // Prendre 's' ou les autres clés.
            ajaxParams.append(key, value);
          }
        }
      });
      // WordPress attend 's', même vide, pour considérer une requête comme une recherche.
      if (!ajaxParams.has('s')) {
        ajaxParams.append('s', '');
      }
      // Le post_type est défini par PHP (KB_MAIN_SEARCH_POST_TYPES), pas besoin de le forcer ici
      // si le formulaire de la modale ne l'envoie plus dans l'URL.
      return ajaxParams;
    }

    /**
     * Effectue la requête AJAX pour récupérer et afficher les résultats de recherche sur search.php.
     * @param {boolean} isNewSearch - True pour une nouvelle recherche (vide le conteneur), false pour l'infinite scroll.
     */
    function fetchResultsOnSearchPage(isNewSearch = false) {
      if (searchIsLoading) {
        return;
      }
      searchIsLoading = true;
      $loader.show();
      $resultsContainer.find('.no-more-posts-message').remove(); // Enlever l'ancien message "plus de posts".

      if (isNewSearch) {
        // Pour une nouvelle recherche ou le chargement initial.
        searchCurrentPage = 1;
        $resultsContainer.html(''); // Vider les résultats précédents.
        $loadMoreSentinel.show(); // Réafficher la sentinelle.
      }
      // Pour l'infinite scroll, searchCurrentPage est déjà incrémenté par l'observer.

      const paramsFromUrl = getSearchParamsFromUrlForAjax(); // Les filtres viennent de l'URL.
      // Convertir URLSearchParams en objet simple pour la propriété `data` de jQuery.ajax.
      const ajaxData = {
        action: 'kb_advanced_search', // IMPORTANT: Doit correspondre à l'action AJAX en PHP.
        nonce: searchData.nonce,
        page: searchCurrentPage.toString(),
      };
      paramsFromUrl.forEach((value, key) => {
        ajaxData[key] = value;
      });

      // Affichage optionnel des critères pour débogage.
      if ($debugUi.length && $appliedCriteriaCode.length) {
        $debugUi.show();
        const debugParamsForDisplay = { ...ajaxData }; // Cloner pour affichage.
        delete debugParamsForDisplay.action;
        delete debugParamsForDisplay.nonce;
        delete debugParamsForDisplay.page;
        $appliedCriteriaCode.text(JSON.stringify(debugParamsForDisplay, null, 2));
      }

      // Annuler la requête AJAX précédente si elle est toujours en cours.
      if (currentSearchXHR) {
        currentSearchXHR.abort();
      }

      currentSearchXHR = $.ajax({
        url: searchData.ajax_url,
        type: 'POST',
        data: ajaxData,
        dataType: 'json', // jQuery essaiera de parser la réponse en JSON.
        success: function (data) {
          if (data.success) {
            // Insérer le HTML des résultats.
            if (isNewSearch) {
              $resultsContainer.html(data.data.html || '');
            } else {
              $resultsContainer.append(data.data.html || '');
            }

            // Mettre à jour les informations de pagination et le total.
            //$totalResultsSpan.text(data.data.count !== undefined ? data.data.count : 0);
            searchMaxPages = data.data.max_pages !== undefined ? parseInt(data.data.max_pages, 10) : 0;

            // Gérer la fin de l'infinite scroll ou l'absence de résultats.
            if (searchCurrentPage >= searchMaxPages || data.data.count === 0 || !data.data.html) {
              $loadMoreSentinel.hide(); // Cacher la sentinelle.
              if ($resultsContainer.html() !== '' && (data.data.html === '' || !data.data.html)) {
                // N'afficher que si des posts étaient déjà là
                if (!$resultsContainer.find('.no-more-posts-message').length) {
                  $resultsContainer.append(
                    `<p class="text-center text-muted py-3 no-more-posts-message">${searchData.labels.no_more_posts}</p>`
                  );
                }
              } else if (data.data.count === 0 && isNewSearch) {
                // Si 0 résultat dès la première recherche
                if (!$resultsContainer.find('.no-more-posts-message').length) {
                  // Eviter doublon si HTML vide
                  $resultsContainer.html(
                    `<p class="text-center text-muted py-3 no-more-posts-message">${
                      searchData.labels.no_results || 'Aucun résultat trouvé.'
                    }</p>`
                  );
                }
              }
            } else {
              $loadMoreSentinel.show(); // S'assurer que la sentinelle est visible.
            }
            // L'URL est déjà celle de la recherche GET après redirection,
            // pas besoin de history.pushState ici pour les filtres de base.
          } else {
            // Erreur renvoyée par l'API WordPress.
            // console.error('Search API Error (jQuery):', data.data?.message || 'Unknown API error');
            $resultsContainer.html(
              `<p>Erreur lors de la recherche : ${data.data?.message || 'Veuillez réessayer.'}</p>`
            );
            $totalResultsSpan.text('0');
            $loadMoreSentinel.hide();
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          if (textStatus !== 'abort') {
            // Ignorer les erreurs d'annulation.
            // console.error('Search AJAX Error (jQuery):', textStatus, errorThrown);
            $resultsContainer.html(`<p>Erreur de communication : ${textStatus}</p>`);
            $totalResultsSpan.text('0');
            $loadMoreSentinel.hide();
          }
        },
        complete: function () {
          searchIsLoading = false;
          $loader.hide();
          currentSearchXHR = null;
        },
      });
    }

    /**
     * Initialise la page de recherche en effectuant la première requête AJAX
     * pour charger les résultats basés sur les paramètres de l'URL.
     */
    function initializeSearchPage() {
      if ($resultsContainer.length) {
        // S'assurer que le conteneur de résultats existe.
        fetchResultsOnSearchPage(true); // true pour une nouvelle recherche (chargement initial).
      }
    }
    // Lancer l'initialisation de la page de recherche.
    initializeSearchPage();

    // Mettre en place l'infinite scroll pour la page de recherche.
    if ($loadMoreSentinel.length && typeof IntersectionObserver !== 'undefined') {
      const searchPageObserver = new IntersectionObserver(
        (entries) => {
          if (
            entries[0].isIntersecting &&
            !searchIsLoading &&
            searchCurrentPage < searchMaxPages &&
            searchMaxPages > 0
          ) {
            searchCurrentPage++; // Incrémenter la page pour la prochaine requête.
            fetchResultsOnSearchPage(false); // false car on ajoute des posts.
          }
        },
        { threshold: 0.8 }
      ); // Déclencher quand 80% de la sentinelle est visible.
      searchPageObserver.observe($loadMoreSentinel[0]); // L'observer attend un élément DOM.
    } else if ($loadMoreSentinel.length) {
      // Si IntersectionObserver n'est pas supporté, ou si pas plus de pages dès le début.
      $loadMoreSentinel.hide();
    }
  } // Fin de if (isOnSearchPage() && $resultsContainer.length)
} // Fin de initializeSearchPageJQuery
