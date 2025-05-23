// Import des fonctions d'initialisation des différents modules.

import { initializeGlobalSearchForm } from './modules/kb-global-search-form.js'; // Gère le formulaire de recherche global
import { initializeSearchPageJQuery } from './modules/kb-search-page.js'; // Gère la page de résultats de recherche
import { initializeArchiveFiltersJQuery } from './modules/kb-archive-filters.js'; // Gère les filtres sur les pages d'archives
import { initializeArchiveInfiniteScrollJQuery } from './modules/kb-archive-infinite-scroll.js'; // Gère l'infinite scroll sur les pages d'archives

jQuery(document).ready(function ($) {
  // console.log("KindaBreak jQuery Modules Initializer: Document ready.");

  // Récupérer les données de configuration globales injectées par PHP (window.searchData).
  const siteSearchData = window.searchData;

  // Vérifier si les données de configuration sont disponibles.
  if (siteSearchData) {
    // console.log("KindaBreak jQuery Modules Initializer: siteSearchData found.", siteSearchData);

    // Initialiser le module du formulaire de recherche global.
    // Ce module gère l'UI du formulaire et sa soumission native.
    initializeGlobalSearchForm(siteSearchData);

    // Initialiser le module pour la page de résultats de recherche.
    // Ce module gère le chargement AJAX des résultats et l'infinite scroll sur /?s=...
    initializeSearchPageJQuery(siteSearchData);

    // Initialiser le module pour les filtres sur les pages d'archives.
    // Ce module gère l'UI des filtres d'archive et déclenche un événement lors des changements.
    initializeArchiveFiltersJQuery(siteSearchData);

    // Initialiser le module d'infinite scroll pour les pages d'archives.
    // Ce module écoute les événements de filtre et charge les posts.
    initializeArchiveInfiniteScrollJQuery(siteSearchData);
  } else {
    // Afficher un avertissement dans la console si searchData est manquant,
    // car les fonctionnalités de recherche et d'archive en dépendent fortement.
    const $modalForm = $('#advanced-search-form-modal');
    const $resultsContainer = $('#kb_infinite_scroll'); // Peut être sur search.php ou archives.
    const $archiveFilters = $('#archive-filters-form');

    if ($modalForm.length || $resultsContainer.length || $archiveFilters.length) {
      console.warn('KindaBreak jQuery Modules Initializer: `window.searchData` is missing. Advanced search and archive features might not function correctly.');
    }
  }

  // Autre code jQuery global qui pourrait être nécessaire pour le thème
  // et qui ne fait pas partie d'un module spécifique.
  // Exemple :
  // $('.votre-classe-globale-initialisee-par-jquery').fadeIn();
});
