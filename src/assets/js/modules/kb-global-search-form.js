// assets/js/modules/kb-global-search-form.js

/**
 * Module jQuery pour la gestion du formulaire de recherche globale KindaBreak.
 * Ce formulaire est typiquement accessible depuis tout le site (ex: dans un header via modale/offcanvas).
 *
 * PHP est responsable du rendu initial des options des catégories parentes et des radios de tags.
 * Ce module JavaScript gère :
 *  - Le pré-remplissage des champs du formulaire basé sur le contexte de la page actuelle
 *    (informations fournies par PHP dans `searchData.current_context_filters`).
 *  - Le peuplement dynamique des sous-catégories en fonction de la catégorie parente.
 *  - Le peuplement dynamique et le filtrage des villes en fonction du tag sélectionné.
 *  - La logique conditionnelle complexe de l'interface (activation/désactivation de champs,
 *    affichage conditionnel des tags Landes/Pyrénées).
 *  - La gestion de la géolocalisation.
 *  - La préparation du formulaire pour une soumission GET native qui redirige vers la page de résultats.
 *
 * Dépend de jQuery et des données `searchData` injectées globalement.
 */
export function initializeGlobalSearchForm(searchDataConfig) {
  // S'assurer que jQuery est disponible.
  if (typeof jQuery === 'undefined') {
    // console.warn('KB Global Search Form: jQuery is not available. Module not initialized.');
    return;
  }
  const $ = jQuery; // Alias local pour jQuery.

  // Utiliser les données de configuration globales.
  const searchData = searchDataConfig || window.searchData;
  if (
    typeof searchData === 'undefined' ||
    !searchData.ajax_url ||
    !searchData.nonce ||
    !searchData.labels ||
    !searchData.all_categories ||
    !searchData.tags ||
    !searchData.villes ||
    !searchData.current_context_filters
  ) {
    // console.warn('KB Global Search Form: Essential searchData properties are missing. Module not initialized.');
    return;
  }

  // --- SÉLECTEURS JQUERY POUR LES ÉLÉMENTS DU FORMULAIRE GLOBAL ---
  const $form = $('#advanced-search-form-modal'); // L'ID du formulaire lui-même.
  const $categorySelect = $('#modal-search-category');
  const $subcategorySelect = $('#modal-search-subcategory');
  const $tagsContainer = $('#modal-search-tags-container');
  const $villeSelect = $('#modal-search-ville');
  const $keywordInput = $('#modal-search-keyword');
  const $keywordHiddenS = $('#modal_search_keyword_s_hidden_field');
  const $geolocCheckbox = $('#modal-search-geoloc');
  //const $geolocStatus = $('#modal-geoloc-status');
  const $userLatInput = $('#modal-user-lat');
  const $userLngInput = $('#modal-user-lng');
  const $resetButton = $('#modal-reset-search-button');

  // Les fonctions populateCategories() et populateTags() sont supprimées car PHP gère leur rendu initial.

  /**
   * Peuple le select des sous-catégories en fonction du parent sélectionné.
   * @param {string|number|null} parentId - L'ID de la catégorie parente.
   */
  function populateSubcategories(parentId) {
    if (!$subcategorySelect.length || !searchData.all_categories) return;
    $subcategorySelect.empty().append('<option value="">Sous thématiques ?</option>').prop('disabled', true);
    const parentIdStr = parentId ? parentId.toString() : null;
    if (
      parentIdStr &&
      searchData.all_categories[parentIdStr]?.children &&
      Object.keys(searchData.all_categories[parentIdStr].children).length > 0
    ) {
      const childrenObject = searchData.all_categories[parentIdStr].children;
      const sortedChildren = Object.entries(childrenObject).sort(([, nameA], [, nameB]) => nameA.localeCompare(nameB));
      sortedChildren.forEach(([childId, childName]) => {
        $subcategorySelect.append($('<option>', { value: childId, text: childName }));
      });
      $subcategorySelect.prop('disabled', false);
    }
  }

  /**
   * Peuple le select des villes.
   * @param {array|null} allowedVilleIds - Liste d'IDs de villes à afficher, ou null pour toutes.
   */
  function populateVilles(allowedVilleIds = null) {
    if (!$villeSelect.length || !searchData.villes || !Array.isArray(searchData.villes)) return;
    const currentVilleValue = $villeSelect.val();
    $villeSelect.empty().append('<option value="">Toutes les villes</option>');
    let villesToDisplay = searchData.villes; // searchData.villes est un tableau d'objets {id, name} trié par PHP.
    if (allowedVilleIds !== null && Array.isArray(allowedVilleIds)) {
      villesToDisplay = searchData.villes.filter((villeObj) => allowedVilleIds.includes(villeObj.id));
    }
    villesToDisplay.forEach((villeObj) => {
      $villeSelect.append($('<option>', { value: villeObj.id, text: villeObj.name }));
    });
    if ($villeSelect.find(`option[value="${currentVilleValue}"]`).length) $villeSelect.val(currentVilleValue);
    else $villeSelect.val('');
  }

  /**
   * Applique la logique conditionnelle à l'interface du formulaire global.
   */
  function applyConditionalLogic() {
    if (
      !$categorySelect.length ||
      !$subcategorySelect.length ||
      !$geolocCheckbox.length ||
      !$tagsContainer.length ||
      !$villeSelect.length
    )
      return;
    const selectedParentCategoryId = $categorySelect.val() ? parseInt($categorySelect.val(), 10) : null;
    const selectedSubcategoryId =
      $subcategorySelect.is(':enabled') && $subcategorySelect.val() ? parseInt($subcategorySelect.val(), 10) : null;
    const isGeolocActive = $geolocCheckbox.is(':checked');

    let isCatDisablingOthers = false;
    if (
      searchData.categories_disabling_others_ids?.includes(selectedParentCategoryId) ||
      searchData.categories_disabling_others_ids?.includes(selectedSubcategoryId)
    )
      isCatDisablingOthers = true;
    let isCatDisablingVillesOnly = false;
    if (
      !isCatDisablingOthers &&
      (searchData.categories_disabling_villes_only_ids?.includes(selectedParentCategoryId) ||
        searchData.categories_disabling_villes_only_ids?.includes(selectedSubcategoryId))
    )
      isCatDisablingVillesOnly = true;

    const disableAllTags = isCatDisablingOthers;
    const disableVillesOverall = isCatDisablingOthers || isGeolocActive || isCatDisablingVillesOnly;
    let showPyreneesTag = false;
    if (
      searchData.tags_pyrenees_trigger_categories?.includes(selectedParentCategoryId) ||
      searchData.tags_pyrenees_trigger_categories?.includes(selectedSubcategoryId)
    )
      showPyreneesTag = true;

    const $tagLandesRadio = $tagsContainer.find(`input[name="tag"][value="${searchData.id_tag_landes}"]`);
    const $tagPyreneesRadio = $tagsContainer.find(`input[name="tag"][value="${searchData.id_tag_pyrenees}"]`);

    if ($tagLandesRadio.length) $tagLandesRadio.parent().toggle(!showPyreneesTag); // && !disableAllTags
    if ($tagPyreneesRadio.length) $tagPyreneesRadio.parent().toggle(showPyreneesTag); // && !disableAllTags

    $tagsContainer.find('input[name="tag"]').each(function () {
      const $radio = $(this);
      const hidden = $radio.parent().css('display') === 'none';
      $radio.prop('disabled', disableAllTags || hidden);
      if ($radio.is(':checked') && $radio.is(':disabled')) {
        $radio.prop('checked', false);
      }
    });

    const $selectedTag = $tagsContainer.find('input[name="tag"]:checked:not(:disabled)');
    const tagSelected = $selectedTag.length > 0 && $selectedTag.parent().css('display') !== 'none';
    const pyreneesSelected = tagSelected && parseInt($selectedTag.val(), 10) === searchData.id_tag_pyrenees;
    $villeSelect.prop('disabled', disableVillesOverall || !tagSelected || pyreneesSelected);
    if ($villeSelect.is(':disabled')) $villeSelect.val('');

    const finalSelectedTagId = tagSelected ? $selectedTag.val() : null;
    if (finalSelectedTagId && !$villeSelect.is(':disabled'))
      populateVilles(searchData.tag_to_villes_map?.[finalSelectedTagId] || null);
    else if (!$villeSelect.is(':disabled')) populateVilles();
    else populateVilles([]);
  }

  /** Gère la géolocalisation pour le formulaire global. */
  function handleGeolocation() {
    if (!$geolocCheckbox.length) return;
    if ($geolocCheckbox.is(':checked')) {
      if (navigator.geolocation) {
        //$geolocStatus.text(searchData.labels.loading || 'Demande...');
        navigator.geolocation.getCurrentPosition(
          (p) => {
            $userLatInput.val(p.coords.latitude);
            $userLngInput.val(p.coords.longitude);
            //$geolocStatus.text(`Pos: ${p.coords.latitude.toFixed(2)},${p.coords.longitude.toFixed(2)}`);
            applyConditionalLogic();
          },
          (e) => {
            //$geolocStatus.text(searchData.labels.error_geolocation);
            $geolocCheckbox.prop('checked', false);
            $userLatInput.val('');
            $userLngInput.val('');
            applyConditionalLogic();
          }
        );
      } else {
        //$geolocStatus.text('Non supportée');
        $geolocCheckbox.prop('checked', false);
        applyConditionalLogic();
      }
    } else {
      $userLatInput.val('');
      $userLngInput.val('');
      //$geolocStatus.text('');
      applyConditionalLogic();
    }
  }

  /**
   * Pré-remplit les champs du formulaire global basé sur `searchData.current_context_filters`.
   */
  function prefillGlobalSearchFormFromContext() {
    if (!$form.length || !searchData.current_context_filters) return;
    const context = searchData.current_context_filters;

    if (context.keyword && $keywordInput.length) $keywordInput.val(context.keyword);

    if (context.category && $categorySelect.length) {
      if ($categorySelect.find(`option[value="${context.category}"]`).length) {
        $categorySelect.val(context.category.toString());
        // Important: Peupler les sous-catégories maintenant que le parent est sélectionné.
        populateSubcategories(context.category.toString());
      }
    } else {
      // Si pas de catégorie parente dans le contexte, s'assurer que les sous-cat sont vides/désactivées.
      populateSubcategories('');
    }

    // La logique conditionnelle doit s'exécuter pour mettre à jour la visibilité des tags
    // et l'état des villes AVANT de tenter de sélectionner tag/sous-cat/ville.
    applyConditionalLogic();

    // Tenter de cocher le tag du contexte, s'il est visible et non désactivé.
    if (context.tag && $tagsContainer.length) {
      const $tagRadio = $tagsContainer.find(`input[name="tag"][value="${context.tag}"]`);
      if ($tagRadio.length && !$tagRadio.is(':disabled') && $tagRadio.parent().css('display') !== 'none') {
        $tagRadio.prop('checked', true);
        // Un changement de tag affecte les villes, donc relancer la logique conditionnelle.
        applyConditionalLogic();
      }
    }

    // Tenter de sélectionner la sous-catégorie (après que populateSubcategories et applyConditionalLogic aient tourné).
    if (context.subcategory && $subcategorySelect.length && !$subcategorySelect.is(':disabled')) {
      if ($subcategorySelect.find(`option[value="${context.subcategory}"]`).length) {
        $subcategorySelect.val(context.subcategory.toString());
      }
    }

    // Tenter de sélectionner la ville (après que les options aient été peuplées par applyConditionalLogic).
    if (context.ville && $villeSelect.length && !$villeSelect.is(':disabled')) {
      if ($villeSelect.find(`option[value="${context.ville}"]`).length) {
        $villeSelect.val(context.ville.toString());
      }
    }

    // Gérer la géolocalisation du contexte.
    if (context.geoloc === true && $geolocCheckbox.length) {
      $geolocCheckbox.prop('checked', true);
      if (context.user_lat && $userLatInput.length) $userLatInput.val(context.user_lat);
      if (context.user_lng && $userLngInput.length) $userLngInput.val(context.user_lng);
      // Mettre à jour l'affichage du statut et réappliquer la logique (handleGeolocation le fait).
      handleGeolocation();
    } else {
      // S'assurer que la géoloc est bien décochée si non définie dans le contexte.
      if ($geolocCheckbox.is(':checked')) {
        $geolocCheckbox.prop('checked', false);
        handleGeolocation(); // Pour nettoyer l'état de la géoloc.
      } else {
        // Si la géoloc n'était pas active dans le contexte et pas cochée,
        // un dernier appel à applyConditionalLogic pour finaliser l'UI.
        applyConditionalLogic();
      }
    }
  }

  // --- Initialisation et Écouteurs pour le Formulaire Global ---
  if ($form.length) {
    // PHP rend les catégories parentes et les tags.
    // JS peuple les villes (initialement toutes, sera filtré/désactivé par applyConditionalLogic).
    populateVilles();

    // Tenter de pré-remplir le formulaire basé sur le contexte de la page actuelle.
    prefillGlobalSearchFormFromContext();

    // Si `prefillGlobalSearchFormFromContext` n'a appliqué aucun contexte significatif,
    // un appel à `applyConditionalLogic` a déjà été fait à la fin de `prefill...`
    // ou via `handleGeolocation` si la géoloc était dans le contexte.
    // Si aucun contexte du tout (`current_context_filters` vide/null), `prefill...`
    // se termine par un appel à `applyConditionalLogic` pour assurer l'état initial.

    // Écouteurs d'événements pour les interactions avec le formulaire.
    $categorySelect.on('change', function () {
      populateSubcategories($(this).val());
      applyConditionalLogic();
    });
    $subcategorySelect.on('change', applyConditionalLogic);
    $tagsContainer.on('change', 'input[name="tag"]', applyConditionalLogic); // Délégation pour radios.
    $geolocCheckbox.on('change', handleGeolocation);

    // Gérer la soumission du formulaire (redirige nativement vers search.php).
    $form.on('submit', function () {
      if ($keywordInput.length && $keywordHiddenS.length) {
        $keywordHiddenS.val($keywordInput.val());
      }
    });

    // Gérer le bouton Reset.
    $resetButton.on('click', function () {
      $form[0].reset();
      $categorySelect.val('');
      $userLatInput.val('');
      $userLngInput.val('');
      //$geolocStatus.text('');
      populateSubcategories(''); // Vider et désactiver.
      // Les radios de tag sont décochés par form.reset().
      // populateVilles() sera appelée par applyConditionalLogic.
      applyConditionalLogic(); // Réappliquer toutes les logiques d'UI.
    });
  }
  // console.log('Global Search Form (jQuery) initialized.');
} // Fin de initializeGlobalSearchForm
