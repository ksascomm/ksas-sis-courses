/**
 * File courses.js.
 *
 * Customized scripts for SIS Courses Page Template.
 */

jQuery(document).ready(function ($) {
  // Recalculate column widths when switching Bootstrap tabs.
  $('a[aria-selected="true"], button[data-bs-toggle="tab"]').on(
    "shown.bs.tab",
    function () {
      if ($.fn.dataTable && $.fn.dataTable.tables) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
      }
    },
  );

  // Safely resolve the DataTables Responsive renderer for v2/v3 compatibility.
  var responsiveRenderer = null;
  if (typeof DataTable !== "undefined" && DataTable.Responsive) {
    responsiveRenderer = DataTable.Responsive.renderer.listHiddenNodes();
  } else if ($.fn.dataTable && $.fn.dataTable.Responsive) {
    responsiveRenderer = $.fn.dataTable.Responsive.renderer.listHiddenNodes();
  }

  // Initialize Course Listing Table.
  $("table.course-table").DataTable({
    responsive: responsiveRenderer
      ? { details: { renderer: responsiveRenderer } }
      : true,
    order: [[0, "asc"]],
    lengthMenu: [
      [15, 30, -1],
      [15, 30, "All"],
    ],
    layout: {
      top1: "searchPanes",
      topStart: "pageLength",
      topEnd: "search",
      bottomStart: "info",
      bottomEnd: "paging",
    },
    language: {
      emptyTable:
        "Courses have a status of Closed, or are unavailable at this time. Please try again later.",
      searchPanes: {
        emptyPanes: null,
      },
    },
    searchPanes: {
      preSelect: [
        {
          column: 5,
          rows: ["Spring 2027"],
        },
      ],
    },
    columnDefs: [
      {
        searchPanes: {
          show: false,
        },
        targets: [0, 1, 2, 3, 4, 6],
      },
    ],
  });
});
