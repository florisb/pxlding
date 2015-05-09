process.env.DISABLE_NOTIFIER = true;

var gulp         = require('gulp');
var gutil        = require('gulp-util');
var minifycss    = require('gulp-minify-css');
var autoprefixer = require('gulp-autoprefixer');
var notify       = require('gulp-notify');
var sass         = require('gulp-ruby-sass');


var scssDir      = 'webroot/scss';
var targetCssDir = 'webroot/css';

var jsDir        = 'webroot/js/src';
var targetJsDir  = 'webroot/js';


gulp.task('css', function () {
	return sass(scssDir + '/default.scss', { style: 'compressed' })
			// .on('error', gutil.log))
		.pipe(autoprefixer('last 15 version'))
		.pipe(minifycss())
		.pipe(gulp.dest(targetCssDir))
		.pipe(notify('done.'));
});


// gulp.task('js', function () {
// 	// browserify
// });


// Rerun the task when a file changes
gulp.task('watch', function() {
	gulp.watch(scssDir + '/**/*.scss', ['css']);
});


gulp.task('default', ['watch']);