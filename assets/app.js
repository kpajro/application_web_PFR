import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './bootstrap.js';
import './styles/app.css';
import 'tablesorter';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
document.addEventListener('DOMContentLoaded', (event) => {
  document.querySelectorAll('.ckeditor').forEach((element) => {
    ClassicEditor
      .create(element)
      .catch(error => {
        console.error(error);
      });
  });
});

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
