<?php
/**
 * Homepage block pattern content for the McPrices layout.
 *
 * @package kadence
 */

return <<<'HTML'
<!-- wp:group {"className":"mcprices-page mcprices-managed-homepage","layout":{"type":"default"}} -->
<div class="wp-block-group mcprices-page mcprices-managed-homepage" data-mcprices-pattern-version="2.4.0">
<!-- wp:html -->
<!-- UPDATE BAR -->


<!-- HEADER -->


<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-pattern"></div>
  <div class="hero-content">
    <div class="hero-left animate-fadeup">
      <div class="hero-eyebrow">Updated April 2026</div>
      <h1 class="hero-title">
        McDonald's Menu<br>
        Prices <span class="highlight">UK 2026</span>
      </h1>
      <p class="hero-sub">The most complete and up-to-date McDonald's UK price list. Find every item's price, calorie count, and the latest deals &mdash; all in one place.</p>
      [mcprices_hero_search]
      <div class="hero-btns">
        <a href="#full-menu" class="btn-primary">&#127828; View Full Menu</a>
        <a href="#deals" class="btn-outline">&#127991;&#65039; Latest Deals</a>
      </div>
      <div class="hero-stats">
        <div class="stat-item">
          <div class="stat-num">202</div>
          <div class="stat-label">Menu Items</div>
        </div>
        <div class="stat-item">
          <div class="stat-num">16</div>
          <div class="stat-label">Categories</div>
        </div>
        <div class="stat-item">
          <div class="stat-num">April</div>
          <div class="stat-label">Last Updated</div>
        </div>
        <div class="stat-item">
          <div class="stat-num">1,400+</div>
          <div class="stat-label">Locations</div>
        </div>
      </div>
    </div>
    <div class="hero-right animate-fadeup delay-2">
      <div class="hero-badge-float">
        &#128293; Most<br><span>Popular</span>
      </div>
      <div class="hero-card-main">
        <div class="hero-card-title">&#11088; Top Menu Items</div>
        <div class="featured-item">
          <div class="item-emoji">&#127828;</div>
          <div class="item-info">
            <div class="item-name">Big Mac</div>
            <div class="item-cal">509 kcal &middot; Burger</div>
          </div>
          <div class="item-price">&pound;5.09</div>
        </div>
        <div class="featured-item">
          <div class="item-emoji">&#127831;</div>
          <div class="item-info">
            <div class="item-name">McChicken Sandwich</div>
            <div class="item-cal">371 kcal &middot; Chicken</div>
          </div>
          <div class="item-price">&pound;4.79</div>
        </div>
        <div class="featured-item">
          <div class="item-emoji">&#129374;</div>
          <div class="item-info">
            <div class="item-name">Sausage &amp; Egg McGriddles</div>
            <div class="item-cal">492 kcal &middot; Breakfast</div>
          </div>
          <div class="item-price">&pound;4.99</div>
        </div>
        <div class="featured-item">
          <div class="item-emoji">&#127846;</div>
          <div class="item-info">
            <div class="item-name">Oreo McFlurry</div>
            <div class="item-cal">330 kcal &middot; Dessert</div>
          </div>
          <div class="item-price">&pound;2.19</div>
        </div>
        <div class="featured-item">
          <div class="item-emoji">&#127839;</div>
          <div class="item-info">
            <div class="item-name">Large Fries</div>
            <div class="item-cal">444 kcal &middot; Sides</div>
          </div>
          <div class="item-price">&pound;1.99</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- AD BANNER -->
<div class="container">
  <div class="ad-banner">
    <span class="ad-banner-label">Advertisement</span>
    728&#215;90 Ad Placement &#8212; Google AdSense / Mediavine
  </div>
</div>

<!-- BREADCRUMBS -->
<div class="breadcrumbs">
  <div class="container">
    <div class="breadcrumb-inner">
      <a href="#">Home</a>
      <span class="breadcrumb-sep">&#8250;</span>
      <span class="breadcrumb-current">McDonald's UK Menu Prices 2026</span>
    </div>
  </div>
</div>

<!-- CATEGORIES -->
<section class="categories">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Browse by Category</div>
      <h2 class="section-title">What Are You Looking For&rarr;</h2>
      <p class="section-sub">Jump straight to the menu category you need &mdash; full prices and calories included.</p>
    </div>
    <div class="cat-grid">
      <a href="#whats-new" class="cat-card red">
        <span class="cat-emoji">&#127381;</span>
        <div class="cat-name">What's New</div>
        <div class="cat-count">13 latest items</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#burgers" class="cat-card">
        <span class="cat-emoji">&#127828;</span>
        <div class="cat-name">Burgers</div>
        <div class="cat-count">15 items</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#saver" class="cat-card">
        <span class="cat-emoji">&#127991;&#65039;</span>
        <div class="cat-name">Saver Menu</div>
        <div class="cat-count">21 value picks</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#nuggets" class="cat-card">
        <span class="cat-emoji">&#127831;</span>
        <div class="cat-name">McNuggets</div>
        <div class="cat-count">10 items</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#wraps" class="cat-card">
        <span class="cat-emoji">&#127791;</span>
        <div class="cat-name">Wraps &amp; Salads</div>
        <div class="cat-count">11 items</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#vegetarian" class="cat-card">
        <span class="cat-emoji">&#127793;</span>
        <div class="cat-name">Vegetarian</div>
        <div class="cat-count">13 items</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#vegan" class="cat-card">
        <span class="cat-emoji">&#127807;</span>
        <div class="cat-name">Vegan</div>
        <div class="cat-count">5 items</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#happymeal" class="cat-card">
        <span class="cat-emoji">&#127881;</span>
        <div class="cat-name">Happy Meal</div>
        <div class="cat-count">6 meals</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#breakfast" class="cat-card">
        <span class="cat-emoji">&#129374;</span>
        <div class="cat-name">Breakfast</div>
        <div class="cat-count">20 items</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#bsaver" class="cat-card">
        <span class="cat-emoji">&#127859;</span>
        <div class="cat-name">Breakfast Saver</div>
        <div class="cat-count">13 morning picks</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#mccafe" class="cat-card">
        <span class="cat-emoji">&#9749;</span>
        <div class="cat-name">McCaf&#233;</div>
        <div class="cat-count">15 drinks</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#desserts" class="cat-card">
        <span class="cat-emoji">&#127846;</span>
        <div class="cat-name">Desserts &amp; McFlurry</div>
        <div class="cat-count">12 sweet picks</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#drinks" class="cat-card">
        <span class="cat-emoji">&#127865;</span>
        <div class="cat-name">Drinks</div>
        <div class="cat-count">21 cold drinks</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#sides" class="cat-card">
        <span class="cat-emoji">&#127839;</span>
        <div class="cat-name">Fries &amp; Sides</div>
        <div class="cat-count">7 items</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#sharers" class="cat-card">
        <span class="cat-emoji">&#128230;</span>
        <div class="cat-name">Sharers &amp; Bundles</div>
        <div class="cat-count">3 share boxes</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#sauces" class="cat-card">
        <span class="cat-emoji">&#129514;</span>
        <div class="cat-name">Condiments &amp; Sauces</div>
        <div class="cat-count">17 dips &amp; extras</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
      <a href="#deals" class="cat-card red">
        <span class="cat-emoji">&#128293;</span>
        <div class="cat-name">Deals</div>
        <div class="cat-count">Live value picks</div>
        <div class="cat-arrow">&rarr;</div>
      </a>
    </div>
  </div>
</section>

