import path from 'path';
import vue from '@vitejs/plugin-vue';

export default ({ command }) => ({
  base: command === 'serve' ? '' : '/build/',
  publicDir: 'fake_dir_so_nothing_gets_copied',
  resolve: {
    alias: {
      '@r': path.resolve('resources'),
      '@': path.resolve('resources/js'),
    },
  },
  build: {
    manifest: true,
    target: 'es2015',
    outDir: 'public/build',
    rollupOptions: {
      input: 'resources/js/app.js',
    },
  },
  plugins: [vue()],
});
