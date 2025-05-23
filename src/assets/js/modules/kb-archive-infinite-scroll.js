// assets/js/modules/kb-archive-infinite-scroll.js

/**
 * Module jQuery pour l'infinite scroll sur les pages d'archives KindaBreak.
 * Il est le SEUL responsable de la manipulation de l'historique du navigateur.
 * Reçoit les filtres avec des clés internes (tag, ville) de 'kb-archive-filters.js'
 * et les traduit en clés préfixées (filter_tag) pour l'URL.
 * Les données AJAX sont envoyées avec les clés internes.
 */
export function initializeArchiveInfiniteScrollJQuery(searchDataConfig) {
  if (typeof jQuery === 'undefined') {
    // console.warn('[InfiniteScroll LOG] jQuery not available.');
    return;
  }
  const $ = jQuery;

  const searchData = searchDataConfig || window.searchData;
  if (!searchData || !searchData.ajax_url || !searchData.nonce || !searchData.labels || !searchData.archive_base_url) {
    /* console.warn(
      '[InfiniteScroll LOG] Essential searchData properties missing. Module not initialized. searchData:',
      searchData
    ); */
    return;
  }

  const $archiveResultsContainer = $('#kb_infinite_scroll');
  const $archiveLoader = $('#search-loader');
  const $archiveLoadMoreSentinel = $('#load-more-sentinel');

  const isArchiveContext =
    searchData.current_archive_post_type &&
    ((searchData.current_archive_term_id && searchData.current_archive_taxonomy) ||
      (!searchData.current_archive_term_id &&
        !searchData.current_archive_taxonomy &&
        searchData.current_archive_post_type !== 'post'));
  const isOnActualSearchPage =
    $('body').is('.search-results, .search') || new URLSearchParams(window.location.search).has('s');

  if (isArchiveContext && !isOnActualSearchPage && $archiveResultsContainer.length && $archiveLoadMoreSentinel.length) {
    // console.log(`[InfiniteScroll LOG] Initializing. CPT: ${searchData.current_archive_post_type}, Tax: ${searchData.current_archive_taxonomy}, Term: ${searchData.current_archive_term_id}, BaseURL: ${searchData.archive_base_url}`);

    const getPageFromPathname = (pathname) => (pathname.match(/\/page\/(\d+)\/?$/) ? parseInt(RegExp.$1, 10) : 1);
    let archiveCurrentPage = searchData.initial_page_number
      ? parseInt(searchData.initial_page_number, 10)
      : getPageFromPathname(window.location.pathname);
    if (archiveCurrentPage < 1) archiveCurrentPage = 1;
    // console.log(`[InfiniteScroll LOG] Initial archiveCurrentPage: ${archiveCurrentPage}`);

    let archiveMaxPages = parseInt($archiveResultsContainer.data('max-pages') || '1', 10);
    let archiveIsLoading = false;
    let currentArchiveXHR = null;
    let msnryArchiveInstance = null;

    let internalCurrentFilters = {};
    let initialUrlFiltersHaveBeenFetched = false;

    if ($archiveResultsContainer.hasClass('masonry-grid') && searchData.current_archive_post_type === 'kindashop') {
      if (typeof Masonry !== 'undefined')
        msnryArchiveInstance = new Masonry($archiveResultsContainer[0], {
          itemSelector: '.shop-item',
          percentPosition: true,
        });
      // else console.warn("[InfiniteScroll LOG] Masonry library not found for kindashop.");
    }

    const mapToUrlKeys = (filtersToMap) => {
      const mapped = {};
      if (filtersToMap.tag) mapped.filter_tag = filtersToMap.tag;
      if (filtersToMap.ville) mapped.filter_ville = filtersToMap.ville;
      if (filtersToMap.geoloc === 'true') {
        mapped.filter_geoloc = 'true';
        if (filtersToMap.user_lat) mapped.user_lat = filtersToMap.user_lat;
        if (filtersToMap.user_lng) mapped.user_lng = filtersToMap.user_lng;
      }
      if (filtersToMap.s) mapped.s = filtersToMap.s;
      // console.log('[InfiniteScroll LOG] mapToUrlKeys input:', filtersToMap, 'output:', mapped);
      return mapped;
    };

    const mapFromUrlKeys = (urlParamsObject) => {
      const internal = {};
      if (urlParamsObject.filter_tag) internal.tag = urlParamsObject.filter_tag;
      if (urlParamsObject.filter_ville) internal.ville = urlParamsObject.filter_ville;
      if (urlParamsObject.filter_geoloc === 'true') {
        internal.geoloc = 'true';
        if (urlParamsObject.user_lat) internal.user_lat = urlParamsObject.user_lat;
        if (urlParamsObject.user_lng) internal.user_lng = urlParamsObject.user_lng;
      } else {
        internal.geoloc = 'false';
      }
      if (urlParamsObject.s) internal.s = urlParamsObject.s;
      // console.log('[InfiniteScroll LOG] mapFromUrlKeys input:', urlParamsObject, 'output:', internal);
      return internal;
    };

    function updateBrowserHistory(page, currentInternalFiltersForState, replace = false) {
      // console.log(`[InfiniteScroll History LOG] updateBrowserHistory called. Page: ${page}, Filters (internal):`, JSON.parse(JSON.stringify(currentInternalFiltersForState)), `Replace: ${replace}`);
      let newUrl = searchData.archive_base_url;
      if (page > 1) newUrl = newUrl.endsWith('/') ? `${newUrl}page/${page}/` : `${newUrl}/page/${page}/`;
      else newUrl = newUrl.endsWith('/') ? newUrl : `${newUrl}/`;

      const filtersForUrl = mapToUrlKeys(currentInternalFiltersForState);
      const queryParams = new URLSearchParams();
      for (const key in filtersForUrl) {
        if (
          Object.prototype.hasOwnProperty.call(filtersForUrl, key) &&
          filtersForUrl[key] !== null &&
          filtersForUrl[key] !== ''
        ) {
          queryParams.append(key, filtersForUrl[key]);
        }
      }
      const queryString = queryParams.toString();
      if (queryString) newUrl += `?${queryString}`;

      const state = { page: page, filters: { ...currentInternalFiltersForState }, source: 'kbInfiniteScroll' };
      const currentFullUrl = window.location.href;
      const targetFullUrl = new URL(newUrl, window.location.origin).href;
      // console.log(`[InfiniteScroll History LOG] Attempting update. Current URL: ${currentFullUrl}, Target URL: ${targetFullUrl}, State to set:`, JSON.parse(JSON.stringify(state)));

      if (targetFullUrl !== currentFullUrl) {
        if (replace) {
          // console.log('[InfiniteScroll History LOG] Executing replaceState to:', targetFullUrl);
          history.replaceState(state, '', targetFullUrl);
        } else {
          // console.log('[InfiniteScroll History LOG] Executing pushState to:', targetFullUrl);
          history.pushState(state, '', targetFullUrl);
        }
      } else if (replace) {
        // console.log('[InfiniteScroll History LOG] Executing replaceState (URL same) to:', targetFullUrl);
        history.replaceState(state, '', targetFullUrl);
      }
      // console.log('[InfiniteScroll History LOG] Current history.state after update:', JSON.parse(JSON.stringify(history.state)));
    }

    function fetchArchiveData(options = {}) {
      const {
        isNewFilterSearch = false,
        newInternalFiltersReceived = {},
        isPopStateEvent = false,
        targetPage = null,
        forceReplaceStateForFilter = false,
      } = options;
      // console.log('[InfiniteScroll LOG] fetchArchiveData called. Options:', JSON.parse(JSON.stringify(options)), 'Current internalCurrentFilters BEOFRE update:', JSON.parse(JSON.stringify(internalCurrentFilters)));

      if (archiveIsLoading && !isPopStateEvent) {
        // console.log('[InfiniteScroll LOG] fetchArchiveData aborted: archiveIsLoading=true and not popstate.');
        return;
      }
      archiveIsLoading = true;
      $archiveLoader.show();
      $archiveResultsContainer.find('.no-more-posts-message').remove();
      if (currentArchiveXHR) {
        // console.log('[InfiniteScroll LOG] Aborting previous XHR.');
        currentArchiveXHR.abort();
      }

      if (isNewFilterSearch) {
        archiveCurrentPage = targetPage || 1;
        if (archiveCurrentPage < 1) archiveCurrentPage = 1;
        // console.log(`[InfiniteScroll LOG] New filter search. Page reset to: ${archiveCurrentPage}. Clearing container.`);
        $archiveResultsContainer.html('');
        if (msnryArchiveInstance) msnryArchiveInstance.layout();
        $archiveLoadMoreSentinel.show();
        internalCurrentFilters = { ...newInternalFiltersReceived };
        // console.log('[InfiniteScroll LOG] New filter search. internalCurrentFilters updated to:', JSON.parse(JSON.stringify(internalCurrentFilters)));
        $('html, body').animate({ scrollTop: 0 }, 300); // Scroll en haut pour UX
      } else if (targetPage && isPopStateEvent) {
        archiveCurrentPage = targetPage;
        if (archiveCurrentPage < 1) archiveCurrentPage = 1;
        internalCurrentFilters = { ...newInternalFiltersReceived };
        // console.log(`[InfiniteScroll LOG] Popstate event. Target page: ${archiveCurrentPage}. Clearing container. internalCurrentFilters set to:`, JSON.parse(JSON.stringify(internalCurrentFilters)));
        $archiveResultsContainer.html('');
        if (msnryArchiveInstance) msnryArchiveInstance.layout();
        $('html, body').animate({ scrollTop: 0 }, 300); // Scroll en haut aussi pour popstate
      }
      // else {
      //    console.log(`[InfiniteScroll LOG] Infinite scroll load for page ${archiveCurrentPage}.`);
      // }

      const ajaxData = {
        action: 'kb_load_more_archive_posts',
        nonce: searchData.nonce,
        page: archiveCurrentPage.toString(),
        archive_post_type: searchData.current_archive_post_type,
        base_term_id: searchData.current_archive_term_id || '',
        base_taxonomy: searchData.current_archive_taxonomy || '',
        ...internalCurrentFilters,
      };
      // console.log('[InfiniteScroll LOG] AJAX Data for fetch:', JSON.parse(JSON.stringify(ajaxData)));

      currentArchiveXHR = $.ajax({
        url: searchData.ajax_url,
        type: 'POST',
        data: ajaxData,
        dataType: 'json',
        success: function (data) {
          // console.log('[InfiniteScroll LOG] AJAX Success. Data received:', data);
          if (data.success) {
            if (data.data.html && $archiveResultsContainer.length) {
              const $newItems = $($.parseHTML(data.data.html.trim()));
              if ($newItems.length > 0) {
                $archiveResultsContainer.append($newItems);
                if (msnryArchiveInstance) {
                  msnryArchiveInstance.appended($newItems.get());
                  setTimeout(() => {
                    if (msnryArchiveInstance) msnryArchiveInstance.layout();
                  }, 150);
                }
              }
            }
            archiveMaxPages =
              data.data.max_pages !== undefined ? parseInt(data.data.max_pages, 10) : archiveCurrentPage;
            // console.log(`[InfiniteScroll LOG] archiveMaxPages updated to: ${archiveMaxPages}`);

            if (archiveCurrentPage >= archiveMaxPages || !data.data.html || data.data.html.trim() === '') {
              // console.log('[InfiniteScroll LOG] No more posts or end of pages. Hiding sentinel.');
              $archiveLoadMoreSentinel.hide();
              if (
                $archiveResultsContainer.html().trim() !== '' &&
                (data.data.html === '' || !data.data.html) &&
                !$archiveResultsContainer.find('.no-more-posts-message').length
              ) {
                $archiveResultsContainer.append(
                  `<p class="text-center text-muted py-3 no-more-posts-message">${searchData.labels.no_more_posts}</p>`
                );
              }
            } else {
              $archiveLoadMoreSentinel.show();
            }

            if (!isPopStateEvent) {
              const pageToUpdateWith = isNewFilterSearch ? 1 : archiveCurrentPage;
              const shouldUseReplace = forceReplaceStateForFilter && isNewFilterSearch;
              // console.log(`[InfiniteScroll LOG] Not a popstate. Updating browser history. Page: ${pageToUpdateWith}, ShouldReplace: ${shouldUseReplace}`);
              updateBrowserHistory(pageToUpdateWith, internalCurrentFilters, shouldUseReplace);
            }
          } else {
            // console.error('[InfiniteScroll LOG] API Error in success response:', data.data?.message || 'Unknown error');
            $archiveLoadMoreSentinel.hide();
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          if (textStatus !== 'abort') {
            // console.error(`[InfiniteScroll LOG] AJAX Error. Status: ${textStatus}, Error: ${errorThrown}`, jqXHR);
            $archiveLoadMoreSentinel.hide();
          }
          // else {
          //   console.log('[InfiniteScroll LOG] AJAX request aborted.');
          // }
        },
        complete: function () {
          archiveIsLoading = false;
          $archiveLoader.hide();
          currentArchiveXHR = null;
          // console.log('[InfiniteScroll LOG] AJAX Complete. archiveIsLoading=false.');
        },
      });
    }

    $(document).on('kb:archive:filters:changed', function (event, activeInternalFiltersFromEvent) {
      // console.log('[InfiniteScroll LOG] Event kb:archive:filters:changed received. Raw activeInternalFiltersFromEvent:', JSON.parse(JSON.stringify(activeInternalFiltersFromEvent)), 'initialUrlFiltersHaveBeenFetched was:', initialUrlFiltersHaveBeenFetched);

      let filtersToUseForFetch;
      const receivedFilters = activeInternalFiltersFromEvent || {}; // S'assurer que ce n'est pas undefined

      if (
        Object.keys(receivedFilters).length === 0 ||
        (Object.keys(receivedFilters).length === 1 &&
          receivedFilters.geoloc === 'false' &&
          !receivedFilters.tag &&
          !receivedFilters.ville)
      ) {
        filtersToUseForFetch = mapFromUrlKeys({});
        // console.log('[InfiniteScroll LOG] filters:changed with effectively empty, using default base filters:', JSON.parse(JSON.stringify(filtersToUseForFetch)));
      } else {
        filtersToUseForFetch = receivedFilters;
      }

      let forceReplace = false;
      if (!initialUrlFiltersHaveBeenFetched) {
        forceReplace = true;
        initialUrlFiltersHaveBeenFetched = true;
        // console.log('[InfiniteScroll LOG] This is the first filters:changed event. Setting forceReplace=true, initialUrlFiltersHaveBeenFetched=true.');
      }
      // else {
      //    console.log('[InfiniteScroll LOG] Subsequent filters:changed event. forceReplace=false.');
      // }

      fetchArchiveData({
        isNewFilterSearch: true,
        newInternalFiltersReceived: filtersToUseForFetch,
        forceReplaceStateForFilter: forceReplace,
      });
    });

    $(window).on('popstate', function (event) {
      const state = event.originalEvent.state;
      // console.log('[InfiniteScroll POPSTATE LOG] Event triggered. history.state:', JSON.parse(JSON.stringify(history.state)), 'event.originalEvent.state:', JSON.parse(JSON.stringify(state)), 'Current URL:', window.location.href);

      if (state && state.source === 'kbInfiniteScroll') {
        // console.log('[InfiniteScroll POPSTATE LOG] Handling our state. Filters (internal keys):', JSON.parse(JSON.stringify(state.filters)));
        internalCurrentFilters = state.filters || mapFromUrlKeys({});

        // console.log('[InfiniteScroll POPSTATE LOG] Triggering kb:archive:filters:update_ui_from_state with filters:', JSON.parse(JSON.stringify(internalCurrentFilters)));
        $(document).trigger('kb:archive:filters:update_ui_from_state', [internalCurrentFilters]);

        fetchArchiveData({
          targetPage: state.page,
          newInternalFiltersReceived: internalCurrentFilters,
          isPopStateEvent: true,
          isNewFilterSearch: true,
        });
      } else if (!state) {
        // console.warn('[InfiniteScroll POPSTATE LOG] State is null. This can happen. Attempting to reload from current URL.');
        const pageFromUrl = getPageFromPathname(window.location.pathname);
        const filtersFromUrlParams = {};
        new URLSearchParams(window.location.search).forEach((value, key) => {
          filtersFromUrlParams[key] = value;
        });

        internalCurrentFilters = mapFromUrlKeys(filtersFromUrlParams);
        // console.log('[InfiniteScroll POPSTATE LOG] State was null. Parsed internal filters from URL:', JSON.parse(JSON.stringify(internalCurrentFilters)));
        // console.log('[InfiniteScroll POPSTATE LOG] Triggering kb:archive:filters:update_ui_from_state with filters from URL:', JSON.parse(JSON.stringify(internalCurrentFilters)));
        $(document).trigger('kb:archive:filters:update_ui_from_state', [internalCurrentFilters]);
        fetchArchiveData({
          targetPage: pageFromUrl,
          newInternalFiltersReceived: internalCurrentFilters,
          isPopStateEvent: true,
          isNewFilterSearch: true,
        });
      }
      // else {
      //   console.warn('[InfiniteScroll POPSTATE LOG] State is not ours or unexpected, ignoring. State:', JSON.parse(JSON.stringify(state)));
      // }
    });

    // --- IntersectionObserver ---
    if ($archiveLoadMoreSentinel.length && archiveCurrentPage < archiveMaxPages) {
      // console.log('[InfiniteScroll LOG] Setting up IntersectionObserver.');
      const archiveObserver = new IntersectionObserver(
        (entries) => {
          if (entries[0].isIntersecting && !archiveIsLoading && archiveCurrentPage < archiveMaxPages) {
            archiveCurrentPage++;
            // console.log(`[InfiniteScroll LOG] Sentinel intersected by Observer. Loading page ${archiveCurrentPage}`);
            fetchArchiveData({ isPopStateEvent: false });
          }
        },
        { threshold: 0.8 }
      );
      archiveObserver.observe($archiveLoadMoreSentinel[0]);
    } else {
      // console.log('[InfiniteScroll LOG] Conditions for IntersectionObserver not met or no more pages at init.');
      $archiveLoadMoreSentinel.hide();
      if (
        archiveCurrentPage >= archiveMaxPages &&
        $archiveResultsContainer.children().length > 0 &&
        !$archiveResultsContainer.find('.no-more-posts-message').length
      ) {
        $archiveResultsContainer.append(
          `<p class="text-center text-muted py-3 no-more-posts-message">${searchData.labels.no_more_posts}</p>`
        );
      }
    }

    // --- Établir l'état initial de l'historique ---
    const initialUrlParamsForState = {};
    if (window.location.search) {
      new URLSearchParams(window.location.search).forEach((value, key) => {
        initialUrlParamsForState[key] = value;
      });
    }
    internalCurrentFilters = mapFromUrlKeys(initialUrlParamsForState);
    // console.log(`[InfiniteScroll LOG] At page load, initial internalCurrentFilters set to (from URL):`, JSON.parse(JSON.stringify(internalCurrentFilters)));
    // console.log(`[InfiniteScroll LOG] Establishing initial history state for page ${archiveCurrentPage}. Initial internalCurrentFilters:`, JSON.parse(JSON.stringify(internalCurrentFilters)), 'Current history.state BEFORE:', JSON.parse(JSON.stringify(history.state)));

    setTimeout(() => {
      // console.log(`[InfiniteScroll LOG] Deferred execution: Establishing initial history state for page ${archiveCurrentPage}. Using current internalCurrentFilters:`, JSON.parse(JSON.stringify(internalCurrentFilters)));
      updateBrowserHistory(archiveCurrentPage, internalCurrentFilters, true);
    }, 0);
    // console.log('[InfiniteScroll LOG] Initialization complete.');
  }
  // else {
  //    console.warn('[InfiniteScroll LOG] Conditions not met for initialization (isArchiveContext, !isOnActualSearchPage, containers).');
  // }
}
