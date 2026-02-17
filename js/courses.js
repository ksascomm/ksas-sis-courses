/**
 * File courses.js.
 *
 * Customized scripts for SIS Courses Page Template. Trigger modal and Data Tables for course listings.
 */

// Initialisation script
jQuery(document).ready( function($) {
    $('a[aria-selected="true"]').on( 'shown.bs.tab', function (e) {
        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
    } );

    $('table.course-table').DataTable( {
        responsive: {
            details: {
            // This ensures your complex HTML inside the 'none' column 
            // is moved into the child row rather than just copied as text.
            renderer: DataTable.Responsive.renderer.listHiddenNodes()
            }
        },
        "order": [[ 0, "asc" ]],
        "lengthMenu": [[15, 30, -1],[15, 30, "All"]],
        "layout": {
            top1: 'searchPanes',
            topStart: 'pageLength',
            topEnd: 'search',
            bottomStart: 'info',
            bottomEnd: 'paging'
        },
        //"dom": 'Plfrtip',
        "language": {
            "emptyTable": "Courses have a status of Closed, or are unavailable at this time. Please try again later.",
            searchPanes: {
                emptyPanes: null,
            }
        },
        searchPanes: {
            preSelect: [
                {
                    column: 5,
                    rows:['Spring 2026']
                }
            ],
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