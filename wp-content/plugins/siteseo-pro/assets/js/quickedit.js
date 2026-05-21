jQuery(document).ready(function($) {
    if (typeof inlineEditPost === 'undefined') {
        return;
    }

    var $wp_inline_edit = inlineEditPost.edit;
    
    inlineEditPost.edit = function(id) {
        // "call" the original WP edit function
        // we don't want to lose this
        $wp_inline_edit.apply(this, arguments);

        // now we take care of our business
        var $post_id = 0;
        if (typeof(id) == 'object') {
            $post_id = parseInt(this.getId(id));
        }

        if ($post_id > 0) {
            var $edit_row = $('#edit-' + $post_id);
            var $data_div = $('#post-' + $post_id).find('.siteseo-quickedit-data');

            // If the fieldset doesn't exist in this row yet, clone it from our template
            if ($edit_row.find('.inline-edit-siteseo-pro').length === 0) {
                var $template = $('#siteseo-pro-quick-edit-template .inline-edit-siteseo-pro').clone();
                // Append directly before the Update/Cancel buttons
                $template.insertBefore($edit_row.find('.submit').last());
            }

            if($data_div.length > 0) {
                var title = $data_div.data('title');
                var desc = $data_div.data('desc');
                var canonical = $data_div.data('canonical');
                var permalink = $data_div.data('permalink');
                var target_kw = $data_div.data('target-kw');
                var index = $data_div.data('index');
                var follow = $data_div.data('follow');
                var imageindex = $data_div.data('imageindex');
                var archive = $data_div.data('archive');
                var snippet = $data_div.data('snippet');
                var primary_cat = $data_div.data('primary-cat');

                $edit_row.find('input[name="_siteseo_titles_title"]').val(title);
                $edit_row.find('textarea[name="_siteseo_titles_desc"]').val(desc);
                $edit_row.find('input[name="_siteseo_analysis_target_kw"]').val(target_kw);
                
                var $canonical_input = $edit_row.find('input[name="_siteseo_robots_canonical"]');
                $canonical_input.val(canonical);
                if(permalink && permalink !== 'undefined') {
                    $canonical_input.attr('placeholder', permalink);
                }
                
                var is_noindex = (index === 'yes' || index === true);
                $edit_row.find('input[name="_siteseo_robots_index"]').prop('checked', is_noindex);

                $edit_row.find('input[name="_siteseo_robots_follow"]').prop('checked', (follow === 'yes' || follow === true));
                $edit_row.find('input[name="_siteseo_robots_imageindex"]').prop('checked', (imageindex === 'yes' || imageindex === true));
                $edit_row.find('input[name="_siteseo_robots_archive"]').prop('checked', (archive === 'yes' || archive === true));
                $edit_row.find('input[name="_siteseo_robots_snippet"]').prop('checked', (snippet === 'yes' || snippet === true));

                if (primary_cat && primary_cat !== 'undefined' && primary_cat !== '') {
                    $edit_row.find('select[name="_siteseo_robots_primary_cat"]').val(primary_cat);
                } else {
                    $edit_row.find('select[name="_siteseo_robots_primary_cat"]').val('none');
                }
            }
        }
    };

    // Removed visual Index/No Index syncing since Index was removed from UI

});
