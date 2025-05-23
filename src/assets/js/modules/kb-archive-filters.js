// assets/js/modules/kb-archive-filters.js

/**
 * Module jQuery pour la gestion des filtres sur les pages d'archives KindaBreak.
 * Utilise les clés de filtre 'tag', 'ville', 'geoloc' en interne.
 * Déclenche 'kb:archive:filters:changed' avec ces clés (uniquement si filtres actifs).
 * NE MANIPULE PAS L'HISTORIQUE DU NAVIGATEUR.
 */
export function initializeArchiveFiltersJQuery(searchDataConfig) {
  if (typeof jQuery === 'undefined') {
    // console.warn('[Filters JS] jQuery not available.');
    return;
  }
  const $ = jQuery;
  const searchData = searchDataConfig || window.searchData;
  if (
    typeof searchData === 'undefined' ||
    !searchData.ajax_url ||
    !searchData.nonce ||
    !searchData.labels ||
    !searchData.tags ||
    !searchData.villes ||
    !searchData.tag_to_villes_map
  ) {
    // console.warn('[Filters JS] Essential searchData properties missing.');
    return;
  }

  const $archiveFiltersWrapper = $('#archive-filters-wrapper');
  if (!$archiveFiltersWrapper.length) return;

  const $archiveFiltersForm = $('#archive-filters-form');
  const $archiveTagRadiosContainer = $('#archive-filter-tags');
  const $archiveVilleSelect = $('#archive-filter-ville');
  const $geolocRadio = $('#archive-filter-geoloc');
  const $archiveUserLat = $('#archive-user-lat');
  const $archiveUserLng = $('#archive-user-lng');
  const $archiveResetButton = $('#archive-reset-filters-button');
  const $archiveDescriptionsWrapper = $('#archive-descriptions-wrapper');

  function populateArchiveFilterVilles(allowedVilleIds = null) {
    if (!$archiveVilleSelect.length || !searchData.villes || !Array.isArray(searchData.villes)) return;
    const currentVal = $archiveVilleSelect.val();
    $archiveVilleSelect.empty().append('<option value="">Toutes les villes</option>');
    let villes = searchData.villes;
    if (allowedVilleIds !== null && Array.isArray(allowedVilleIds)) {
      villes = searchData.villes.filter((v) => allowedVilleIds && allowedVilleIds.includes(v.id));
    }
    villes.forEach((v) => {
      $archiveVilleSelect.append($('<option>', { value: v.id, text: v.name }));
    });
    if ($archiveVilleSelect.find(`option[value="${currentVal}"]`).length) {
      $archiveVilleSelect.val(currentVal);
    } else {
      $archiveVilleSelect.val('');
    }
  }

  function applyArchiveFiltersConditionalLogic() {
    if (!$archiveTagRadiosContainer.length || !$archiveVilleSelect.length || !$geolocRadio.length) return;
    const isGeolocRadioSelected = $geolocRadio.is(':checked');
    const archiveDisablesAllFilterOptions = searchData.archive_disables_tags_villes === true;
    const archiveDisablesOnlyVillesFlag = searchData.archive_disables_villes_only === true;
    const showPyreneesTagOnThisArchive = searchData.archive_should_show_pyrenees === true;
    const $tagLandesRadio = $archiveTagRadiosContainer.find(
      `input[name="filter_tag"][value="${searchData.id_tag_landes}"]`
    );
    const $tagPyreneesRadio = $archiveTagRadiosContainer.find(
      `input[name="filter_tag"][value="${searchData.id_tag_pyrenees}"]`
    );

    if ($tagLandesRadio.length)
      $tagLandesRadio.parent().toggle(!showPyreneesTagOnThisArchive && !archiveDisablesAllFilterOptions);
    if ($tagPyreneesRadio.length)
      $tagPyreneesRadio.parent().toggle(showPyreneesTagOnThisArchive && !archiveDisablesAllFilterOptions);

    $archiveTagRadiosContainer.find('input[name="filter_tag"]').each(function () {
      const $radio = $(this);
      let isThisRadioDisabledByArchive = false;
      if (archiveDisablesAllFilterOptions) isThisRadioDisabledByArchive = true;
      else if (
        ($radio[0] === $tagLandesRadio[0] || $radio[0] === $tagPyreneesRadio[0]) &&
        $radio.parent().css('display') === 'none'
      )
        isThisRadioDisabledByArchive = true;
      if ($radio[0] !== $geolocRadio[0]) $radio.prop('disabled', isThisRadioDisabledByArchive);
      if ($radio.is(':checked') && $radio.is(':disabled')) $radio.prop('checked', false);
    });

    const $selectedRadio = $archiveTagRadiosContainer.find('input[name="filter_tag"]:checked:not(:disabled)');
    const isAnyRadioEffectivelySelected = $selectedRadio.length > 0;
    const selectedRadioValue = isAnyRadioEffectivelySelected ? $selectedRadio.val() : null;
    const isActualTagRegionSelected = isAnyRadioEffectivelySelected && $selectedRadio[0] !== $geolocRadio[0];
    const isPyreneesTagRegionSelected =
      isActualTagRegionSelected && parseInt(selectedRadioValue, 10) === searchData.id_tag_pyrenees;
    const finalDisableVilles =
      archiveDisablesAllFilterOptions ||
      archiveDisablesOnlyVillesFlag ||
      isGeolocRadioSelected ||
      !isAnyRadioEffectivelySelected ||
      isPyreneesTagRegionSelected;

    $archiveVilleSelect.prop('disabled', finalDisableVilles);
    if (finalDisableVilles) $archiveVilleSelect.val('');
    if (archiveDisablesAllFilterOptions || archiveDisablesOnlyVillesFlag)
      $archiveVilleSelect.parent().css('display', 'none');

    if (isActualTagRegionSelected && !finalDisableVilles) {
      populateArchiveFilterVilles(searchData.tag_to_villes_map?.[selectedRadioValue] || null);
    } else if (!isActualTagRegionSelected && !finalDisableVilles && !isGeolocRadioSelected) {
      populateArchiveFilterVilles(); // Toutes les villes si aucun tag région et pas géoloc active
    } else {
      // Géoloc active OU villes désactivées
      populateArchiveFilterVilles([]); // Vider la liste
    }

    if ($archiveDescriptionsWrapper.length && searchData.current_archive_term_id) {
      let activeDescriptionId = `desc-default-${searchData.current_archive_term_id}`;
      if (isActualTagRegionSelected && !isGeolocRadioSelected) activeDescriptionId = `desc-tag-${selectedRadioValue}`;
      $archiveDescriptionsWrapper.find('.archive-description-block').hide();
      const $activeDesc = $archiveDescriptionsWrapper.find(`#${activeDescriptionId}`);
      if ($activeDesc.length && $activeDesc.html().trim() !== '') $activeDesc.show();
      else $archiveDescriptionsWrapper.find(`#desc-default-${searchData.current_archive_term_id}`).show();
    }
    if ($archiveResetButton.length) {
      const showReset =
        isAnyRadioEffectivelySelected || ($archiveVilleSelect.val() && !$archiveVilleSelect.is(':disabled'));
      $archiveResetButton.parent().toggle(showReset);
      $archiveResetButton.prop('disabled', !showReset);
    }
  }

  async function handleArchiveGeolocation() {
    if (!$geolocRadio.length) return Promise.resolve(false);
    const isGeolocRadioNowChecked = $geolocRadio.is(':checked');
    if (isGeolocRadioNowChecked) {
      if (navigator.geolocation) {
        return new Promise((resolve) => {
          navigator.geolocation.getCurrentPosition(
            (p) => {
              $archiveUserLat.val(p.coords.latitude);
              $archiveUserLng.val(p.coords.longitude);
              resolve(true);
            },
            (e) => {
              $geolocRadio.prop('checked', false);
              $archiveUserLat.val('');
              $archiveUserLng.val('');
              resolve(false);
            }
          );
        });
      } else {
        $geolocRadio.prop('checked', false);
        return Promise.resolve(false);
      }
    } else {
      $archiveUserLat.val('');
      $archiveUserLng.val('');
      return Promise.resolve(false);
    }
  }

  function getActiveArchiveFilters() {
    const filters = {}; // Commence vide, on ajoute seulement les filtres actifs
    const $selectedRadio = $archiveTagRadiosContainer.find('input[name="filter_tag"]:checked:not(:disabled)');

    if ($selectedRadio.length) {
      if ($selectedRadio[0] === $geolocRadio[0]) {
        if ($archiveUserLat.val() && $archiveUserLng.val()) {
          filters.geoloc = 'true';
          filters.user_lat = $archiveUserLat.val();
          filters.user_lng = $archiveUserLng.val();
        }
        // Si géoloc est coché mais pas de coords, 'filters' reste vide pour 'geoloc', ce qui est OK.
        // 'tag' ne sera pas défini.
      } else {
        filters.tag = $selectedRadio.val();
        // 'geoloc' ne sera pas défini.
      }
    }

    const villeVal = $archiveVilleSelect.val();
    if (villeVal && !$archiveVilleSelect.is(':disabled')) {
      filters.ville = villeVal;
    }
    return filters; // Retourne un objet avec seulement les filtres activement sélectionnés par l'utilisateur
  }

  function triggerFilterChange() {
    const activeFilters = getActiveArchiveFilters();
    // console.log('[Filters JS] Triggering kb:archive:filters:changed with:', JSON.parse(JSON.stringify(activeFilters)));
    $(document).trigger('kb:archive:filters:changed', [activeFilters]);
  }

  // --- Initialisation ---
  populateArchiveFilterVilles();

  const initialUrlParams = new URLSearchParams(window.location.search);

  if (initialUrlParams.has('filter_geoloc') && initialUrlParams.get('filter_geoloc') === 'true') {
    if ($geolocRadio.length) $geolocRadio.prop('checked', true);
    if (initialUrlParams.has('user_lat')) $archiveUserLat.val(initialUrlParams.get('user_lat'));
    if (initialUrlParams.has('user_lng')) $archiveUserLng.val(initialUrlParams.get('user_lng'));
  } else if (initialUrlParams.has('filter_tag')) {
    const tagValFromUrl = initialUrlParams.get('filter_tag');
    if ($archiveTagRadiosContainer.length) {
      $archiveTagRadiosContainer.find(`input[name="filter_tag"][value="${tagValFromUrl}"]`).prop('checked', true);
    }
  }

  applyArchiveFiltersConditionalLogic(); // Appliquer après avoir coché les radios pour que la logique des villes soit correcte

  if (initialUrlParams.has('filter_ville') && !$archiveVilleSelect.is(':disabled')) {
    const villeValFromUrl = initialUrlParams.get('filter_ville');
    if ($archiveVilleSelect.find(`option[value="${villeValFromUrl}"]`).length) {
      $archiveVilleSelect.val(villeValFromUrl);
    }
  }

  // Déclencher l'événement initial après que l'UI a été mise à jour à partir de l'URL.
  const initialActiveFilters = getActiveArchiveFilters();
  // console.log('[Filters JS] Triggering initial kb:archive:filters:changed (from URL parsing):', JSON.parse(JSON.stringify(initialActiveFilters)));
  $(document).trigger('kb:archive:filters:changed', [initialActiveFilters]);

  // --- Écouteurs d'événements ---
  $archiveTagRadiosContainer.on('change', 'input[name="filter_tag"]', async function () {
    const $changedRadio = $(this);
    if ($changedRadio[0] === $geolocRadio[0] && $changedRadio.is(':checked')) {
      await handleArchiveGeolocation();
    } else if ($changedRadio[0] !== $geolocRadio[0]) {
      if ($geolocRadio.is(':checked')) {
        $geolocRadio.prop('checked', false);
        await handleArchiveGeolocation(); // Nettoyer lat/lng
      }
    }
    applyArchiveFiltersConditionalLogic();
    triggerFilterChange();
  });

  $archiveVilleSelect.on('change', () => {
    triggerFilterChange();
  });

  $archiveResetButton.on('click', () => {
    if ($archiveFiltersForm.length) $archiveFiltersForm[0].reset();
    $archiveUserLat.val('');
    $archiveUserLng.val('');
    applyArchiveFiltersConditionalLogic();
    triggerFilterChange();
  });

  /**
   * Met à jour l'interface utilisateur des filtres en fonction d'un objet de filtres donné.
   * @param {object} filtersToApply - Objet avec les filtres à appliquer (clés internes: tag, ville, geoloc).
   */
  function updateFiltersUI(filtersToApply) {
    // console.log('[Filters JS] updateFiltersUI called with:', JSON.parse(JSON.stringify(filtersToApply)));
    if ($archiveFiltersForm.length) $archiveFiltersForm[0].reset();
    $archiveUserLat.val('');
    $archiveUserLng.val('');

    if (filtersToApply.geoloc === 'true') {
      $geolocRadio.prop('checked', true);
      if (filtersToApply.user_lat) $archiveUserLat.val(filtersToApply.user_lat);
      if (filtersToApply.user_lng) $archiveUserLng.val(filtersToApply.user_lng);
    } else if (filtersToApply.tag) {
      $archiveTagRadiosContainer.find(`input[name="filter_tag"][value="${filtersToApply.tag}"]`).prop('checked', true);
    }
    // Si ni geoloc ni tag, les radios seront décochés par form.reset()

    applyArchiveFiltersConditionalLogic(); // Essentiel pour réappliquer la logique et peupler les villes

    if (filtersToApply.ville && !$archiveVilleSelect.is(':disabled')) {
      // Vérifier si l'option ville existe toujours après la mise à jour des options par applyArchiveFiltersConditionalLogic
      if ($archiveVilleSelect.find(`option[value="${filtersToApply.ville}"]`).length) {
        $archiveVilleSelect.val(filtersToApply.ville);
      }
    }
    // Une dernière fois pour s'assurer que tout est correct (ex: visibilité bouton reset)
    applyArchiveFiltersConditionalLogic();
  }

  $(document).on('kb:archive:filters:update_ui_from_state', function (event, filtersFromState) {
    // console.log('[Filters JS] Event kb:archive:filters:update_ui_from_state received:', JSON.parse(JSON.stringify(filtersFromState)));
    updateFiltersUI(filtersFromState || {}); // Passer un objet vide si filtersFromState est null/undefined
  });

  // console.log('[Filters JS] Initialized.');
}
