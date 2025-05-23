// assets/js/main.js

// --- Imports gérés par Vite (CSS, autres librairies JS, vos scripts ES6 natifs) ---
import '../scss/main.scss'; // Vos styles globaux
import './swiper.js'; // Votre initialisation Swiper
import './kb-scripts.js'; // Vos scripts personnalisés non-jQuery

// --- Importer et exécuter le point d'entrée pour la logique jQuery ---
// Cela suppose que jQuery est déjà chargé globalement par WordPress.
// L'import lui-même n'exécute rien, c'est le contenu de main-search.js (son $(document).ready) qui le fera.
import './main-search.js';
