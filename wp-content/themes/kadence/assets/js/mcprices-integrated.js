(function () {
  function initMcPricesInteractions() {
    var root = document.querySelector(".mcprices-page");
    var pageBody = document.body;

    if (!root) {
      return;
    }

    var tabs = Array.prototype.slice.call(
      root.querySelectorAll(".menu-tab[data-menu-filter]")
    );
    var menuSections = Array.prototype.slice.call(
      root.querySelectorAll(".menu-section[data-menu-category]")
    );
    var whatsNewSection = root.querySelector(".whats-new[data-menu-category]");
    var searchForms = Array.prototype.slice.call(
      document.querySelectorAll("[data-mcprices-search]")
    );
    var searchFeedbackNodes = Array.prototype.slice.call(
      document.querySelectorAll("[data-mcprices-search-feedback]")
    );
    var mobileSearchToggle = document.querySelector(
      "[data-mcprices-mobile-search-toggle]"
    );
    var mobileSearchShell = document.querySelector(
      "[data-mcprices-mobile-search-shell]"
    );
    var mobileSearchPanel = document.querySelector(
      "[data-mcprices-mobile-search-panel]"
    );
    var mobileSearchInput = document.querySelector(
      "[data-mcprices-mobile-search-input]"
    );
    var mobileQuickNavLinks = Array.prototype.slice.call(
      document.querySelectorAll(".mcprices-mobile-quick-nav__link")
    );
    var searchInputs = [];
    var searchSelectors = [
      ".td-name",
      ".card-name",
      ".cat-name",
      ".item-name",
      ".link-card-title",
      ".faq-q",
      ".new-item-name",
      ".menu-section-title",
    ];
    var mediaManifest = window.mcpricesMediaManifest || {};
    var mediaItems = mediaManifest.items || {};
    var mediaCategories = mediaManifest.categories || {};
    var mediaBaseUrl = window.mcpricesMediaBaseUrl || "";

    function getSearchInput(form) {
      if (!form) {
        return null;
      }

      return (
        form.querySelector("[data-mcprices-search-input]") ||
        form.querySelector('input[type="search"]')
      );
    }

    searchForms.forEach(function (form) {
      var input = getSearchInput(form);
      if (input) {
        searchInputs.push(input);
      }
    });

    function normalizeMediaKey(value) {
      var normalized = value || "";

      if (normalized.normalize) {
        normalized = normalized.normalize("NFKD");
      }

      normalized = normalized
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .replace(/&/g, " and ")
        .replace(/[®™]/g, "")
        .replace(/[’']/g, "")
        .replace(/\b(limited time only|here to stay)\b/g, " ")
        .replace(/[^a-z0-9]+/g, " ")
        .replace(/\btm\b/g, " ")
        .replace(/\s+/g, " ")
        .trim();

      return normalized;
    }

    function getMediaUrl(relativePath) {
      if (!relativePath) {
        return "";
      }

      if (/^https?:\/\//i.test(relativePath)) {
        return relativePath;
      }

      return mediaBaseUrl + relativePath.replace(/^\/+/, "");
    }

    function createMediaImage(url, className, altText) {
      var image = document.createElement("img");
      image.className = className;
      image.src = url;
      image.alt = altText || "";
      image.loading = "lazy";
      image.decoding = "async";
      return image;
    }

    function clearTextNodes(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (childNode) {
        if (childNode.nodeType === 3) {
          childNode.textContent = "";
        }
      });
    }

    function getCategoryMedia(sectionId) {
      return getMediaUrl(mediaCategories[sectionId] || "");
    }

    function getItemMedia(itemName) {
      if (!itemName) {
        return "";
      }

      var normalized = normalizeMediaKey(itemName);
      var variants = [normalized];
      var strippedParens = normalizeMediaKey(itemName.replace(/\([^)]*\)/g, " "));
      var noMcdonalds = normalized.replace(/^mcdonalds /, "");
      var noSize = normalized.replace(
        /\b(regular|mini|small|medium|large|selected|restaurants)\b/g,
        " "
      );
      var friesMatch = normalized.match(/^mcdonalds fries (small|medium|large)$/);

      if (strippedParens && variants.indexOf(strippedParens) === -1) {
        variants.push(strippedParens);
      }

      if (noMcdonalds && variants.indexOf(noMcdonalds) === -1) {
        variants.push(noMcdonalds);
      }

      noSize = normalizeMediaKey(noSize);
      if (noSize && variants.indexOf(noSize) === -1) {
        variants.push(noSize);
      }

      if (friesMatch) {
        ["fries " + friesMatch[1], friesMatch[1] + " fries"].forEach(function (
          variant
        ) {
          variant = normalizeMediaKey(variant);
          if (variant && variants.indexOf(variant) === -1) {
            variants.push(variant);
          }
        });
      }

      for (var index = 0; index < variants.length; index += 1) {
        if (mediaItems[variants[index]]) {
          return getMediaUrl(mediaItems[variants[index]]);
        }
      }

      return "";
    }

    function applyCategoryMedia() {
      root.querySelectorAll(".cat-card").forEach(function (card) {
        var icon = card.querySelector(".cat-emoji");
        var nameNode = card.querySelector(".cat-name");
        var sectionId = (card.getAttribute("href") || "").replace(/^#/, "");
        var mediaUrl = getCategoryMedia(sectionId);

        if (!icon || !mediaUrl || icon.querySelector(".mcprices-media-icon")) {
          return;
        }

        icon.textContent = "";
        icon.appendChild(
          createMediaImage(
            mediaUrl,
            "mcprices-media-icon mcprices-media-icon--category",
            nameNode ? nameNode.textContent.trim() : sectionId
          )
        );
      });

      root.querySelectorAll(".menu-section").forEach(function (section) {
        var icon = section.querySelector(".menu-section-icon");
        var titleNode = section.querySelector(".menu-section-title");
        var mediaUrl = getCategoryMedia(section.id);

        if (!icon || !mediaUrl || icon.querySelector(".mcprices-media-icon")) {
          return;
        }

        icon.textContent = "";
        icon.appendChild(
          createMediaImage(
            mediaUrl,
            "mcprices-media-icon mcprices-media-icon--section",
            titleNode ? titleNode.textContent.trim() : section.id
          )
        );
      });
    }

    function applyFeaturedItemMedia() {
      root.querySelectorAll(".featured-item").forEach(function (item) {
        var icon = item.querySelector(".item-emoji");
        var nameNode = item.querySelector(".item-name");
        var mediaUrl = getItemMedia(nameNode ? nameNode.textContent : "");

        if (!icon || !mediaUrl || icon.querySelector(".mcprices-media-icon")) {
          return;
        }

        icon.textContent = "";
        icon.appendChild(
          createMediaImage(
            mediaUrl,
            "mcprices-media-icon mcprices-media-icon--item",
            nameNode ? nameNode.textContent.trim() : ""
          )
        );
      });
    }

    function applyNewItemMedia() {
      root.querySelectorAll(".new-item-card").forEach(function (card) {
        var icon = card.querySelector(".new-item-emoji");
        var nameNode = card.querySelector(".new-item-name");
        var mediaUrl = getItemMedia(nameNode ? nameNode.textContent : "");

        if (!icon || !mediaUrl || icon.querySelector(".mcprices-media-icon")) {
          return;
        }

        icon.textContent = "";
        icon.appendChild(
          createMediaImage(
            mediaUrl,
            "mcprices-media-icon mcprices-media-icon--new",
            nameNode ? nameNode.textContent.trim() : ""
          )
        );
      });
    }

    function applyMenuCardMedia() {
      root.querySelectorAll(".menu-card").forEach(function (card) {
        var imageShell = card.querySelector(".card-img");
        var nameNode = card.querySelector(".card-name");
        var mediaUrl = getItemMedia(nameNode ? nameNode.textContent : "");

        if (
          !imageShell ||
          !mediaUrl ||
          imageShell.querySelector(".mcprices-card-media")
        ) {
          return;
        }

        clearTextNodes(imageShell);
        imageShell.appendChild(
          createMediaImage(
            mediaUrl,
            "mcprices-card-media",
            nameNode ? nameNode.textContent.trim() : ""
          )
        );
      });
    }

    function applyTableItemMedia() {
      root.querySelectorAll(".menu-table .td-name").forEach(function (nameNode) {
        var itemName = nameNode.textContent.trim();
        var mediaUrl = getItemMedia(itemName);
        var thumb;

        if (!itemName || !mediaUrl || nameNode.querySelector(".mcprices-inline-media")) {
          return;
        }

        var label = document.createElement("span");
        label.className = "mcprices-inline-media__label";
        label.textContent = itemName;
        label.style.display = "inline";
        label.style.lineHeight = "1.3";
        label.style.verticalAlign = "middle";

        var wrapper = document.createElement("span");
        wrapper.className = "mcprices-inline-media";
        wrapper.style.display = "inline-flex";
        wrapper.style.alignItems = "center";
        wrapper.style.gap = "8px";
        wrapper.style.verticalAlign = "middle";

        thumb = createMediaImage(
          mediaUrl,
          "mcprices-inline-media__thumb",
          itemName
        );
        thumb.style.width = "18px";
        thumb.style.height = "18px";
        thumb.style.minWidth = "18px";
        thumb.style.maxWidth = "18px";
        thumb.style.maxHeight = "18px";
        thumb.style.flex = "0 0 18px";
        thumb.style.objectFit = "contain";
        thumb.style.borderRadius = "0";
        thumb.style.background = "transparent";
        thumb.style.boxShadow = "none";
        thumb.style.verticalAlign = "middle";
        wrapper.appendChild(thumb);
        wrapper.appendChild(label);

        nameNode.textContent = "";
        nameNode.appendChild(wrapper);
      });
    }

    function applyOfficialMedia() {
      if (!mediaBaseUrl || !Object.keys(mediaCategories).length) {
        return;
      }

      applyCategoryMedia();
      applyFeaturedItemMedia();
      applyNewItemMedia();
      applyMenuCardMedia();
      applyTableItemMedia();
    }

    function isMobileViewport() {
      return window.innerWidth <= 1024;
    }

    function normalizePath(path) {
      if (!path) {
        return "/";
      }

      var normalized = path.replace(/\/+$/, "");
      return normalized || "/";
    }

    function getCurrentHeaderOffset() {
      var masthead = document.querySelector("#masthead");

      if (masthead) {
        var mastheadRect = masthead.getBoundingClientRect();
        var mastheadStyles = window.getComputedStyle(masthead);

        if (
          mastheadRect.height > 0 &&
          (mastheadStyles.position === "fixed" ||
            mastheadStyles.position === "sticky")
        ) {
          return mastheadRect.height + 12;
        }
      }

      return 16;
    }

    function setMobileQuickNavActive(activeLink) {
      mobileQuickNavLinks.forEach(function (link) {
        var isActive = !!activeLink && link === activeLink;
        link.classList.toggle("is-active", isActive);
        link.setAttribute("aria-current", isActive ? "page" : "false");
      });
    }

    function updateMobileQuickNavState() {
      if (!mobileQuickNavLinks.length) {
        return;
      }

      var currentUrl = new URL(window.location.href);
      var currentPath = normalizePath(currentUrl.pathname);
      var currentHash = currentUrl.hash;
      var activeLink = null;
      var bestMatchOffset = -1;

      if (currentHash) {
        activeLink =
          mobileQuickNavLinks.find(function (link) {
            var linkUrl = new URL(link.href, window.location.origin);
            return (
              normalizePath(linkUrl.pathname) === currentPath &&
              linkUrl.hash === currentHash
            );
          }) || null;
      }

      if (!activeLink) {
        var markerLine = window.scrollY + getCurrentHeaderOffset() + 48;
        var matchingLinks = mobileQuickNavLinks.filter(function (link) {
          var linkUrl = new URL(link.href, window.location.origin);
          return (
            normalizePath(linkUrl.pathname) === currentPath &&
            !!linkUrl.hash &&
            !!document.querySelector(linkUrl.hash)
          );
        });

        matchingLinks.forEach(function (link) {
          var target = document.querySelector(
            new URL(link.href, window.location.origin).hash
          );
          if (!target) {
            return;
          }

          if (target.offsetTop <= markerLine && target.offsetTop >= bestMatchOffset) {
            bestMatchOffset = target.offsetTop;
            activeLink = link;
          }
        });
      }

      if (!activeLink) {
        activeLink =
          mobileQuickNavLinks.find(function (link) {
            var linkUrl = new URL(link.href, window.location.origin);
            return (
              normalizePath(linkUrl.pathname) === currentPath && !linkUrl.hash
            );
          }) || null;
      }

      if (!activeLink) {
        activeLink =
          mobileQuickNavLinks.find(function (link) {
            var linkUrl = new URL(link.href, window.location.origin);
            return (
              normalizePath(linkUrl.pathname) === currentPath &&
              (!currentHash || linkUrl.hash === currentHash)
            );
          }) || null;
      }

      setMobileQuickNavActive(activeLink);
    }

    function updateMobileHeaderOffset() {
      if (!pageBody) {
        return;
      }

      var mobileHeader =
        document.querySelector(
          "#mobile-header .site-main-header-wrap.item-is-fixed.item-is-stuck"
        ) ||
        document.querySelector("#mobile-header .site-main-header-wrap");

      if (!mobileHeader) {
        return;
      }

      pageBody.style.setProperty(
        "--mcprices-mobile-header-offset",
        Math.max(Math.ceil(mobileHeader.getBoundingClientRect().bottom + 10), 82) +
          "px"
      );
    }

    function setMobileSearchOpen(isOpen) {
      if (!pageBody || !mobileSearchToggle || !mobileSearchPanel) {
        return;
      }

      updateMobileHeaderOffset();

      var nextState = !!isOpen && isMobileViewport();

      pageBody.classList.toggle("mcprices-mobile-search-open", nextState);
      mobileSearchToggle.setAttribute(
        "aria-expanded",
        nextState ? "true" : "false"
      );

      if (mobileSearchShell) {
        mobileSearchShell.setAttribute(
          "aria-hidden",
          nextState ? "false" : "true"
        );
      }

      mobileSearchPanel.setAttribute(
        "aria-hidden",
        nextState ? "false" : "true"
      );

      if (nextState && mobileSearchInput) {
        window.setTimeout(function () {
          mobileSearchInput.focus();
          mobileSearchInput.select();
        }, 40);
      }
    }

    function setFeedback(message, isError) {
      searchFeedbackNodes.forEach(function (node) {
        node.textContent = message || "";
        node.classList.toggle("is-error", !!isError);
      });
    }

    function syncSearchInputs(value, sourceInput) {
      searchInputs.forEach(function (input) {
        if (input !== sourceInput && input.value !== value) {
          input.value = value;
        }
      });
    }

    function clearHighlights() {
      root.querySelectorAll(".mcprices-search-hit").forEach(function (node) {
        node.classList.remove("mcprices-search-hit");
      });
    }

    function getHighlightNode(node) {
      return (
        node.closest("tr") ||
        node.closest(".menu-card") ||
        node.closest(".cat-card") ||
        node.closest(".featured-item") ||
        node.closest(".link-card") ||
        node.closest(".faq-item") ||
        node.closest(".new-item-card") ||
        node.closest(".cal-card") ||
        node
      );
    }

    function scrollToNode(node) {
      if (!node) {
        return;
      }

      window.requestAnimationFrame(function () {
        var headerOffset = getCurrentHeaderOffset();

        var nodeRect = node.getBoundingClientRect();
        var fullyVisible =
          nodeRect.top >= headerOffset &&
          nodeRect.bottom <= window.innerHeight - 24;

        if (fullyVisible) {
          return;
        }

        var targetTop = window.scrollY + nodeRect.top - headerOffset;

        window.scrollTo({
          top: Math.max(targetTop, 0),
          behavior: "smooth",
        });
      });
    }

    function syncHeaderScrolledState() {
      if (!pageBody) {
        return;
      }

      pageBody.classList.toggle("mcprices-header-scrolled", window.scrollY > 80);
    }

    function setActiveTabState(filter) {
      tabs.forEach(function (tab) {
        var isActive = tab.getAttribute("data-menu-filter") === filter;
        tab.classList.toggle("active", isActive);
        tab.setAttribute("aria-pressed", isActive ? "true" : "false");
      });
    }

    function showAllMenuSections() {
      menuSections.forEach(function (section) {
        section.classList.remove("is-hidden");
      });

      if (whatsNewSection) {
        whatsNewSection.classList.remove("is-hidden");
      }
    }

    function activateFilter(filter, options) {
      var settings = options || {};
      var normalized = filter || "all";

      setActiveTabState(normalized);

      if (normalized === "all") {
        showAllMenuSections();
        if (settings.scroll !== false) {
          scrollToNode(root.querySelector("#full-menu"));
        }
        return;
      }

      if (normalized === "whats-new") {
        showAllMenuSections();
        if (whatsNewSection && settings.scroll !== false) {
          scrollToNode(whatsNewSection);
        }
        return;
      }

      var targetSection = menuSections.find(function (section) {
        return section.getAttribute("data-menu-category") === normalized;
      });

      showAllMenuSections();

      menuSections.forEach(function (section) {
        if (targetSection && section !== targetSection) {
          section.classList.add("is-hidden");
        }
      });

      if (targetSection && settings.scroll !== false) {
        scrollToNode(targetSection);
      }
    }

    function activateFromHash() {
      var hash = window.location.hash.replace(/^#/, "");

      if (!hash) {
        return;
      }

      if (hash === "full-menu") {
        activateFilter("all", { scroll: false });
        return;
      }

      if (hash === "whats-new") {
        activateFilter("whats-new", { scroll: false });
        return;
      }

      var matchingSection = root.querySelector(
        '[data-menu-category][id="' + hash + '"]'
      );

      if (matchingSection) {
        activateFilter(matchingSection.getAttribute("data-menu-category"), {
          scroll: false,
        });
      }
    }

    function runSearch(queryValue) {
      var query = (queryValue || "").trim().toLowerCase();

      clearHighlights();

      if (!query) {
        setFeedback("Enter a menu item, section, or guide to search.", true);
        return;
      }

      var match = null;
      var nodes = root.querySelectorAll(searchSelectors.join(","));

      nodes.forEach(function (node) {
        if (match) {
          return;
        }

        if (node.textContent.toLowerCase().indexOf(query) !== -1) {
          match = node;
        }
      });

      if (!match) {
        setFeedback('No results found for "' + queryValue + '".', true);
        return;
      }

      var targetSection = match.closest("[data-menu-category]");
      if (targetSection) {
        activateFilter(targetSection.getAttribute("data-menu-category"), {
          scroll: false,
        });
      }

      var highlightNode = getHighlightNode(match);
      highlightNode.classList.add("mcprices-search-hit");
      scrollToNode(highlightNode);

      var labelNode =
        highlightNode.querySelector(
          ".td-name, .card-name, .cat-name, .item-name, .link-card-title, .new-item-name, .menu-section-title"
        ) || match;
      setFeedback('Jumped to "' + labelNode.textContent.trim() + '".', false);

      if (pageBody.classList.contains("mcprices-mobile-search-open")) {
        setMobileSearchOpen(false);
      }
    }

    applyOfficialMedia();

    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        activateFilter(tab.getAttribute("data-menu-filter"));
      });
    });

    searchForms.forEach(function (form) {
      var input = getSearchInput(form);

      form.addEventListener("submit", function (event) {
        event.preventDefault();
        runSearch(input ? input.value : "");
      });
    });

    searchInputs.forEach(function (input) {
      input.addEventListener("input", function () {
        syncSearchInputs(input.value, input);
        clearHighlights();
        setFeedback("", false);
      });

      input.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
          if (pageBody.classList.contains("mcprices-mobile-search-open")) {
            setMobileSearchOpen(false);
          } else {
            input.value = "";
            syncSearchInputs("", input);
            clearHighlights();
            setFeedback("", false);
          }
        }
      });
    });

    if (mobileSearchToggle) {
      mobileSearchToggle.addEventListener("click", function (event) {
        event.preventDefault();
        setMobileSearchOpen(
          !pageBody.classList.contains("mcprices-mobile-search-open")
        );
      });
    }

    document.addEventListener("click", function (event) {
      if (!pageBody.classList.contains("mcprices-mobile-search-open")) {
        return;
      }

      var clickedInsideSearch =
        event.target.closest("[data-mcprices-mobile-search-toggle]") ||
        event.target.closest("[data-mcprices-mobile-search-panel]");

      if (!clickedInsideSearch) {
        setMobileSearchOpen(false);
      }
    });

    root.addEventListener("click", function (event) {
      var button = event.target.closest(".btn-card");

      if (!button || button.tagName !== "BUTTON") {
        return;
      }

      event.preventDefault();

      if (button.textContent.toLowerCase().indexOf("calories") !== -1) {
        scrollToNode(root.querySelector("#calories"));
        return;
      }

      scrollToNode(root.querySelector("#full-menu"));
    });

    syncHeaderScrolledState();
    updateMobileHeaderOffset();
    updateMobileQuickNavState();
    window.addEventListener("scroll", syncHeaderScrolledState, {
      passive: true,
    });
    window.addEventListener("scroll", updateMobileHeaderOffset, {
      passive: true,
    });
    window.addEventListener("scroll", updateMobileQuickNavState, {
      passive: true,
    });
    window.addEventListener("resize", function () {
      updateMobileHeaderOffset();
      updateMobileQuickNavState();
      if (!isMobileViewport()) {
        setMobileSearchOpen(false);
      }
    });
    activateFromHash();
    window.addEventListener("hashchange", function () {
      activateFromHash();
      updateMobileQuickNavState();
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMcPricesInteractions);
  } else {
    initMcPricesInteractions();
  }
})();
