(function () {
  function initMcPricesInteractions() {
    var root = document.querySelector(".mcprices-page");
    var pageBody = document.body;
    var tabs;
    var menuSections;
    var whatsNewSection;
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
    var toolCatalog = Array.isArray(window.mcpricesToolCatalog)
      ? window.mcpricesToolCatalog.slice()
      : [];

    function formatCurrency(value) {
      var numericValue = Number(value || 0);

      return "$" + numericValue.toFixed(2);
    }

    function escapeHtml(value) {
      return String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
    }

    function getToolValueScore(item) {
      if (!item || !item.price) {
        return 0;
      }

      return Math.round((Number(item.calories || 0) / Number(item.price)) * 10) / 10;
    }

    function getToolCatalogItem(itemId) {
      for (var index = 0; index < toolCatalog.length; index += 1) {
        if (toolCatalog[index].id === itemId) {
          return toolCatalog[index];
        }
      }

      return null;
    }

    function isBudgetMealCandidate(item) {
      var allowedCategories = {
        "whats-new": true,
        meals: true,
        mcvalue: true,
        breakfast: true,
        burgers: true,
        chickenfish: true,
        nuggets: true,
        happymeal: true,
        snackwrap: true,
      };

      return (
        !!item &&
        !!allowedCategories[item.categoryId] &&
        Number(item.calories || 0) >= 250
      );
    }

    function getToolCategories(items) {
      var seen = {};
      var categories = [];

      (items || []).forEach(function (item) {
        if (!item || !item.categoryId || seen[item.categoryId]) {
          return;
        }

        seen[item.categoryId] = true;
        categories.push({
          id: item.categoryId,
          label: item.category || item.categoryId,
        });
      });

      categories.sort(function (left, right) {
        return left.label.localeCompare(right.label);
      });

      return categories;
    }

    function renderToolEmpty(message) {
      return (
        '<div class="mcprices-tool-empty">' + escapeHtml(message || "") + "</div>"
      );
    }

    function buildToolOptionMarkup(selectedValue) {
      var grouped = {};
      var categories = getToolCategories(toolCatalog);
      var markup = '<option value="">Select an item</option>';

      toolCatalog.forEach(function (item) {
        if (!grouped[item.categoryId]) {
          grouped[item.categoryId] = [];
        }

        grouped[item.categoryId].push(item);
      });

      categories.forEach(function (category) {
        var items = grouped[category.id] || [];

        items.sort(function (left, right) {
          return left.name.localeCompare(right.name);
        });

        markup += '<optgroup label="' + escapeHtml(category.label) + '">';
        items.forEach(function (item) {
          markup +=
            '<option value="' +
            escapeHtml(item.id) +
            '"' +
            (selectedValue === item.id ? ' selected="selected"' : "") +
            ">" +
            escapeHtml(item.name) +
            "</option>";
        });
        markup += "</optgroup>";
      });

      return markup;
    }

    function initBudgetFinder(toolRoot) {
      var buttons = Array.prototype.slice.call(
        toolRoot.querySelectorAll("[data-budget-option]")
      );
      var feedbackNode = toolRoot.querySelector("[data-budget-feedback]");
      var resultsNode = toolRoot.querySelector("[data-budget-results]");

      function renderBudget(maxBudget) {
        var filtered = toolCatalog
          .filter(function (item) {
            return isBudgetMealCandidate(item) && Number(item.price || 0) <= maxBudget;
          })
          .sort(function (left, right) {
            var scoreDifference = getToolValueScore(right) - getToolValueScore(left);

            if (scoreDifference !== 0) {
              return scoreDifference;
            }

            if (left.price !== right.price) {
              return left.price - right.price;
            }

            return right.calories - left.calories;
          })
          .slice(0, 8);

        buttons.forEach(function (button) {
          var isActive = Number(button.getAttribute("data-budget-option")) === maxBudget;
          button.classList.toggle("is-active", isActive);
          button.setAttribute("aria-pressed", isActive ? "true" : "false");
        });

        if (!filtered.length) {
          feedbackNode.textContent = "No tracked items fit that spend cap right now.";
          resultsNode.innerHTML = renderToolEmpty(
            "No current tracked menu items were found under that spend target."
          );
          return;
        }

        feedbackNode.textContent =
          "Showing " +
          filtered.length +
          " current picks under $" +
          maxBudget +
          " ranked by calories-per-dollar value.";

        resultsNode.innerHTML = filtered
          .map(function (item) {
            return (
              '<article class="mcprices-budget-card">' +
              '<div class="mcprices-budget-card__top">' +
              "<div>" +
              '<h3 class="mcprices-budget-card__name">' +
              escapeHtml(item.name) +
              "</h3>" +
              '<p class="mcprices-budget-card__category">' +
              escapeHtml(item.category) +
              "</p>" +
              "</div>" +
              '<div class="mcprices-budget-card__price">' +
              formatCurrency(item.price) +
              "</div>" +
              "</div>" +
              '<div class="mcprices-budget-card__meta">' +
              '<div class="mcprices-budget-card__meta-item"><span class="mcprices-budget-card__meta-label">Calories</span><span class="mcprices-budget-card__meta-value">' +
              escapeHtml(item.calories) +
              " kcal</span></div>" +
              '<div class="mcprices-budget-card__meta-item"><span class="mcprices-budget-card__meta-label">Value score</span><span class="mcprices-budget-card__meta-value">' +
              escapeHtml(getToolValueScore(item)) +
              " cal/$</span></div>" +
              "</div>" +
              '<div class="mcprices-budget-card__actions"><a class="mcprices-tool-link" href="' +
              escapeHtml(item.url) +
              '">View item</a></div>' +
              "</article>"
            );
          })
          .join("");
      }

      buttons.forEach(function (button) {
        button.addEventListener("click", function () {
          renderBudget(Number(button.getAttribute("data-budget-option") || 0));
        });
      });

      renderBudget(8);
    }

    function initCalorieCalculator(toolRoot) {
      var searchInput = toolRoot.querySelector("[data-calorie-search]");
      var filtersNode = toolRoot.querySelector("[data-calorie-filters]");
      var listNode = toolRoot.querySelector("[data-calorie-list]");
      var totalCaloriesNode = toolRoot.querySelector("[data-calorie-total]");
      var totalPriceNode = toolRoot.querySelector("[data-price-total]");
      var totalItemsNode = toolRoot.querySelector("[data-item-total]");
      var selectionNode = toolRoot.querySelector("[data-calorie-selection]");
      var clearButton = toolRoot.querySelector("[data-calorie-clear]");
      var selectedIds = {};
      var activeCategory = "all";

      function getFilteredItems() {
        var searchTerm = (searchInput.value || "").toLowerCase().trim();

        return toolCatalog.filter(function (item) {
          var matchesCategory =
            activeCategory === "all" || item.categoryId === activeCategory;
          var matchesSearch =
            !searchTerm ||
            item.name.toLowerCase().indexOf(searchTerm) !== -1 ||
            item.category.toLowerCase().indexOf(searchTerm) !== -1 ||
            String(item.subLabel || "").toLowerCase().indexOf(searchTerm) !== -1;

          return matchesCategory && matchesSearch;
        });
      }

      function getSelectedItems() {
        return toolCatalog.filter(function (item) {
          return !!selectedIds[item.id];
        });
      }

      function renderFilters() {
        var categories = getToolCategories(toolCatalog);
        var markup =
          '<button class="mcprices-tool-pill' +
          (activeCategory === "all" ? " is-active" : "") +
          '" type="button" data-calorie-filter="all">All categories</button>';

        categories.forEach(function (category) {
          markup +=
            '<button class="mcprices-tool-pill' +
            (activeCategory === category.id ? " is-active" : "") +
            '" type="button" data-calorie-filter="' +
            escapeHtml(category.id) +
            '">' +
            escapeHtml(category.label) +
            "</button>";
        });

        filtersNode.innerHTML = markup;
      }

      function renderSummary() {
        var items = getSelectedItems();
        var totalCalories = 0;
        var totalPrice = 0;

        items.forEach(function (item) {
          totalCalories += Number(item.calories || 0);
          totalPrice += Number(item.price || 0);
        });

        totalCaloriesNode.innerHTML = totalCalories + " <span>kcal</span>";
        totalPriceNode.textContent = formatCurrency(totalPrice);
        totalItemsNode.textContent = String(items.length);

        if (!items.length) {
          selectionNode.innerHTML = renderToolEmpty(
            "No items selected yet. Add items from the list to build your meal."
          );
          return;
        }

        selectionNode.innerHTML = items
          .map(function (item) {
            return (
              '<div class="mcprices-calculator-selection__item"><span>' +
              escapeHtml(item.name) +
              "</span><strong>" +
              escapeHtml(item.calories) +
              " kcal</strong></div>"
            );
          })
          .join("");
      }

      function renderList() {
        var items = getFilteredItems();

        if (!items.length) {
          listNode.innerHTML = renderToolEmpty(
            "No tracked items match that category and search filter."
          );
          return;
        }

        listNode.innerHTML = items
          .map(function (item) {
            var isSelected = !!selectedIds[item.id];

            return (
              '<div class="mcprices-calculator-row' +
              (isSelected ? " is-selected" : "") +
              '" data-calorie-row="' +
              escapeHtml(item.id) +
              '">' +
              '<input class="mcprices-calculator-row__check" type="checkbox"' +
              (isSelected ? ' checked="checked"' : "") +
              ' tabindex="-1" aria-hidden="true">' +
              '<div class="mcprices-calculator-row__main"><p class="mcprices-calculator-row__name">' +
              escapeHtml(item.name) +
              '</p><p class="mcprices-calculator-row__meta">' +
              escapeHtml(item.category) +
              "</p></div>" +
              '<div class="mcprices-calculator-row__side"><span class="mcprices-calculator-row__price">' +
              formatCurrency(item.price) +
              '</span><span class="mcprices-calculator-row__calories">' +
              escapeHtml(item.calories) +
              " kcal</span></div>" +
              "</div>"
            );
          })
          .join("");
      }

      filtersNode.addEventListener("click", function (event) {
        var button = event.target.closest("[data-calorie-filter]");

        if (!button) {
          return;
        }

        activeCategory = button.getAttribute("data-calorie-filter") || "all";
        renderFilters();
        renderList();
      });

      listNode.addEventListener("click", function (event) {
        var row = event.target.closest("[data-calorie-row]");
        var itemId;

        if (!row) {
          return;
        }

        itemId = row.getAttribute("data-calorie-row");

        if (!itemId) {
          return;
        }

        if (selectedIds[itemId]) {
          delete selectedIds[itemId];
        } else {
          selectedIds[itemId] = true;
        }

        renderList();
        renderSummary();
      });

      searchInput.addEventListener("input", renderList);
      clearButton.addEventListener("click", function () {
        selectedIds = {};
        renderList();
        renderSummary();
      });

      renderFilters();
      renderList();
      renderSummary();
    }

    function initCompareItems(toolRoot) {
      var selects = Array.prototype.slice.call(
        toolRoot.querySelectorAll("[data-compare-select]")
      );
      var compareButton = toolRoot.querySelector("[data-compare-run]");
      var feedbackNode = toolRoot.querySelector("[data-compare-feedback]");
      var gridNode = toolRoot.querySelector("[data-compare-grid]");
      var defaultNames = [
        "Big Mac",
        "Quarter Pounder with Cheese",
        "McChicken",
        "Egg McMuffin",
      ];

      function setDefaultSelections() {
        selects.forEach(function (selectNode, index) {
          var matchingItem = null;
          var catalogIndex;

          selectNode.innerHTML = buildToolOptionMarkup("");

          for (catalogIndex = 0; catalogIndex < toolCatalog.length; catalogIndex += 1) {
            if (toolCatalog[catalogIndex].name === defaultNames[index]) {
              matchingItem = toolCatalog[catalogIndex];
              break;
            }
          }

          if (matchingItem) {
            selectNode.value = matchingItem.id;
          }
        });
      }

      function renderCompare() {
        var pickedIds = [];
        var items;
        var lowestPrice;
        var lowestCalories;
        var strongestValue;

        selects.forEach(function (selectNode) {
          var value = selectNode.value;

          if (value && pickedIds.indexOf(value) === -1) {
            pickedIds.push(value);
          }
        });

        items = pickedIds
          .map(function (itemId) {
            return getToolCatalogItem(itemId);
          })
          .filter(Boolean);

        if (items.length < 2) {
          feedbackNode.textContent =
            "Select at least two items to see a meaningful comparison.";
          gridNode.innerHTML = renderToolEmpty(
            "Choose two to four tracked menu items to compare price, calories, and value."
          );
          return;
        }

        lowestPrice = Math.min.apply(
          null,
          items.map(function (item) {
            return Number(item.price || 0);
          })
        );
        lowestCalories = Math.min.apply(
          null,
          items.map(function (item) {
            return Number(item.calories || 0);
          })
        );
        strongestValue = Math.max.apply(
          null,
          items.map(function (item) {
            return getToolValueScore(item);
          })
        );

        feedbackNode.textContent =
          "Comparing " + items.length + " current menu items side by side.";

        gridNode.innerHTML = items
          .map(function (item) {
            var badges = [];

            if (Number(item.price || 0) === lowestPrice) {
              badges.push('<span class="mcprices-tool-badge">Lowest price</span>');
            }

            if (Number(item.calories || 0) === lowestCalories) {
              badges.push('<span class="mcprices-tool-badge">Lowest calories</span>');
            }

            if (getToolValueScore(item) === strongestValue) {
              badges.push('<span class="mcprices-tool-badge">Best value score</span>');
            }

            return (
              '<article class="mcprices-compare-card">' +
              '<div class="mcprices-compare-card__head">' +
              "<div>" +
              '<h3 class="mcprices-compare-card__title">' +
              escapeHtml(item.name) +
              "</h3>" +
              '<p class="mcprices-compare-card__category">' +
              escapeHtml(item.category) +
              "</p>" +
              "</div>" +
              "</div>" +
              '<div class="mcprices-compare-card__stats">' +
              '<div class="mcprices-compare-card__stat"><span class="mcprices-compare-card__label">Price</span><span class="mcprices-compare-card__value mcprices-compare-card__value--price">' +
              formatCurrency(item.price) +
              '</span></div>' +
              '<div class="mcprices-compare-card__stat"><span class="mcprices-compare-card__label">Calories</span><span class="mcprices-compare-card__value">' +
              escapeHtml(item.calories) +
              ' kcal</span></div>' +
              '<div class="mcprices-compare-card__stat"><span class="mcprices-compare-card__label">Value score</span><span class="mcprices-compare-card__value">' +
              escapeHtml(getToolValueScore(item)) +
              ' cal/$</span></div>' +
              '<div class="mcprices-compare-card__stat"><span class="mcprices-compare-card__label">Category</span><span class="mcprices-compare-card__value">' +
              escapeHtml(item.category) +
              "</span></div>" +
              "</div>" +
              '<div class="mcprices-tool-badge-row">' +
              badges.join("") +
              "</div>" +
              '<div class="mcprices-compare-card__actions"><a class="mcprices-tool-link" href="' +
              escapeHtml(item.url) +
              '">View item</a></div>' +
              "</article>"
            );
          })
          .join("");
      }

      compareButton.addEventListener("click", renderCompare);
      selects.forEach(function (selectNode) {
        selectNode.addEventListener("change", renderCompare);
      });

      setDefaultSelections();
      renderCompare();
    }

    function initInteractiveTools() {
      var toolRoots = Array.prototype.slice.call(
        document.querySelectorAll("[data-mcprices-tool-root]")
      );

      if (!toolRoots.length || !toolCatalog.length) {
        return;
      }

      toolRoots.forEach(function (toolRoot) {
        var toolType = toolRoot.getAttribute("data-mcprices-tool-root");

        if ("budget-finder" === toolType) {
          initBudgetFinder(toolRoot);
        } else if ("calorie-calculator" === toolType) {
          initCalorieCalculator(toolRoot);
        } else if ("compare-items" === toolType) {
          initCompareItems(toolRoot);
        }
      });
    }

    function initHomeToolPanels() {
      var homeToolsRoot = document.querySelector("[data-home-tool-stage]");
      var panels;
      var activators;
      var statefulTabs;
      var closeButton;
      var defaultTool;
      var isOpen;

      if (!homeToolsRoot) {
        return;
      }

      panels = Array.prototype.slice.call(
        homeToolsRoot.querySelectorAll("[data-home-tool-panel]")
      );
      activators = Array.prototype.slice.call(
        document.querySelectorAll("[data-home-tool-trigger]")
      );
      statefulTabs = Array.prototype.slice.call(
        document.querySelectorAll("[data-home-tool-tab]")
      );
      closeButton = homeToolsRoot.querySelector("[data-home-tool-close]");
      defaultTool =
        homeToolsRoot.getAttribute("data-home-tool-default") ||
        (panels[0] && panels[0].getAttribute("data-home-tool-panel")) ||
        "";
      isOpen = homeToolsRoot.getAttribute("data-home-tool-open") === "1";

      if (!panels.length || !activators.length || !defaultTool) {
        return;
      }

      function syncCollapsedState() {
        homeToolsRoot.classList.toggle("is-collapsed", !isOpen);
        homeToolsRoot.setAttribute("data-home-tool-open", isOpen ? "1" : "0");
      }

      function closeToolPanels() {
        isOpen = false;
        panels.forEach(function (panelNode) {
          panelNode.classList.remove("is-active");
          panelNode.hidden = true;
        });

        statefulTabs.forEach(function (tabNode) {
          tabNode.classList.remove("is-active");

          if ("BUTTON" === tabNode.tagName) {
            tabNode.setAttribute("aria-pressed", "false");
          } else {
            tabNode.removeAttribute("aria-current");
          }
        });

        syncCollapsedState();
      }

      function activateTool(toolSlug, shouldScroll) {
        var targetSlug = toolSlug || defaultTool;
        var hasTargetPanel = panels.some(function (panelNode) {
          return panelNode.getAttribute("data-home-tool-panel") === targetSlug;
        });
        var currentPanel = panels.find(function (panelNode) {
          return panelNode.classList.contains("is-active");
        });
        var currentSlug = currentPanel
          ? currentPanel.getAttribute("data-home-tool-panel")
          : "";

        if (!hasTargetPanel) {
          targetSlug = defaultTool;
        }

        if (isOpen && currentSlug === targetSlug) {
          closeToolPanels();

          if (shouldScroll) {
            scrollToNode(homeToolsRoot);
          }

          return;
        }

        isOpen = true;
        syncCollapsedState();

        panels.forEach(function (panelNode) {
          var isActive =
            panelNode.getAttribute("data-home-tool-panel") === targetSlug;

          panelNode.classList.toggle("is-active", isActive);
          panelNode.hidden = !isActive;
        });

        statefulTabs.forEach(function (tabNode) {
          var isActive = tabNode.getAttribute("data-home-tool-tab") === targetSlug;

          tabNode.classList.toggle("is-active", isActive);

          if ("BUTTON" === tabNode.tagName) {
            tabNode.setAttribute("aria-pressed", isActive ? "true" : "false");
          } else if (isActive) {
            tabNode.setAttribute("aria-current", "true");
          } else {
            tabNode.removeAttribute("aria-current");
          }
        });

        if (shouldScroll) {
          scrollToNode(homeToolsRoot);
        }
      }

      activators.forEach(function (triggerNode) {
        triggerNode.addEventListener("click", function (event) {
          var targetSlug = triggerNode.getAttribute("data-home-tool-trigger");
          var shouldScroll = triggerNode.hasAttribute("data-home-tool-scroll");

          if ("A" === triggerNode.tagName) {
            event.preventDefault();
          }

          activateTool(targetSlug, shouldScroll);
        });
      });

      if (closeButton) {
        closeButton.addEventListener("click", function () {
          closeToolPanels();
        });
      }

      if (isOpen) {
        activateTool(defaultTool, false);
      } else {
        closeToolPanels();
      }
    }

    initInteractiveTools();
    initHomeToolPanels();

    if (!root) {
      return;
    }

    tabs = Array.prototype.slice.call(root.querySelectorAll(".menu-tab[data-menu-filter]"));
    menuSections = Array.prototype.slice.call(
      root.querySelectorAll(".menu-section[data-menu-category]")
    );
    whatsNewSection = root.querySelector(".whats-new[data-menu-category]");

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
        activateFilter("all", { scroll: false });
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
        var filterTarget = tab.getAttribute("data-menu-filter");
        if (window.requestAnimationFrame) {
          window.requestAnimationFrame(function () {
            activateFilter(filterTarget);
          });
        } else {
          activateFilter(filterTarget);
        }
      });
    });

    searchForms.forEach(function (form) {
      var input = getSearchInput(form);

      form.addEventListener("submit", function (event) {
        event.preventDefault();
        var query = input ? input.value : "";
        if (window.requestAnimationFrame) {
          window.requestAnimationFrame(function () {
            runSearch(query);
          });
        } else {
          runSearch(query);
        }
      });
    });

    var searchInputDebounceTimer = null;
    searchInputs.forEach(function (input) {
      input.addEventListener("input", function () {
        var val = input.value;
        if (searchInputDebounceTimer) {
          clearTimeout(searchInputDebounceTimer);
        }
        searchInputDebounceTimer = setTimeout(function () {
          if (window.requestAnimationFrame) {
            window.requestAnimationFrame(function () {
              syncSearchInputs(val, input);
              clearHighlights();
              setFeedback("", false);
            });
          } else {
            syncSearchInputs(val, input);
            clearHighlights();
            setFeedback("", false);
          }
        }, 40);
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


/* Live Homepage Menu Search Handler */
document.addEventListener("DOMContentLoaded", function() {
  var searchInput = document.getElementById("mcprices-live-menu-search");
  var searchForm = document.querySelector(".mcprices-hero-search-form");

  if (!searchInput) return;

  function performFilter() {
    var query = searchInput.value.trim().toLowerCase();
    var tables = document.querySelectorAll(".menu-table");
    var totalMatches = 0;

    tables.forEach(function(table) {
      var rows = table.querySelectorAll("tbody tr");
      var sectionMatchCount = 0;

      rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        if (!query || text.indexOf(query) !== -1) {
          row.style.display = "";
          sectionMatchCount++;
          totalMatches++;
        } else {
          row.style.display = "none";
        }
      });

      var wrap = table.closest(".menu-section") || table.closest(".wp-block-group");
      if (wrap) {
        if (query && sectionMatchCount === 0) {
          wrap.style.display = "none";
        } else {
          wrap.style.display = "";
        }
      }
    });
  }

  searchInput.addEventListener("input", performFilter);
  if (searchForm) {
    searchForm.addEventListener("submit", function(e) {
      e.preventDefault();
      performFilter();
      var fullMenu = document.getElementById("full-menu");
      if (fullMenu) {
        fullMenu.scrollIntoView({ behavior: "smooth" });
      }
    });
  }
});
