var gulp = require('gulp');
var uglify = require('gulp-uglify');
var livereload = require('gulp-livereload');

// Автообновление
gulp.task('default', function () {
  livereload.listen();
  gulp.watch('../files/*.css').on('change', livereload.changed);
  gulp.watch('../files/*.js').on('change', livereload.changed);
  gulp.watch('../layer/*.php').on('change', livereload.changed);
  gulp.watch('../templates/**/*.php').on('change', livereload.changed);
  gulp.watch('../classes/**/*.php').on('change', livereload.changed);
  gulp.watch('../errors/*.html').on('change', livereload.changed);
  gulp.watch('../src/js/*.js', ['compress-js']);
});

// JS compress
gulp.task('compress-js', function () {
  // gulp.src('../src/js/*.js')
    // .pipe(uglify())
    // .pipe(gulp.dest('../files'));
});