/**
 * File courses.js.
 *
 * Customized scripts for SIS Courses Page Template. Trigger modal and Data Tables for course listings.
 */

// Get the button that opens the modal
var btn = document.querySelectorAll("button.modal-button");

// All page modals
var modals = document.querySelectorAll('.modal');

// Get the <span> element that closes the modal
var spans = document.getElementsByClassName("close");

// When the user clicks the button, open the modal
for (var i = 0; i < btn.length; i++) {
    btn[i].onclick = function(e) {
        e.preventDefault();
        modal = document.querySelector(e.target.getAttribute("href"));
        modal.style.display = "block";
    }
}

// When the user clicks on <span> (x), close the modal
for (var i = 0; i < spans.length; i++) {
    spans[i].onclick = function() {
        for (var index in modals) {
            if (typeof modals[index].style !== 'undefined') modals[index].style.display = "none";
        }
    }
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        for (var index in modals) {
            if (typeof modals[index].style !== 'undefined') modals[index].style.display = "none";
        }
    }
}

//Data Tables scripts
/*jQuery(document).ready(function($) {
    $('a[aria-selected="true"]').on('shown.bs.tab', function(e) {
        $.fn.dataTable.tables({
            visible: true,
            api: true
        }).columns.adjust();
    });

    $('table.course-table').DataTable({
        "order": [
            [0, "asc"]
        ],
        "lengthMenu": [ [-1, 25, 50, 75], ["All", 25, 50, 75] ],
        //"paging": false,
        "dom": '<"top"f>ilrt<"bottom"p><"clear">',
        "language": {
            "emptyTable": "Courses have a status of Closed, or are unavailable at this time. Please try again later."
        }
    });
});*/

jQuery(document).ready( function($) {
    $('a[aria-selected="true"]').on( 'shown.bs.tab', function (e) {
        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
    } );

    $('table.course-table').DataTable( {
        "order": [[ 0, "asc" ]],
        "lengthMenu": [[15, 30, -1],[15, 30, "All"]],
        "dom": 'Plfrtip',
        "language": {
            "emptyTable": "Courses have a status of Closed, or are unavailable at this time. Please try again later.",
            searchPanes: {
                emptyPanes: null,
            }
        },
        searchPanes: {
            preSelect: [{
                rows:['Fall 2024'],
                column: 5
            }],
        },
        columnDefs: [
        {
            //This hides all but Term pane from search/filter
            searchPanes: {
                show: false
            },
            targets: [0,1,2,3,4,6]
        }
        ]
    } );
} );