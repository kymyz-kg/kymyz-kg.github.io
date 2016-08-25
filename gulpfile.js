/*!
 * gulp
 * $ npm install gulp gulp-htmlmin gulp-autoprefixer gulp-cssnano jshint gulp-jshint gulp-concat gulp-uglify gulp-imagemin gulp-notify gulp-rename gulp-cache del --save-dev
 */

// Load plugins
var gulp = require('gulp'),
    autoprefixer = require('gulp-autoprefixer'),
    htmlmin = require('gulp-htmlmin'),
    cssnano = require('gulp-cssnano'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    imagemin = require('gulp-imagemin'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cache = require('gulp-cache'),
    del = require('del');

// Html minify
gulp.task('htmlmin', function() {
    return gulp.src('./main.html')
        .pipe(htmlmin({collapseWhitespace: true}))
        .pipe(rename('./index.html'))
        .pipe(gulp.dest('./'))
        .pipe(notify({ message: 'Html minifying task complete' }));
});

// Styles
gulp.task('styles', function() {
    return gulp.src('src/styles/**/*.css')
        .pipe(cssnano())
        .pipe(autoprefixer('last 2 version'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(gulp.dest('dist/styles'))
        .pipe(notify({ message: 'Styles task complete' }));
});

// Scripts
gulp.task('scripts', function() {
    return  gulp.src('src/scripts/**/*.js')
        .pipe(jshint())
        .pipe(jshint.reporter('default'))
        .pipe(concat('main.js'))
        .pipe(gulp.dest('dist/scripts'))
        .pipe(rename({ suffix: '.min' }))
        .pipe(uglify())
        .pipe(gulp.dest('dist/scripts'))
        .pipe(notify({ message: 'Scripts task complete' }));
});

// Images
gulp.task('images', function() {
    return gulp.src('src/images/**/*')
        .pipe(cache(imagemin({ optimizationLevel: 3, progressive: true, interlaced: true })))
        .pipe(gulp.dest('dist/images'))
        .pipe(notify({ message: 'Images task complete' }));
});

// Clean
gulp.task('clean', function() {
    return del(['index.html', 'dist/styles', 'dist/scripts', 'dist/images']);
});

// Default task
gulp.task('default', ['clean'], function() {
    gulp.start('htmlmin', 'styles', 'scripts', 'images');
});

// Watch
gulp.task('watch', function() {

    // Watch main.html file
    gulp.watch('main.html', ['htmlmin']);

    // Watch .css files
    gulp.watch('src/styles/**/*.css', ['styles']);

    // Watch .js files
    gulp.watch('src/scripts/**/*.js', ['scripts']);

    // Watch image files
    gulp.watch('src/images/**/*', ['images']);

    gulp.watch('index.html').on('change', function(){ 
        return gulp.src("index.html")
            .pipe(notify("Dist files are changed"));
        });
});
