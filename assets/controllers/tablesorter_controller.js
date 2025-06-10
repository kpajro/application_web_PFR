import { Controller } from "@hotwired/stimulus";

/**
 * controller js pour initialiser tablesorter pour l'utiliser où on veut 
 * (sert à gérer les tableaux)
 */
export default class extends Controller {
    /**
     * fonction lancée au chargement qui initialise tablesorter en utilisant jquery
     */
    connect() {
        const $ = require('jquery');

        $(".tablesorter-init").tablesorter({
  		    widthFixed: false,
  		    headerTemplate : '{content} {icon}',
  		    widgets : ["zebra", "filter", "stickyHeaders"],
  		    widgetOptions : {
  		        filter_cssFilter: "input",
                filter_saveFilters : true,
                stickyHeaders_offset : 0,
                stickyHeaders_filteredToTop: true,
                stickyHeaders_cloneId : '-sticky',
                zebra : [ "normal-row", "alt-row" ],
            }
        });
    }
}