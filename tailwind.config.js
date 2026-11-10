/**
 * Tailwind CSS 3.4 untuk SIMAGANG. Hasil build (public/css/tailwind.css) ikut di-commit, jadi server tidak perlu Node.
 * Bangun ulang setelah menambah/mengubah kelas Tailwind di view atau JS:  npm install  lalu  npm run build:css
 *
 * Kelas yang dibentuk dinamis di kode (mis. 'bg-' + warna) tidak terbaca pemindai; daftarkan di `safelist`.
 */
export default {
    content: [
        './resources/views/**/*.php',
        './public/js/**/*.js',
        './app/**/*.php',
        // tampilan paginasi bawaan Laravel memakai kelas Tailwind
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.php',
    ],
    safelist: [],
    theme: { extend: {} },
    plugins: [],
};