<!-- WHAT'S NEW SECTION -->
<section class="whats-new" id="whats-new" data-menu-category="whats-new">
  <div class="container">
    <div class="section-header">
      <div class="section-label">April 2026</div>
      <h2 class="section-title">What's New at McDonald's UK 2026</h2>
      <p class="section-sub">McDonald's is kicking off April 2026 with limited-time burgers, spicy chicken, returning breakfast favourites and seasonal desserts.</p>
    </div>
    <div class="mcprices-whats-new-intro">
      <p class="mcprices-whats-new-intro-text">The attached April 2026 source adds <strong class="mcprices-whats-new-date">Big Arch</strong>, <strong>Double Big Mac</strong>, <strong>Double Big Mac with Bacon</strong>, <strong>Sausage &amp; Egg McGriddles</strong>, <strong>Spicy Chicken McNuggets</strong> and the <strong>Cheesy Garlic Bread Dippers Sharebox</strong>, alongside CARDS meal bundles and the seasonal <strong>Cadbury Creme Egg McFlurry</strong>.</p>
    </div>
    <div class="new-items-grid">
      <div class="new-item-card">
        <div class="new-item-top">
          <div class="new-item-emoji">&#127828;</div>
          <span class="new-item-avail avail-new">New</span>
        </div>
        <div class="new-item-body">
          <div class="new-item-name">Big Arch&#174;</div>
          <div class="new-item-cal">1,057 kcal</div>
          <div class="new-item-price">&pound;6.89</div>
        </div>
      </div>
      <div class="new-item-card">
        <div class="new-item-top">
          <div class="new-item-emoji">&#127828;</div>
          <span class="new-item-avail avail-limited">Limited Time</span>
        </div>
        <div class="new-item-body">
          <div class="new-item-name">Double Big Mac&#174;</div>
          <div class="new-item-cal">715 kcal</div>
          <div class="new-item-price">&pound;7.09</div>
        </div>
      </div>
      <div class="new-item-card">
        <div class="new-item-top">
          <div class="new-item-emoji">&#129363;</div>
          <span class="new-item-avail avail-limited">Limited Time</span>
        </div>
        <div class="new-item-body">
          <div class="new-item-name">Double Big Mac&#174; with Bacon</div>
          <div class="new-item-cal">762 kcal</div>
          <div class="new-item-price">&pound;7.99</div>
        </div>
      </div>
      <div class="new-item-card">
        <div class="new-item-top">
          <div class="new-item-emoji">&#129374;</div>
          <span class="new-item-avail avail-limited">Limited Time</span>
        </div>
        <div class="new-item-body">
          <div class="new-item-name">Sausage &amp; Egg McGriddles&#174;</div>
          <div class="new-item-cal">492 kcal</div>
          <div class="new-item-price">&pound;4.99</div>
        </div>
      </div>
      <div class="new-item-card">
        <div class="new-item-top">
          <div class="new-item-emoji">&#127831;</div>
          <span class="new-item-avail avail-limited">Limited Time</span>
        </div>
        <div class="new-item-body">
          <div class="new-item-name">Spicy Chicken McNuggets&#174; 9 pieces</div>
          <div class="new-item-cal">392 kcal</div>
          <div class="new-item-price">&pound;6.49</div>
        </div>
      </div>
      <div class="new-item-card">
        <div class="new-item-top">
          <div class="new-item-emoji">&#127846;</div>
          <span class="new-item-avail avail-limited">Limited Time</span>
        </div>
        <div class="new-item-body">
          <div class="new-item-name">Cadbury Creme Egg&#174; McFlurry&#174;</div>
          <div class="new-item-cal">290 kcal</div>
          <div class="new-item-price">&pound;2.49</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURED ITEMS -->
<section class="featured">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Most Popular</div>
      <h2 class="section-title">Fan Favourites</h2>
      <p class="section-sub">The UK's most ordered McDonald's items &mdash; with current prices and calorie counts.</p>
    </div>
    <div class="featured-grid">
      <div class="menu-card">
        <div class="card-img">
          <div class="card-img-bg"></div>
          <div class="card-popular">&#128293; #1 Bestseller</div>
          &#127828;
        </div>
        <div class="card-body">
          <div class="card-top">
            <div>
              <div class="card-name">Big Mac</div>
              <div class="card-meta">
                <span class="card-cal">509 kcal</span>
                <span class="badge badge-red">Burger</span>
              </div>
            </div>
            <div class="card-price">&pound;5.09</div>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn-card">Classic Pick</button>
          <button class="btn-card">509 kcal</button>
        </div>
      </div>
      <div class="menu-card">
        <div class="card-img" style="background: #fff8e8;">
          <div class="card-img-bg"></div>
          &#127839;
        </div>
        <div class="card-body">
          <div class="card-top">
            <div>
              <div class="card-name">Large Fries</div>
              <div class="card-meta">
                <span class="card-cal">444 kcal</span>
                <span class="badge badge-yellow">Side</span>
              </div>
            </div>
            <div class="card-price">&pound;1.99</div>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn-card">All Sizes</button>
          <button class="btn-card">444 kcal</button>
        </div>
      </div>
      <div class="menu-card">
        <div class="card-img" style="background: #f5f0ff;">
          <div class="card-img-bg"></div>
          <div class="card-popular" style="background: var(--yellow); color: var(--black);">&#9889; Value Pick</div>
          &#127831;
        </div>
        <div class="card-body">
          <div class="card-top">
            <div>
              <div class="card-name">6 Piece Chicken McNuggets</div>
              <div class="card-meta">
                <span class="card-cal">261 kcal</span>
                <span class="badge badge-green">Popular</span>
              </div>
            </div>
            <div class="card-price">&pound;3.49</div>
          </div>
        </div>
        <div class="card-footer">
          <button class="btn-card">Dip Options</button>
          <button class="btn-card">261 kcal</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FULL MENU TABLE -->
