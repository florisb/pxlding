process.env.DISABLE_NOTIFIER = true;

var gulp         = require('gulp');
var gutil        = require('gulp-util');
var notify       = require('gulp-notify');
var sourcemaps   = require('gulp-sourcemaps');

var jshint       = require('gulp-jshint');
var browserify   = require('gulp-browserify');
var uglify       = require('gulp-uglify');

var sass         = require('gulp-sass');
var minifycss    = require('gulp-minify-css');
var autoprefixer = require('gulp-autoprefixer');
// var concat       = require('gulp-concat');


var scssDir      = 'webroot/scss';
var targetCssDir = 'webroot/css';

var jsDir            = 'webroot/js/src';
var targetJsDevDir   = 'webroot/js/_dev';
var targetJsLiveDir  = 'webroot/js/_live';


gulp.task('css', function () {
	gulp.src([
			scssDir + '/default.scss',
			scssDir + '/content/**/*.scss'
		])
		.pipe(sourcemaps.init())
		.pipe(sass({
			style: 'compressed',
			errLogToConsole: true
		}))
        .on('error', function (error) {
            console.error(error);
            this.emit('end');
        })
		.pipe(autoprefixer('last 15 version'))
		.pipe(minifycss())
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest(targetCssDir))
		.pipe(notify('CSS done.'));
});


gulp.task('js', function () {

	// hint everything in src
	gulp.src(jsDir + '/**/*.js')
		.pipe(jshint())
		.pipe(jshint.reporter('default'))
		.pipe(jshint.reporter('fail'));

	// first browserify to dev
	gulp.src(jsDir + '/main.js')
		.pipe(browserify({
			// insertGlobals : true
		})).on('error', gutil.log)
		.pipe(gulp.dest(targetJsDevDir))

	// then uglify the dev versions for live
	gulp.src(targetJsDevDir + '/main.js')
		.pipe(sourcemaps.init())
		.pipe(uglify())
		.pipe(sourcemaps.write())
		.pipe(gulp.dest(targetJsLiveDir))
});

// Rerun the task when a file changes
gulp.task('watch', function() {
	gulp.watch(scssDir + '/**/*.scss', ['css']);
	gulp.watch(jsDir + '/**/*.js', ['js']);
});


gulp.task('default', ['watch', 'js', 'css']);
