import 'prismjs/themes/prism.min.css';
import Prism from 'prismjs';
import 'prismjs/plugins/autoloader/prism-autoloader.min.js';

// Configure autoloader to load additional languages from CDN by default
// but since we bundle, keep default path; if needed, set window.Prism.plugins.autoloader.languages_path
// Expose Prism globally for inline usage
// @ts-ignore
window.Prism = Prism;
export default Prism;