<section class="full-menu" id="full-menu">
  <div class="container">
    <div class="section-header" style="text-align:left;">
      <div class="section-label">Full Price List</div>
      <h2 class="section-title">Complete McDonald's UK Menu 2026</h2>
      <p class="section-sub">Every item, every price &#8212; updated for April 2026. Click a category to jump directly.</p>
    </div>

    <div class="size-legend">
      <span class="size-legend-title">Size Key:</span>
      <div class="size-legend-items">
        <span class="size-legend-item"><strong>(S)</strong> Small</span>
        <span class="size-legend-item"><strong>(R)</strong> Regular</span>
        <span class="size-legend-item"><strong>(M)</strong> Medium</span>
        <span class="size-legend-item"><strong>(L)</strong> Large</span>
        <span class="size-legend-item" style="color:var(--grey-400);">&#128992; = Limited Time &nbsp; &#9899; = Not Currently Available</span>
      </div>
    </div>

    <div class="menu-tabs">
      <button class="menu-tab active" type="button" data-menu-filter="all" aria-pressed="true">&#9776; All</button>
      <button class="menu-tab" type="button" data-menu-filter="whats-new">&#127381; What's New</button>
      <button class="menu-tab" type="button" data-menu-filter="burgers">&#127828; Burgers</button>
      <button class="menu-tab" type="button" data-menu-filter="saver">&#127991;&#65039; Saver Menu</button>
      <button class="menu-tab" type="button" data-menu-filter="nuggets">&#127831; McNuggets</button>
      <button class="menu-tab" type="button" data-menu-filter="wraps">&#127791; Wraps &amp; Salads</button>
      <button class="menu-tab" type="button" data-menu-filter="breakfast">&#129374; Breakfast</button>
      <button class="menu-tab" type="button" data-menu-filter="bsaver">&#127859; Breakfast Saver</button>
      <button class="menu-tab" type="button" data-menu-filter="vegetarian">&#127793; Vegetarian</button>
      <button class="menu-tab" type="button" data-menu-filter="vegan">&#127807; Vegan</button>
      <button class="menu-tab" type="button" data-menu-filter="happymeal">&#127881; Happy Meal</button>
      <button class="menu-tab" type="button" data-menu-filter="mccafe">&#9749; McCaf&eacute;</button>
      <button class="menu-tab" type="button" data-menu-filter="desserts">&#127846; Desserts &amp; McFlurry</button>
      <button class="menu-tab" type="button" data-menu-filter="drinks">&#127865; Drinks</button>
      <button class="menu-tab" type="button" data-menu-filter="sides">&#127839; Fries &amp; Sides</button>
      <button class="menu-tab" type="button" data-menu-filter="sharers">&#128230; Sharers &amp; Bundles</button>
      <button class="menu-tab" type="button" data-menu-filter="sauces">&#129514; Condiments &amp; Sauces</button>
    </div>

    <div class="menu-section" id="burgers" data-menu-category="burgers">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127828;</span>
        <span class="menu-section-title">Burgers</span>
        <span class="menu-section-count">15 items</span>
      </div>
      <p class="menu-section-desc">All current burgers from the April 2026 source, from value picks like the Hamburger to premium builds like the Big Arch and McPlant.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Big Mac&#174;</div></td><td class="td-price">&pound;5.09</td><td class="td-cal">509 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Quarter Pounder&#8482; with Cheese</div></td><td class="td-price">&pound;5.09</td><td class="td-cal">514 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Double Quarter Pounder&#8482; with Cheese</div></td><td class="td-price">&pound;6.19</td><td class="td-cal"><span class="high">749</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">McCrispy&#174;</div></td><td class="td-price">&pound;6.19</td><td class="td-cal">484 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">McSpicy&#174;</div></td><td class="td-price">&pound;5.89</td><td class="td-cal">454 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">McChicken&#174; Sandwich</div></td><td class="td-price">&pound;4.79</td><td class="td-cal">371 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Meal Deal Plus</div></td><td class="td-price">&pound;5.59</td><td class="td-cal"><span class="high">771</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Double Filet-O-Fish&#174;</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">453 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Filet-O-Fish&#174;</div></td><td class="td-price">&pound;4.79</td><td class="td-cal">316 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">McPlant&#174;</div></td><td class="td-price">&pound;5.09</td><td class="td-cal">426 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Double Cheeseburger</div></td><td class="td-price">&pound;2.29</td><td class="td-cal">452 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Cheeseburger</div></td><td class="td-price">&pound;1.39</td><td class="td-cal">303 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Hamburger</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">255</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Mayo Chicken</div></td><td class="td-price">&pound;1.39</td><td class="td-cal"><span class="low">282</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Vegetable Deluxe</div></td><td class="td-price">&pound;4.89</td><td class="td-cal">361 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="saver" data-menu-category="saver">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127991;&#65039;</span>
        <span class="menu-section-title">Saver Menu</span>
        <span class="menu-section-count">21 items</span>
      </div>
      <p class="menu-section-desc">The official Saver Menu mixes budget burgers, fries, drinks and add-ons with the lowest current entry prices.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Meal Deal Plus</div></td><td class="td-price">&pound;5.59</td><td class="td-cal"><span class="high">771</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Double Cheeseburger</div></td><td class="td-price">&pound;2.29</td><td class="td-cal">452 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Cheeseburger</div></td><td class="td-price">&pound;1.39</td><td class="td-cal">303 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Hamburger</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">255</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Mayo Chicken</div></td><td class="td-price">&pound;1.39</td><td class="td-cal"><span class="low">282</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">McDonald's Fries Small</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">237</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Chocolate Milkshake Small</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">235</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Strawberry Milkshake Small</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">230</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Banana Milkshake Small</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">228</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Vanilla Milkshake Small</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">225</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">White Coffee</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">54</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Americano</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">6</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Coca-Cola&#174; Zero Sugar Small</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">1</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Diet Coke&#174; Small</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">1</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sprite&#174; Zero Small</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">1</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Fanta&#174; Orange Zero Small</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">2</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">IRN-BRU&#174; Small (selected)</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">68</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Oasis&#174; Summer Fruits Small</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">48</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Coca-Cola&#174; Classic Small</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">89</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Oreo&#174; McFlurry&#174; Mini</div></td><td class="td-price">&pound;1.39</td><td class="td-cal"><span class="low">188</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Smarties&#174; McFlurry&#174; Mini</div></td><td class="td-price">&pound;1.39</td><td class="td-cal"><span class="low">195</span> kcal</td><td>&mdash;</td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="nuggets" data-menu-category="nuggets">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127831;</span>
        <span class="menu-section-title">McNuggets</span>
        <span class="menu-section-count">10 items</span>
      </div>
      <p class="menu-section-desc">McNuggets, Chicken Selects and current dippers, including spicy limited-time variants and share boxes.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">6 Piece Chicken McNuggets&#174;</div></td><td class="td-price">&pound;3.49</td><td class="td-cal"><span class="low">261</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">9 Piece Chicken McNuggets&#174;</div></td><td class="td-price">&pound;4.79</td><td class="td-cal">392 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">20 Chicken McNuggets&#174; Sharebox&#174;</div></td><td class="td-price">&pound;6.99</td><td class="td-cal"><span class="high">869</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Chicken Selects&#174; (3 pieces)</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">359 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">9 Chicken Selects Sharebox&#174;</div></td><td class="td-price">&pound;9.19</td><td class="td-cal"><span class="high">1,008</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">The McDonald's Chicken Sharebox&#174;</div></td><td class="td-price">&pound;11.29</td><td class="td-cal"><span class="high">1,240</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Cheesy Garlic Bread Dippers (4 pcs)</div></td><td class="td-price">&pound;3.49</td><td class="td-cal"><span class="low">219</span> kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span> <span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Cheesy Garlic Bread Dippers Sharebox&#174;</div></td><td class="td-price">&pound;5.49</td><td class="td-cal">548 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span> <span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Spicy Chicken McNuggets&#174; 6 pieces</div></td><td class="td-price">&pound;4.99</td><td class="td-cal"><span class="low">261</span> kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span></td></tr>
          <tr><td><div class="td-name">Spicy Chicken McNuggets&#174; 9 pieces</div></td><td class="td-price">&pound;6.49</td><td class="td-cal">392 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="wraps" data-menu-category="wraps">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127791;</span>
        <span class="menu-section-title">Wraps &amp; Salads</span>
        <span class="menu-section-count">11 items</span>
      </div>
      <p class="menu-section-desc">Crispy and grilled wraps plus current salad options and side salad picks from the latest source.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Crispy Sweet Chilli Chicken Wrap</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">482 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Grilled Sweet Chilli Chicken Wrap</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">320 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Crispy BBQ &amp; Bacon Wrap</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">469 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Grilled BBQ &amp; Bacon Wrap</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">366 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Crispy Tikka Chicken Wrap</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">471 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span></td></tr>
          <tr><td><div class="td-name">Grilled Tikka Chicken Wrap</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">342 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span></td></tr>
          <tr><td><div class="td-name">Side Salad</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">18</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Crispy Chicken Salad</div></td><td class="td-price">&pound;3.29</td><td class="td-cal"><span class="low">265</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Crispy Chicken and Bacon Salad</div></td><td class="td-price">&pound;3.99</td><td class="td-cal">320 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Grilled Chicken Salad</div></td><td class="td-price">&pound;3.29</td><td class="td-cal"><span class="low">133</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Grilled Chicken and Bacon Salad</div></td><td class="td-price">&pound;4.09</td><td class="td-cal"><span class="low">183</span> kcal</td><td>&mdash;</td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="breakfast" data-menu-category="breakfast">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#129374;</span>
        <span class="menu-section-title">Breakfast</span>
        <span class="menu-section-count">20 items</span>
      </div>
      <p class="menu-section-desc">Breakfast is served until 11am and includes McMuffins, wraps, pancakes, porridge and the returning McGriddles.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Sausage &amp; Egg McGriddles&#174;</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">492 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span></td></tr>
          <tr><td><div class="td-name">Breakfast Wrap with Ketchup</div></td><td class="td-price">&pound;5.29</td><td class="td-cal">666 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Breakfast Wrap with Brown Sauce</div></td><td class="td-price">&pound;5.29</td><td class="td-cal">666 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Double Bacon &amp; Egg McMuffin&#174;</div></td><td class="td-price">&pound;2.59</td><td class="td-cal">377 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sausage &amp; Egg McMuffin&#174;</div></td><td class="td-price">&pound;3.29</td><td class="td-cal">424 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Bacon &amp; Egg McMuffin&#174;</div></td><td class="td-price">&pound;3.09</td><td class="td-cal">336 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Double Sausage &amp; Egg McMuffin&#174;</div></td><td class="td-price">&pound;4.29</td><td class="td-cal">552 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Egg &amp; Cheese McMuffin&#174;</div></td><td class="td-price">&pound;2.69</td><td class="td-cal"><span class="low">296</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Muffin with Jam</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">214</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Sausage Sandwich with Ketchup</div></td><td class="td-price">&pound;3.59</td><td class="td-cal">327 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sausage Sandwich with Brown Sauce</div></td><td class="td-price">&pound;3.59</td><td class="td-cal">330 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Cheesy Bacon Flatbread</div></td><td class="td-price">&pound;3.59</td><td class="td-cal"><span class="low">280</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Pancakes &amp; Syrup</div></td><td class="td-price">&pound;3.59</td><td class="td-cal">464 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Pancakes &amp; Sausage with Syrup</div></td><td class="td-price">&pound;3.99</td><td class="td-cal">592 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Hash Brown</div></td><td class="td-price">&pound;1.89</td><td class="td-cal"><span class="low">127</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Porridge</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">154</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Porridge with Sugar</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">166</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Porridge with Strawberry Jam</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">189</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Porridge with Lyle's Golden Syrup&#174;</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">193</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Flahavan's&#174; Quick Oats&#174; (selected)</div></td><td class="td-price">&pound;1.79</td><td class="td-cal"><span class="low">143</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="bsaver" data-menu-category="bsaver">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127859;</span>
        <span class="menu-section-title">Breakfast Saver</span>
        <span class="menu-section-count">13 items</span>
      </div>
      <p class="menu-section-desc">Budget breakfast sandwiches, drinks and porridge options available before 11am.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Sausage Sandwich with Ketchup</div></td><td class="td-price">&pound;2.99</td><td class="td-cal">327 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sausage Sandwich with Brown Sauce</div></td><td class="td-price">&pound;2.99</td><td class="td-cal">330 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Cheesy Bacon Flatbread</div></td><td class="td-price">&pound;2.99</td><td class="td-cal"><span class="low">280</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Apple Slices</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">40</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Hash Brown</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">127</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">White Coffee</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">54</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Americano</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">6</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Tropicana&#174; Apple Juice</div></td><td class="td-price">&pound;1.29</td><td class="td-cal"><span class="low">101</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Tropicana&#174; Orange Juice</div></td><td class="td-price">&pound;1.29</td><td class="td-cal"><span class="low">94</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Porridge</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">154</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Porridge with Sugar</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">166</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Porridge with Strawberry Jam</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">189</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Porridge with Lyle's Golden Syrup&#174;</div></td><td class="td-price">&pound;1.99</td><td class="td-cal"><span class="low">193</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="vegetarian" data-menu-category="vegetarian">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127793;</span>
        <span class="menu-section-title">Vegetarian</span>
        <span class="menu-section-count">13 items</span>
      </div>
      <p class="menu-section-desc">Vegetarian-friendly mains, desserts and sides currently listed in the menu source.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">McPlant&#174;</div></td><td class="td-price">&pound;5.09</td><td class="td-cal">426 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Vegetable Deluxe</div></td><td class="td-price">&pound;4.89</td><td class="td-cal">361 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Veggie Dippers (4 pieces)</div></td><td class="td-price">&pound;4.79</td><td class="td-cal">321 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">The Spicy Veggie One</div></td><td class="td-price">&pound;4.99</td><td class="td-cal">365 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">McDonald's Fries (small)</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">237</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Side Salad</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">18</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Oreo&#174; McFlurry&#174; (regular)</div></td><td class="td-price">&pound;2.19</td><td class="td-cal">330 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Smarties&#174; McFlurry&#174; (regular)</div></td><td class="td-price">&pound;2.19</td><td class="td-cal">330 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Chocolate Brownie</div></td><td class="td-price">&pound;2.29</td><td class="td-cal">316 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Mixed Berry Muffin</div></td><td class="td-price">&pound;2.29</td><td class="td-cal"><span class="low">298</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Sugar Donut</div></td><td class="td-price">&pound;2.09</td><td class="td-cal"><span class="low">195</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Carrot Sticks</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">25</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Apple Slices</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">40</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="vegan" data-menu-category="vegan">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127807;</span>
        <span class="menu-section-title">Vegan</span>
        <span class="menu-section-count">5 items</span>
      </div>
      <p class="menu-section-desc">Current vegan-friendly picks including the McPlant, Veggie Dippers, fries and fruit or veg sides.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">McPlant&#174;</div></td><td class="td-price">&pound;5.09</td><td class="td-cal">426 kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">McDonald's Fries (small)</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">237</span> kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Carrot Sticks</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">25</span> kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Apple Slices</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">40</span> kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Veggie Dippers (4 pieces)</div></td><td class="td-price">&pound;4.79</td><td class="td-cal">321 kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="happymeal" data-menu-category="happymeal">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127881;</span>
        <span class="menu-section-title">Happy Meal</span>
        <span class="menu-section-count">6 items</span>
      </div>
      <p class="menu-section-desc">Current Happy Meal mains with their latest all-in prices and calories.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Mayo Chicken Happy Meal</div></td><td class="td-price">&pound;3.89</td><td class="td-cal">382 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Hamburger Happy Meal</div></td><td class="td-price">&pound;3.89</td><td class="td-cal">350 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Cheeseburger Happy Meal</div></td><td class="td-price">&pound;3.89</td><td class="td-cal">398 kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Chicken McNuggets&#174; 4 pieces Happy Meal</div></td><td class="td-price">&pound;3.89</td><td class="td-cal"><span class="low">274</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">McFish&#174; Happy Meal</div></td><td class="td-price">&pound;3.89</td><td class="td-cal"><span class="low">290</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Veggie Dippers 2 pieces Happy Meal</div></td><td class="td-price">&pound;3.89</td><td class="td-cal"><span class="low">197</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="mccafe" data-menu-category="mccafe">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#9749;</span>
        <span class="menu-section-title">McCaf&#233;</span>
        <span class="menu-section-count">15 items</span>
      </div>
      <p class="menu-section-desc">Hot and iced McCaf&#233; drinks, frapp&#233;s and smoothies from the latest UK menu.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Donut Crumble Latte</div></td><td class="td-price">&pound;2.99</td><td class="td-cal"><span class="low">220</span> kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span> <span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Toffee Latte</div></td><td class="td-price">&pound;1.69</td><td class="td-cal"><span class="low">150</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Flat White</div></td><td class="td-price">&pound;1.69</td><td class="td-cal"><span class="low">86</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Latte</div></td><td class="td-price">&pound;1.69</td><td class="td-cal"><span class="low">145</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Cappuccino</div></td><td class="td-price">&pound;1.69</td><td class="td-cal"><span class="low">97</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">White Coffee</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">54</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Americano</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">6</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Espresso</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">1</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Espresso Single</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">1</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Hot Chocolate</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">173</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Tea</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">6</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Iced Latte</div></td><td class="td-price">&pound;2.49</td><td class="td-cal"><span class="low">136</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Caramel Iced Frapp&#233;</div></td><td class="td-price">&pound;2.79</td><td class="td-cal"><span class="low">278</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Frozen Strawberry Lemonade</div></td><td class="td-price">&pound;2.49</td><td class="td-cal"><span class="low">190</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Mango &amp; Pineapple Smoothie</div></td><td class="td-price">&pound;2.79</td><td class="td-cal"><span class="low">162</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="desserts" data-menu-category="desserts">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127846;</span>
        <span class="menu-section-title">Desserts &amp; McFlurry</span>
        <span class="menu-section-count">12 items</span>
      </div>
      <p class="menu-section-desc">Current McFlurry flavours, pies, muffins, brownies and fruit desserts from the April 2026 menu.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Oreo&#174; McFlurry&#174; (regular)</div></td><td class="td-price">&pound;2.19</td><td class="td-cal">330 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Oreo&#174; McFlurry&#174; (mini)</div></td><td class="td-price">&pound;1.39</td><td class="td-cal"><span class="low">188</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Smarties&#174; McFlurry&#174; (regular)</div></td><td class="td-price">&pound;2.19</td><td class="td-cal">330 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Smarties&#174; McFlurry&#174; (mini)</div></td><td class="td-price">&pound;1.39</td><td class="td-cal"><span class="low">195</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Cadbury Creme Egg&#174; McFlurry&#174;</div></td><td class="td-price">&pound;2.49</td><td class="td-cal"><span class="low">290</span> kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span> <span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Cadbury Mini Eggs&#174; McFlurry&#174;</div></td><td class="td-price">&pound;2.49</td><td class="td-cal">310 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span> <span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Chocolate Brownie Pie</div></td><td class="td-price">&pound;2.49</td><td class="td-cal">320 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span> <span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Sugar Donut</div></td><td class="td-price">&pound;2.09</td><td class="td-cal"><span class="low">195</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Chocolate Brownie</div></td><td class="td-price">&pound;2.29</td><td class="td-cal">316 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Mixed Berry Muffin</div></td><td class="td-price">&pound;2.29</td><td class="td-cal"><span class="low">298</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Apple Pie</div></td><td class="td-price">&pound;1.89</td><td class="td-cal"><span class="low">243</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Apple Slices</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">40</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="drinks" data-menu-category="drinks">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127865;</span>
        <span class="menu-section-title">Drinks</span>
        <span class="menu-section-count">21 items</span>
      </div>
      <p class="menu-section-desc">Milkshakes, fizzy drinks, juice, smoothies and cold coffee drinks from the current menu source.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Chocolate Milkshake (small/med/large)</div></td><td class="td-price">from &pound;1.99</td><td class="td-cal"><span class="low">235</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Strawberry Milkshake (small/med/large)</div></td><td class="td-price">from &pound;1.99</td><td class="td-cal"><span class="low">230</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Banana Milkshake (small/med/large)</div></td><td class="td-price">from &pound;1.99</td><td class="td-cal"><span class="low">228</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Vanilla Milkshake (small/med/large)</div></td><td class="td-price">from &pound;1.99</td><td class="td-cal"><span class="low">225</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Cadbury Mini Eggs&#174; Frapp&#233;</div></td><td class="td-price">&pound;3.49</td><td class="td-cal">355 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span></td></tr>
          <tr><td><div class="td-name">Frozen Strawberry Lemonade</div></td><td class="td-price">&pound;2.49</td><td class="td-cal"><span class="low">190</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Iced Latte</div></td><td class="td-price">&pound;2.49</td><td class="td-cal"><span class="low">136</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Caramel Iced Frapp&#233;</div></td><td class="td-price">&pound;2.79</td><td class="td-cal"><span class="low">278</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Mango &amp; Pineapple Smoothie</div></td><td class="td-price">&pound;2.79</td><td class="td-cal"><span class="low">162</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Coca-Cola&#174; Zero Sugar</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">1</span> kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Diet Coke&#174;</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">1</span> kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Sprite&#174; Zero</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">1</span> kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Fanta&#174; Orange Zero</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">2</span> kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Oasis&#174; Summer Fruits Zero</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">48</span> kcal</td><td><span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Coca-Cola&#174; Classic</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">89</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">IRN-BRU&#174; (selected restaurants)</div></td><td class="td-price">&pound;1.09</td><td class="td-cal"><span class="low">68</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Organic Milk (selected restaurants)</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">130</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Tropicana&#174; Apple Juice</div></td><td class="td-price">&pound;1.29</td><td class="td-cal"><span class="low">101</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Tropicana&#174; Orange Juice</div></td><td class="td-price">&pound;1.29</td><td class="td-cal"><span class="low">94</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Robinsons&#174; Fruit Shoot</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">4</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Semi Skimmed Milk 250ml (selected)</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">120</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="sides" data-menu-category="sides">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#127839;</span>
        <span class="menu-section-title">Fries &amp; Sides</span>
        <span class="menu-section-count">7 items</span>
      </div>
      <p class="menu-section-desc">Fries in every size plus bites, salads and fruit or veg side options.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">McDonald's Fries Small</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">237</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">McDonald's Fries Medium</div></td><td class="td-price">&pound;1.79</td><td class="td-cal">337 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">McDonald's Fries Large</div></td><td class="td-price">&pound;1.99</td><td class="td-cal">444 kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Chilli Cheese Bites</div></td><td class="td-price">&pound;3.29</td><td class="td-cal">340 kcal</td><td><span class="avail-badge avail-limited-badge">Limited</span> <span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Side Salad</div></td><td class="td-price">&pound;1.19</td><td class="td-cal"><span class="low">18</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span></td></tr>
          <tr><td><div class="td-name">Pineapple Stick</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">20</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
          <tr><td><div class="td-name">Carrot Sticks</div></td><td class="td-price">&pound;0.99</td><td class="td-cal"><span class="low">25</span> kcal</td><td><span class="badge badge-yellow" style="font-size:10px;">Veg</span> <span class="badge badge-green" style="font-size:10px;">Vegan</span></td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="sharers" data-menu-category="sharers">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#128230;</span>
        <span class="menu-section-title">Sharers &amp; Bundles</span>
        <span class="menu-section-count">3 items</span>
      </div>
      <p class="menu-section-desc">Current share boxes and bigger-format bundles for groups or hungrier orders.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">The McDonald's Chicken Sharebox&#174;</div></td><td class="td-price">&pound;11.29</td><td class="td-cal"><span class="high">1,240</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">9 Chicken Selects Sharebox&#174;</div></td><td class="td-price">&pound;9.19</td><td class="td-cal"><span class="high">1,008</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">20 Chicken McNuggets&#174; Sharebox&#174;</div></td><td class="td-price">&pound;6.99</td><td class="td-cal"><span class="high">869</span> kcal</td><td>&mdash;</td></tr>
        </tbody>
      </table>
    </div>

    <div class="menu-section" id="sauces" data-menu-category="sauces">
      <div class="menu-section-head">
        <span class="menu-section-icon">&#129514;</span>
        <span class="menu-section-title">Condiments &amp; Sauces</span>
        <span class="menu-section-count">17 items</span>
      </div>
      <p class="menu-section-desc">Free dips, syrups and breakfast extras from the latest condiment list.</p>
      <table class="menu-table">
        <thead>
          <tr><th style="width:52%">Item</th><th>Price</th><th>Calories</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><div class="td-name">Spicy Chilli Dip (for Spicy McNuggets&#174;)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">55</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Rich Tomato Dip (25ml)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">20</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Tomato Ketchup (25ml)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">23</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">BBQ Dip (25ml)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">84</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sweet &amp; Sour Dip (25ml)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">51</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sweet Curry Dip (25ml)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">62</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Garlic Mayo Dip</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">95</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sweet Chilli Dip</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">89</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sweet &amp; Smoky BBQ Dip (40g)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">112</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">McDonald's Balsamic Dressing (30g)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">38</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Pancake Syrup (40g)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">94</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Strawberry Jam (15g)</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">37</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Lurpak Spreadable</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">72</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Milk Portion</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">14</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Canderel&#174; Yellow Sweetener Sachet</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">2</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Sugar Stick</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">20</span> kcal</td><td>&mdash;</td></tr>
          <tr><td><div class="td-name">Golden Syrup</div></td><td class="td-price">Free</td><td class="td-cal"><span class="low">59</span> kcal</td><td>&mdash;</td></tr>
        </tbody>
      </table>
    </div>

  </div>
</section>

<!-- DEALS SECTION -->
<section class="deals" id="deals">
  <div class="container">
    <div class="section-header" style="position:relative;z-index:1;">
      <div class="section-label" style="background: rgba(255,199,44,0.15); color: var(--yellow);">Live Deals</div>
      <h2 class="section-title" style="color: white;">McDonald's UK Value Picks 2026</h2>
      <p class="section-sub" style="color: rgba(255,255,255,0.55);">Current value-led menu options pulled from the attached April 2026 menu data.</p>
    </div>
    <div class="deals-grid">
      <div class="deal-card deal-red">
        <div class="deal-badge-top">&#11088; Best Value</div>
        <div class="deal-title">Meal Deal Plus</div>
        <div class="deal-sub">Burger, fries, drink and dessert bundle from the current Saver Menu listing.</div>
        <div class="deal-price">&pound;5.59 <span style="font-size:14px; opacity:0.6; font-weight:400;">Bundle</span></div>
        <div class="deal-emoji">&#127828;&#127839;</div>
      </div>
      <div class="deal-card deal-yellow">
        <div class="deal-badge-top">&#127991;&#65039; Saver Range</div>
        <div class="deal-title">Everyday Low-Cost Picks</div>
        <div class="deal-sub">White Coffee, Americano, Espresso, Apple Slices and Carrot Sticks all sit at 99p in the latest source.</div>
        <div class="deal-price">From &pound;0.99 <span style="font-size:14px; opacity:0.5; font-weight:400;">Current price</span></div>
        <div class="deal-emoji">&#128176;</div>
      </div>
      <div class="deal-card deal-dark">
        <div class="deal-badge-top">&#127859; Breakfast Saver</div>
        <div class="deal-title">Morning Value Menu</div>
        <div class="deal-sub">Breakfast Saver extras start at 99p, while hot sandwiches and flatbread sit from &pound;2.99 before 11am.</div>
        <div class="deal-price">From &pound;0.99 <span style="font-size:14px; opacity:0.5; font-weight:400;">Until 11am</span></div>
        <div class="deal-emoji">&#129374;</div>
      </div>
    </div>
  </div>
</section>

<!-- ORDERING METHODS -->
<section class="ordering-section">
  <div class="container">
    <div class="section-header">
      <div class="section-label">How to Order</div>
      <h2 class="section-title">Ways to Order at McDonald's UK</h2>
      <p class="section-sub">McDonald's UK offers multiple convenient ordering options &#8212; in-restaurant, on the go, or from the comfort of your home.</p>
    </div>
    <div class="ordering-grid">
      <div class="order-card">
        <span class="order-icon">&#128241;</span>
        <div class="order-title">McDonald's App</div>
        <div class="order-desc">Earn loyalty points, access app-only deals, mobile order, and skip the queue with Click &amp; Collect.</div>
      </div>
      <div class="order-card">
        <span class="order-icon">&#128421;&#65039;</span>
        <div class="order-title">Self-Service Kiosk</div>
        <div class="order-desc">Available in all UK restaurants. Customise your meal and pay by card &#8212; no waiting at the counter.</div>
      </div>
      <div class="order-card">
        <span class="order-icon">&#128663;</span>
        <div class="order-title">Drive-Thru</div>
        <div class="order-desc">Available 24/7 at over 1,000 UK locations. Pre-order on the app for faster drive-thru service.</div>
      </div>
      <div class="order-card">
        <span class="order-icon">&#128757;</span>
        <div class="order-title">McDelivery</div>
        <div class="order-desc">Available at 90% of UK restaurants. Order via McDonald's App, Uber Eats, Just Eat or Deliveroo. Delivery in 20&#8211;35 mins.</div>
      </div>
      <div class="order-card">
        <span class="order-icon">&#129489;&#8205;&#128187;</span>
        <div class="order-title">Counter Order</div>
        <div class="order-desc">Traditional counter service. Table delivery available at most modern McDonald's restaurants in the UK.</div>
      </div>
    </div>
  </div>
</section>

<!-- DELIVERY INFO -->
<section class="delivery-section">
  <div class="container">
    <div class="section-header">
      <div class="section-label">McDelivery &amp; App Deals</div>
      <h2 class="section-title">McDonald's UK Home Delivery &amp; Rewards</h2>
      <p class="section-sub">Get McDonald's delivered to your door and earn points every time you order through the app.</p>
    </div>
    <div class="delivery-grid">
      <div class="delivery-card">
        <div class="delivery-card-icon">&#128757;</div>
        <div>
          <div class="delivery-card-title">McDelivery via the McDonald's App</div>
          <div class="delivery-card-text">The official McDonald's UK App offers exclusive delivery deals and real-time order tracking. Earn loyalty points on every delivery order. Get 20% off your first McDelivery and access to personalised app-only offers and weekly vouchers.</div>
          <div class="delivery-platform-list">
            <span class="platform-chip">&#128241; McDonald's App</span>
            <span class="platform-chip">90% UK coverage</span>
          </div>
        </div>
      </div>
      <div class="delivery-card">
        <div class="delivery-card-icon">&#128230;</div>
        <div>
          <div class="delivery-card-title">Third-Party Delivery Platforms</div>
          <div class="delivery-card-text">Order McDonald's via Uber Eats, Just Eat, and Deliveroo. Average delivery time is 20&#8211;35 minutes, with delivery charges of &#163;2.99&#8211;&#163;4.99. Specially designed packaging maintains food temperature and quality during delivery.</div>
          <div class="delivery-platform-list">
            <span class="platform-chip">&#128994; Uber Eats</span>
            <span class="platform-chip">&#128309; Just Eat</span>
            <span class="platform-chip">&#128308; Deliveroo</span>
          </div>
        </div>
      </div>
      <div class="delivery-card">
        <div class="delivery-card-icon">&#11088;</div>
        <div>
          <div class="delivery-card-title">MyMcDonald's Rewards Programme</div>
          <div class="delivery-card-text">Earn 100 loyalty points for every &#163;1 spent via the McDonald's app. Redeem points for free coffees, burgers, desserts, and more. Frequent app users save an estimated &#163;8&#8211;&#163;12 per month. Points are earned on all app-based orders.</div>
          <div class="delivery-platform-list">
            <span class="platform-chip">100 pts per &#163;1</span>
            <span class="platform-chip">Free rewards</span>
            <span class="platform-chip">iOS &amp; Android</span>
          </div>
        </div>
      </div>
      <div class="delivery-card">
        <div class="delivery-card-icon">&#128176;</div>
        <div>
          <div class="delivery-card-title">How to Save Money at McDonald's UK</div>
          <div class="delivery-card-text">Use the McDonald's App for weekly digital vouchers and exclusive deals &#8212; sometimes up to 70% off. Order from the Saver Menu for items starting from just &#163;1.19. Visit on weekdays before 11 AM for budget breakfast options. Combine the Meal Deal Plus for the best all-round value at &#163;5.59.</div>
          <div class="delivery-platform-list">
            <span class="platform-chip">&#127991;&#65039; Saver from &#163;1.19</span>
            <span class="platform-chip">&#128241; App Deals</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CALORIES SECTION -->
<section class="calories-section" id="calories">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Nutrition Guide</div>
      <h2 class="section-title">McDonald's UK Calorie Guide 2026</h2>
      <p class="section-sub">Key calorie facts pulled directly from the attached April 2026 menu source.</p>
    </div>
    <div class="cal-grid">
      <div class="cal-card">
        <div class="cal-icon">&#129367;</div>
        <div class="cal-name">Lowest Calorie Food Item</div>
        <div class="cal-value">18 kcal</div>
        <div class="cal-label">Side Salad</div>
      </div>
      <div class="cal-card">
        <div class="cal-icon">&#127828;</div>
        <div class="cal-name">Highest Calorie Burger</div>
        <div class="cal-value">1,057 kcal</div>
        <div class="cal-label">Big Arch</div>
      </div>
      <div class="cal-card">
        <div class="cal-icon">&#9749;</div>
        <div class="cal-name">Lowest Calorie Hot Drink</div>
        <div class="cal-value">1 kcal</div>
        <div class="cal-label">Espresso</div>
      </div>
      <div class="cal-card">
        <div class="cal-icon">&#127846;</div>
        <div class="cal-name">Lowest Calorie McFlurry</div>
        <div class="cal-value">290 kcal</div>
        <div class="cal-label">Cadbury Creme Egg McFlurry</div>
      </div>
      <div class="cal-card">
        <div class="cal-icon">&#129374;</div>
        <div class="cal-name">Lowest Calorie Breakfast</div>
        <div class="cal-value">127 kcal</div>
        <div class="cal-label">Hash Brown</div>
      </div>
      <div class="cal-card">
        <div class="cal-icon">&#128230;</div>
        <div class="cal-name">Highest Calorie Sharer</div>
        <div class="cal-value">1,240 kcal</div>
        <div class="cal-label">McDonald's Chicken Sharebox</div>
      </div>
    </div>
  </div>
</section>

<!-- SEO CONTENT SECTION -->
<section class="seo-section">
  <div class="container">
    <div class="seo-layout">
      <div class="seo-content">
        <h2>McDonald's UK Menu Prices 2026 &mdash; Everything You Need to Know</h2>
        <p>The current McDonald's UK menu spans 202 listed items across 16 core categories in the attached April 2026 source. Core burger prices still place the Big Mac at &pound;5.09, while limited-time launches such as the Big Arch, Double Big Mac and Cadbury Creme Egg McFlurry push the menu forward with fresh seasonal choice.</p>
        <p>The latest April 2026 update mixes staples like the Quarter Pounder, Fries and McFlurry with short-run products such as Sausage &amp; Egg McGriddles, Spicy Chicken McNuggets, Cheesy Garlic Bread Dippers Sharebox and CARDS meal bundles.</p>
        <p>McDonald's breakfast hours in the UK still typically run from 5:00 AM to 11:00 AM, with the Breakfast Saver range giving the best morning value. The McDonald's UK App remains the quickest way to check location-specific pricing, live offers and restaurant opening times before you order.</p>
        <p>McDelivery is available across most UK locations through the McDonald's App plus third-party partners including Uber Eats, Just Eat and Deliveroo. Delivery charges vary by platform and location, and app users can still pair those orders with Rewards points and weekly vouchers.</p>
        <p><strong>Important:</strong> This is an independent, unofficial website. All prices are sourced from publicly available menus and may vary by location. Prices were last verified in April 2026.</p>
      </div>
      <div class="seo-sidebar">
        <div class="sidebar-title">Quick Links</div>
        <div class="sidebar-links">
          <a href="#whats-new" class="sidebar-link">&#127381; What's New April 2026</a>
          <a href="#burgers" class="sidebar-link">&#127828; All Burgers</a>
          <a href="#saver" class="sidebar-link">&#127991;&#65039; Saver Menu Prices</a>
          <a href="#breakfast" class="sidebar-link">&#129374; Breakfast Menu</a>
          <a href="#happymeal" class="sidebar-link">&#127881; Happy Meal Prices</a>
          <a href="#mccafe" class="sidebar-link">&#9749; McCaf&eacute; Prices</a>
          <a href="#desserts" class="sidebar-link">&#127846; Desserts &amp; McFlurry</a>
          <a href="#drinks" class="sidebar-link">&#127865; Drinks &amp; Milkshakes</a>
          <a href="#vegan" class="sidebar-link">&#127807; Vegan Options</a>
          <a href="#deals" class="sidebar-link">&#127991;&#65039; Latest Deals</a>
          <a href="#sharers" class="sidebar-link">&#128230; Sharers &amp; Bundles</a>
          <a href="#sauces" class="sidebar-link">&#129514; Condiments &amp; Sauces</a>
          <a href="#bsaver" class="sidebar-link">&#127859; Breakfast Saver</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOLIDAY HOURS -->
<section class="hours-section">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Opening Hours</div>
      <h2 class="section-title">McDonald's UK Holiday Hours 2026</h2>
      <p class="section-sub">McDonald's adjusts its operating hours during UK public holidays. Use the app to check your nearest restaurant's exact times.</p>
    </div>
    <div class="hours-wrapper">
      <div>
        <table class="hours-table">
          <thead>
            <tr><th>Holiday</th><th>Date 2026</th><th>Typical Hours</th><th>Notes</th></tr>
          </thead>
          <tbody>
            <tr><td>New Year's Day</td><td>1 Jan 2026</td><td><span class="hours-varies">Reduced hours</span></td><td>Drive-thru &amp; city locations may open longer</td></tr>
            <tr><td>Good Friday</td><td>3 Apr 2026</td><td><span class="hours-varies">Modified hours</span></td><td>Many locations close early in the evening</td></tr>
            <tr><td>Easter Sunday</td><td>5 Apr 2026</td><td><span class="hours-varies">Reduced hours</span></td><td>Reduced hours at most restaurants</td></tr>
            <tr><td>Easter Monday</td><td>6 Apr 2026</td><td><span class="hours-varies">Sunday trading</span></td><td>Follow Sunday schedule</td></tr>
            <tr><td>Early May Bank Holiday</td><td>4 May 2026</td><td><span class="hours-open">Normal/Extended</span></td><td>Most restaurants open standard hours</td></tr>
            <tr><td>Spring Bank Holiday</td><td>25 May 2026</td><td><span class="hours-open">Normal/Extended</span></td><td>Extended hours in tourist areas</td></tr>
            <tr><td>Summer Bank Holiday</td><td>31 Aug 2026</td><td><span class="hours-open">Extended</span></td><td>Coastal &amp; tourist areas often extend hours</td></tr>
            <tr><td>Christmas Eve</td><td>24 Dec 2026</td><td><span class="hours-varies">Reduced from 6pm</span></td><td>Many locations close in the evening</td></tr>
            <tr><td>Christmas Day</td><td>25 Dec 2026</td><td><span class="hours-closed">Mostly CLOSED</span></td><td>Only a few locations open with limited hours</td></tr>
            <tr><td>Boxing Day</td><td>26 Dec 2026</td><td><span class="hours-varies">Reduced hours</span></td><td>Drive-thru &amp; city centre locations typically open</td></tr>
            <tr><td>New Year's Eve</td><td>31 Dec 2026</td><td><span class="hours-varies">Modified hours</span></td><td>Some locations close earlier than usual</td></tr>
          </tbody>
        </table>
        <p style="font-size:13px;color:var(--grey-500);margin-top:14px;font-style:italic;">&#9888;&#65039; Hours are approximate. Always use the official McDonald's UK App or website to check your nearest restaurant's exact opening times.</p>
      </div>
      <div class="hours-tip-box">
        <div class="hours-tip-title">&#128205; Quick Tips</div>
        <div class="hours-tip-item"><span class="hours-tip-em">&#128241;</span> Use the <strong>McDonald's UK App</strong> store locator for real-time hours at any branch.</div>
        <div class="hours-tip-item"><span class="hours-tip-em">&#128663;</span> <strong>Drive-thru</strong> locations typically have extended hours on most holidays.</div>
        <div class="hours-tip-item"><span class="hours-tip-em">&#127961;&#65039;</span> <strong>City centre restaurants</strong> in London, Manchester &amp; Birmingham often stay open longer.</div>
        <div class="hours-tip-item"><span class="hours-tip-em">&#9992;&#65039;</span> <strong>Airport McDonald's</strong> follow airport hours and are open on most public holidays.</div>
        <div class="hours-tip-item"><span class="hours-tip-em">&#127876;</span> <strong>Christmas Day</strong> is the only day most UK McDonald's are fully closed.</div>
        <div class="hours-tip-item"><span class="hours-tip-em">&#9200;</span> Standard hours are typically <strong>6:00 AM &#8211; 11:00 PM</strong>, with many running <strong>24/7</strong>.</div>
      </div>
    </div>
  </div>
</section>


<!-- QUALITY INGREDIENTS -->
<section class="quality-section">
  <div class="container">
    <div class="section-header" style="position:relative;z-index:1;">
      <div class="section-label">Quality &amp; Sourcing</div>
      <h2 class="section-title">McDonald's UK Quality Ingredients</h2>
      <p class="section-sub">McDonald's UK sources quality ingredients from trusted British, Irish, and European farms &#8212; here's what goes into your meal.</p>
    </div>
    <div class="quality-grid">
      <div class="quality-card">
        <span class="quality-card-icon">&#128004;</span>
        <div class="quality-card-title">100% British &amp; Irish Beef</div>
        <div class="quality-card-text">All McDonald's UK beef burgers use 100% British and Irish beef with no fillers or additives. Sourced from trusted farms since 2002.</div>
      </div>
      <div class="quality-card">
        <span class="quality-card-icon">&#128020;</span>
        <div class="quality-card-title">Real Chicken Breast</div>
        <div class="quality-card-text">McNuggets and Chicken Selects use real chicken breast meat from UK and European farms. Recipes are regularly updated to reduce salt content.</div>
      </div>
      <div class="quality-card">
        <span class="quality-card-icon">&#129370;</span>
        <div class="quality-card-title">Free-Range Eggs</div>
        <div class="quality-card-text">McDonald's UK uses free-range eggs across its entire breakfast menu. All eggs are sourced from British farms meeting strict animal welfare standards.</div>
      </div>
      <div class="quality-card">
        <span class="quality-card-icon">&#9749;</span>
        <div class="quality-card-title">100% Arabica Coffee</div>
        <div class="quality-card-text">All McCaf&#233; hot drinks are made with sustainably sourced 100% Arabica coffee beans, freshly ground in each restaurant every day.</div>
      </div>
      <div class="quality-card">
        <span class="quality-card-icon">&#129364;</span>
        <div class="quality-card-title">Locally Grown Potatoes</div>
        <div class="quality-card-text">McDonald's fries are made from locally grown British potatoes. Cooked in vegetable oil &#8212; suitable for vegetarians and vegans.</div>
      </div>
      <div class="quality-card">
        <span class="quality-card-icon">&#128031;</span>
        <div class="quality-card-title">Sustainably Sourced Fish</div>
        <div class="quality-card-text">The Filet-o-Fish uses MSC certified, sustainably sourced fish. McDonald's works with responsible fishing suppliers to protect marine ecosystems.</div>
      </div>
      <div class="quality-card">
        <span class="quality-card-icon">&#129371;</span>
        <div class="quality-card-title">UK &amp; Northern Irish Dairy</div>
        <div class="quality-card-text">Milk for milkshakes, McCaf&#233; drinks, and desserts comes exclusively from UK and Northern Irish dairy farms with verified quality standards.</div>
      </div>
      <div class="quality-card">
        <span class="quality-card-icon">&#127807;</span>
        <div class="quality-card-title">Plant-Based &amp; Vegan Options</div>
        <div class="quality-card-text">The McPlant is developed with Beyond Meat and is fully vegan without cheese. McDonald's UK continues to expand its plant-based menu range.</div>
      </div>
    </div>
  </div>
</section>

<!-- AD BANNER -->
<div class="container" style="padding-top: 20px;">
  <div class="ad-banner">
    <span class="ad-banner-label">Advertisement</span>
    Post-Content Ad &#8212; 728&#215;90
  </div>
</div>


<!-- HISTORY & LOCATIONS -->
<section class="history-section">
  <div class="container">
    <div class="section-header">
      <div class="section-label">McDonald's in the UK</div>
      <h2 class="section-title">McDonald's UK History &amp; Locations</h2>
      <p class="section-sub">From a single restaurant in Woolwich in 1974 to 1,481 locations across Britain today.</p>
    </div>
    <div class="history-grid">
      <div>
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">1974</div>
            <div class="timeline-title">First UK Restaurant Opens</div>
            <div class="timeline-text">McDonald's opened its first UK restaurant in Woolwich, South East London &#8212; beginning a 50-year journey across Britain.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">1983</div>
            <div class="timeline-title">Chicken McNuggets Launch</div>
            <div class="timeline-text">McNuggets launched nationwide in 1983, becoming one of McDonald's most beloved menu items across all age groups.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">1998</div>
            <div class="timeline-title">McCaf&#233; Launched in the UK</div>
            <div class="timeline-text">McCaf&#233; expanded McDonald's into the coffee shop market, offering freshly ground Arabica coffee as an affordable alternative to high-street chains.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">2007</div>
            <div class="timeline-title">Calorie Labelling Introduced</div>
            <div class="timeline-text">McDonald's UK was one of the first major fast-food chains to display calorie information on packaging and in-store menus.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">2021</div>
            <div class="timeline-title">McPlant Launches Nationwide</div>
            <div class="timeline-text">The McPlant, developed with Beyond Meat, launched across the UK &#8212; becoming McDonald's first dedicated vegan burger option.</div>
          </div>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year">2026</div>
            <div class="timeline-title">Big Arch, McGriddles &amp; Spring Specials</div>
            <div class="timeline-text">April 2026 brought the Big Arch, Double Big Mac, Spicy Chicken McNuggets, the return of Sausage &amp; Egg McGriddles, and seasonal desserts including the Cadbury Creme Egg and Cadbury Mini Eggs McFlurry.</div>
          </div>
        </div>
      </div>
      <div class="stats-panel">
        <div class="stats-panel-title">&#128202; McDonald's UK in Numbers (2026)</div>
        <div class="stat-row"><span class="stat-row-label">Total UK Restaurants</span><span class="stat-row-value">1,481</span></div>
        <div class="stat-row"><span class="stat-row-label">Cities with McDonald's</span><span class="stat-row-value">773</span></div>
        <div class="stat-row"><span class="stat-row-label">England Restaurants</span><span class="stat-row-value">~85%</span></div>
        <div class="stat-row"><span class="stat-row-label">UK Employees</span><span class="stat-row-value">180,000+</span></div>
        <div class="stat-row"><span class="stat-row-label">Daily UK Customers</span><span class="stat-row-value">4 million+</span></div>
        <div class="stat-row"><span class="stat-row-label">Drive-Thru Locations</span><span class="stat-row-value">1,000+</span></div>
        <div class="stat-row"><span class="stat-row-label">McDelivery Coverage</span><span class="stat-row-value">90%</span></div>
        <div class="stat-row"><span class="stat-row-label">Menu Items</span><span class="stat-row-value">202</span></div>
        <div class="stat-row"><span class="stat-row-label">First UK Restaurant</span><span class="stat-row-value">1974</span></div>
      </div>
    </div>
  </div>
</section>

<!-- INTERNAL LINKS GRID -->
<section class="internal-links" id="blog">
  <div class="container">
    <div class="section-header">
      <div class="section-label">Explore More</div>
      <h2 class="section-title">In-Depth Price Guides</h2>
      <p class="section-sub">Deep-dive guides for the main sections of the McDonald's UK menu.</p>
    </div>
    <div class="links-grid">
      <a href="#burgers" class="link-card">
        <div class="link-card-emoji">&#127828;</div>
        <div class="link-card-title">Big Mac Price History</div>
        <div class="link-card-text">Track Big Mac pricing and compare it with the rest of the burger range.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#breakfast" class="link-card">
        <div class="link-card-emoji">&#129374;</div>
        <div class="link-card-title">Full Breakfast Menu</div>
        <div class="link-card-text">Every breakfast item with current prices, calories and the latest morning specials.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#nuggets" class="link-card">
        <div class="link-card-emoji">&#127831;</div>
        <div class="link-card-title">McNuggets Guide</div>
        <div class="link-card-text">Prices for nuggets, selects, dippers and share boxes from the live 2026 source.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#deals" class="link-card">
        <div class="link-card-emoji">&#128241;</div>
        <div class="link-card-title">McDonald's App Deals</div>
        <div class="link-card-text">Where the latest value picks sit, from Meal Deal Plus to app-led bundles and 99p add-ons.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#vegan" class="link-card">
        <div class="link-card-emoji">&#127807;</div>
        <div class="link-card-title">Vegan Options Guide</div>
        <div class="link-card-text">Current vegan-friendly mains, fries, sides and drinks in one place.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#mccafe" class="link-card">
        <div class="link-card-emoji">&#9749;</div>
        <div class="link-card-title">McCaf&eacute; Price List</div>
        <div class="link-card-text">Every McCaf&eacute; coffee and hot drink with current UK prices.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#happymeal" class="link-card">
        <div class="link-card-emoji">&#127881;</div>
        <div class="link-card-title">Happy Meal Prices</div>
        <div class="link-card-text">Happy Meal options and current pricing for 2026.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#desserts" class="link-card">
        <div class="link-card-emoji">&#127846;</div>
        <div class="link-card-title">Desserts &amp; McFlurry</div>
        <div class="link-card-text">Current McFlurry flavours, pies, muffins and seasonal sweet picks.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#wraps" class="link-card">
        <div class="link-card-emoji">&#127791;</div>
        <div class="link-card-title">Wraps &amp; Salads Guide</div>
        <div class="link-card-text">Every wrap and salad with current prices, calories and limited-time variants.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#drinks" class="link-card">
        <div class="link-card-emoji">&#127865;</div>
        <div class="link-card-title">Drinks &amp; Milkshakes</div>
        <div class="link-card-text">Cold drinks, milkshakes, smoothies, frapp&eacute;s and juice all in one guide.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#saver" class="link-card">
        <div class="link-card-emoji">&#127991;&#65039;</div>
        <div class="link-card-title">Saver Menu Breakdown</div>
        <div class="link-card-text">Every item on the Saver Menu with the latest low-price 2026 update.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#sharers" class="link-card">
        <div class="link-card-emoji">&#128230;</div>
        <div class="link-card-title">Sharers &amp; Bundles Guide</div>
        <div class="link-card-text">Everything in the Sharers &amp; Bundles range with full prices and calories.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#sauces" class="link-card">
        <div class="link-card-emoji">&#129514;</div>
        <div class="link-card-title">Condiments &amp; Sauces</div>
        <div class="link-card-text">Every dip, syrup and condiment with calories from the latest menu source.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#bsaver" class="link-card">
        <div class="link-card-emoji">&#127859;</div>
        <div class="link-card-title">Breakfast Saver Guide</div>
        <div class="link-card-text">Budget breakfast options and drinks before 11am, from 99p up to the hot sandwiches.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
      <a href="#vegetarian" class="link-card">
        <div class="link-card-emoji">&#127793;</div>
        <div class="link-card-title">Vegetarian Picks</div>
        <div class="link-card-text">A single place for the full vegetarian-friendly menu, including desserts and sides.</div>
        <div class="link-card-arrow">&#8594;</div>
      </a>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="faq-section" id="faq">
  <div class="container">
    <h2 class="section-title">McDonald's UK Menu Prices - Frequently Asked Questions</h2>
    <div class="faq-list">
      <details class="faq-item">
        <summary class="faq-q">How much is a Big Mac in the UK?<span class="faq-icon">+</span></summary>
        <div class="faq-a"><p>A Big Mac costs &pound;5.09 in this April 2026 update. Meal prices can vary a little by restaurant, so the McDonald's app is the best place to confirm your local price.</p></div>
      </details>
      <details class="faq-item">
        <summary class="faq-q">What is on the McDonald's Saver Menu UK?<span class="faq-icon">+</span></summary>
        <div class="faq-a"><p>The Saver Menu includes lower-priced picks such as Hamburger (&pound;1.19), Cheeseburger (&pound;1.39), Mayo Chicken (&pound;1.39), Double Cheeseburger (&pound;2.29), 99p drinks and value add-ons, with Meal Deal Plus listed at &pound;5.59.</p></div>
      </details>
      <details class="faq-item">
        <summary class="faq-q">How much is a Happy Meal in the UK?<span class="faq-icon">+</span></summary>
        <div class="faq-a"><p>Most Happy Meal options are &pound;3.89 in the current April 2026 menu, including Hamburger, Cheeseburger, Mayo Chicken and 4-piece Chicken McNuggets Happy Meals.</p></div>
      </details>
      <details class="faq-item">
        <summary class="faq-q">What time does McDonald's serve breakfast in the UK?<span class="faq-icon">+</span></summary>
        <div class="faq-a"><p>McDonald's UK breakfast is typically served from 5:00 AM until 11:00 AM. After 11:00 AM, the main daytime menu takes over.</p></div>
      </details>
      <details class="faq-item">
        <summary class="faq-q">How many calories are in McDonald's large fries?<span class="faq-icon">+</span></summary>
        <div class="faq-a"><p>A large fries has 444 kcal in the current menu data.</p></div>
      </details>
      <details class="faq-item">
        <summary class="faq-q">Does McDonald's UK have a vegan burger?<span class="faq-icon">+</span></summary>
        <div class="faq-a"><p>Yes. McDonald's UK has the McPlant, which is listed at &pound;5.09 and 426 kcal in the current menu data.</p></div>
      </details>
      <details class="faq-item">
        <summary class="faq-q">How much is a McFlurry in the UK?<span class="faq-icon">+</span></summary>
        <div class="faq-a"><p>Regular Oreo and Smarties McFlurry flavours are &pound;2.19, while mini versions are &pound;1.39. Seasonal flavours such as Cadbury Creme Egg and Cadbury Mini Eggs McFlurry are &pound;2.49.</p></div>
      </details>
      <details class="faq-item">
        <summary class="faq-q">What is the cheapest item on the McDonald's UK menu?<span class="faq-icon">+</span></summary>
        <div class="faq-a"><p>The cheapest current items are 99p picks such as White Coffee, Americano, Espresso, Apple Slices, Pineapple Stick and Carrot Sticks. The cheapest burger is the Hamburger at &pound;1.19.</p></div>
      </details>
    </div>
  </div>
</section>

<!-- DISCLAIMER BANNER -->
<div class="disclaimer">
  <p>&#9888;&#65039; <strong>Disclaimer:</strong> This is an independent, unofficial website. McPrices UK is not affiliated with, endorsed by, or connected to McDonald's Corporation or McDonald's UK Ltd in any way. All prices are sourced from publicly available menus and may vary by location and date. This site may contain advertisements.</p>
</div>

<!-- FOOTER -->


<!-- SCROLL TO TOP -->
<!-- /wp:html -->
</div>
<!-- /wp:group -->
HTML;
