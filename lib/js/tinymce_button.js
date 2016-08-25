jQuery(document).ready(function($) {

    tinymce.create('tinymce.plugins.msd_easy_date_plugin', {
        init : function(ed, url) {
                // Register command for when button is clicked
                ed.addCommand('msd_easy_date_insert_shortcode', function() {
                    selected = tinyMCE.activeEditor.selection.getContent();
                    date1 = new Date();
                    date = date1.getDate();
                    year = date1.getFullYear();
                    month = date1.getMonth();
                    months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
                    start = months[parseInt(month)]+' '+date+', '+year;
                    end = months[parseInt(month)+1]+' '+date+', '+year;
                    if( selected ){
                        //If text is selected when button is clicked
                        //Wrap shortcode around it.
                        content =  '[easy-date start="'+start+'" end="'+end+'"]'+selected+'[/easy-date]';
                    }else{
                        content =  '[easy-date start="'+start+'" end="'+end+'"]Insert dated content here[/easy-date]';
                    }

                    tinymce.execCommand('mceInsertContent', false, content);
                });

            // Register buttons - trigger above command when clicked
            ed.addButton('msd_easy_date_button', {title : 'Insert Easy Date', cmd : 'msd_easy_date_insert_shortcode', icon: 'calendar' });
        },   
    });

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('msd_easy_date_button', tinymce.plugins.msd_easy_date_plugin);
});