jQuery(function ($) {
  class Search {
    // init
    constructor() {
      this.searchOverlay = $("section#search");
      this.searchInput = $("#input#search-input");
      this.menuBtn = $("#open-search-overlay");
      this.closeBtn = $("span#close-search-overlay");
    }

    // eventHandler
    eventHandlers() {
      this.menuBtn.on("click", this.openSearchOverlay.bind(this));
      this.closeBtn.on("click", this.closeSearchOverlay.bind(this));
    }

    // Methods
    openSearchOverlay() {}

    closeSearchOverlay() {
      console.log("hiding");
      this.searchOverlay.hide();
    }
  }
});

const search = new Search();
