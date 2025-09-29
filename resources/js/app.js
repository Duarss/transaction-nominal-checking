import './bootstrap';

import AutoNumeric from 'autonumeric';
window.AutoNumeric = AutoNumeric;

// resources/js/app.js
import $ from 'jquery';
window.$ = window.jQuery = $;

// DataTables core + Bootstrap 5 styling
import 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import 'datatables.net-buttons-bs5';

// (optional) Buttons HTML5/print if your Kirana tools use them
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';

// CSS (either here or in app.scss)
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css';
import 'datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css';
