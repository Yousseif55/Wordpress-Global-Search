jQuery(document).ready(function() {
  
  if (typeof codecruze_full_menu != 'undefined') {
      jQuery('.ui.search')
        .search({
          source: codecruze_full_menu,
          type          : 'category',
          selectFirstResult: true,
          fullTextSearch: 'exact',
          cache: true,
          searchFields   : [
                'title',
                'category',
                'ID',
                'description'
              ],
          minCharacters: 3, 
          maxResults : false,
        });
  }
});

codecruze_shortcut.add("Ctrl+F",function() {
  jQuery('#codecruze_search_box').focus();
})

