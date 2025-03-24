import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        const $ = require('jquery');

        $(".tablesorter-init").tablesorter({
  		    widthFixed: true,
  		    headerTemplate : '{content} {icon}',
  		    widgets : ["zebra", "filter", "stickyHeaders"],
  		    widgetOptions : {
  		        filter_cssFilter: "input",
                filter_saveFilters : true,
                filter_reset : "#reset",
                filter_placeholder: 'recherchez',
                stickyHeaders_offset : 0,
                stickyHeaders_filteredToTop: true,
                stickyHeaders_cloneId : '-sticky',
                zebra : [ "normal-row", "alt-row" ],
            }
        });
    }
}