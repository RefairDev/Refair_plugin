// Gulp.js configuration
/**
 * 
 * Sample to modify according to your dev environment 
 * 
 */
'use strict';

const
	
	devType = "plugins",
	domainName = "refair",
	devName = "refair_plugin",
	serverPath = '[LOCAL_SERVER_DIR_PATH]',
	
	minified_public_js_file = "refair-plugin-public.js",
	minified_admin_js_file = "refair-plugin-admin.js",


  // source and build folders
  dir = {
    src         : 'src/',
    build       : serverPath + domainName +'/wp-content/'+devType+'/'+ devName + '/',
	dist		: 'dist/' + devName + '/',
	distCustom	: 'dist/'	
  },

  // Gulp and plugins
  gulp          = require('gulp'),
  gutil         = require('gulp-util'),
  newer         = require('gulp-newer'),
  imagemin      = require('gulp-imagemin'),
  sass          = require('gulp-sass')(require('sass')),
  postcss       = require('gulp-postcss'),
  deporder      = require('gulp-deporder'),
  concat        = require('gulp-concat'),
  stripdebug    = require('gulp-strip-debug'),
  uglify        = require('gulp-uglify'),
  debug     	= require('gulp-debug'),
  gulpdel       = require('del'),
  composer		= require('gulp-composer')
 ;



// Browser-sync
var browsersync = false;
var destFolder = dir.build;

let /** @type {import("gulp-zip")} */ gulpzip;

function setDevEnv(cb){
	destFolder = dir.build;
	setEnv(destFolder);
	cb();
}

function setProdEnv(cb){
	destFolder = dir.dist;
	setEnv(destFolder);
	cb();
}

function setEnv(destFold){	
	vendors.build = destFold + 'vendor';
	php.build = destFold;
	images.build = destFold + 'images/';
	css.build = destFold;
	css.sassOpts.imagePath = images.build;
	js.build = destFold + 'js/';
	return;
}

function clean(){
  return gulpdel(destFolder, {force: true});
};

const startup = async () => {
	// @ts-ignore
	gulpzip = (await import("gulp-zip")).default;  
};
  
// run this task before any that require imagemin
async function startupWrapper() {
	await startup();
};

//fonts settings
var fonts = {
  src           : dir.src + '/public/fonts/*',
  build         : destFolder + 'fonts',
};

//copy fonts files
function fontsCopy() {
	fonts.build = destFolder + '/public/fonts';
	return gulp.src(fonts.src)
	    .pipe(newer(fonts.build))
		.pipe(debug({title: "font_task:"}))
	    .pipe(gulp.dest(fonts.build));
};

//vendors settings
var vendors = {
srcNode			: 'node_modules/',
	src         : dir.src + 'vendor/',
  build         : destFolder + 'vendor',
};


//copy other vendors files
function others (){
  return gulp.src([vendors.src+'**/*', '!' + bootstrap.src +'js/bootstrap.bundle.min.js'])
    .pipe(gulp.dest(vendors.build))
    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
};

var leaflet = {
	src : vendors.srcNode + 'leaflet/dist/**/*',
	dest  : destFolder + 'admin/js/leaflet',
  };
  
  //copy leaflet files
function leafletCopy () {
	leaflet.dest = destFolder + 'admin/js/leaflet';
	return gulp.src(leaflet.src)
	.pipe(debug({title: "leaflet_task:"}))
	.pipe(gulp.dest(leaflet.dest))
	.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
};

var select2 = {
	src : vendors.srcNode + 'select2/dist/**/*',
	dest  : destFolder + 'admin/js/select2',
  };
  
  //copy select2copy files
function select2Copy () {
	select2.dest = destFolder + 'admin/js/select2';
	return gulp.src(select2.src)
	.pipe(debug({title: "select2_task:"}))
	.pipe(gulp.dest(select2.dest))
	.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
};


// PHP settings
var php = {
  src           : dir.src + '**/*.php',
  build         : destFolder
};

// copy PHP files
function phpCopy () {

	php.build = destFolder;
	return gulp.src(php.src)
	    .pipe(debug({title: "Php_task:"}))
	    .pipe(newer(php.build))
	    .pipe(gulp.dest(php.build))
	    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
};

function jsonCopy () {
	  return gulp.src(dir.src + '**/*.json')
	    .pipe(debug({title: "json_task:"}))
	    .pipe(newer(destFolder))
	    .pipe(gulp.dest(destFolder))
	    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop()); 
	}


function composer_libs(cb){
	composer({
		"working-dir": destFolder
  });
  cb();
};

//image settings
var images = {
  src         : dir.src + 'images/**/*',
  build       : destFolder + 'images/'
};

// image processing
function imagesCopy() {
	images.build = destFolder + 'images/';
	return gulp.src(images.src)
	    .pipe(newer(images.build))
	    .pipe(imagemin())
	    .pipe(gulp.dest(images.build));
};

//language settings
var lang = {
	src         : dir.src + 'languages/**/*',
	build       : destFolder + 'languages/'
  };

function langCopy(){
	lang.build = destFolder + 'languages/';
	return gulp.src(lang.src)
	    .pipe(newer(lang.build))
	    .pipe(gulp.dest(lang.build));
}




//CSS settings
var css = {
  src_public	: dir.src + 'public/scss/**/*.scss',
  src_admin    	: dir.src + 'admin/scss/**/*.scss',
  src_print    	: dir.src + 'print/scss/**/*.scss',
  watch_public	: dir.src + 'public/scss/**/*.scss',
  watch_admin	: dir.src + 'admin/scss/**/*.scss',
  watch_print	: dir.src + 'print/scss/**/*.scss',
  build_public	: destFolder + 'public/css/',
  build_admin	: destFolder + 'admin/css/',
  build_print	: destFolder + 'print/css/',

  sassOpts: {
    outputStyle     : 'expanded',
    imagePath       : images.build,
    precision       : 3,
    errLogToConsole : true
  },

  processors: [
    require('postcss-assets')({
      loadPaths: ['images/'],
      basePath: destFolder,
      baseUrl: '/wp-content/' + devType + '/' + devName + '/'
    }),
    require('postcss-sort-media-queries')(),
    require('autoprefixer')(),    
    require('cssnano')
  ]
};

// CSS processing
function css_public_task() {
	css.build_public = destFolder + 'public/css/';
	return gulp.src(css.src_public)
		.pipe(debug({title: "css_public_task:"}))
		.pipe(sass(css.sassOpts))
		.pipe(postcss(css.processors))
		.pipe(gulp.dest(css.build_public))
		.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
};

//CSS processing
function css_admin_Task() {
	css.build_admin = destFolder + 'admin/css/';
	return gulp.src(css.src_admin)
		.pipe(debug({title: "css_admin_Task:"}))
		.pipe(sass(css.sassOpts))
		.pipe(postcss(css.processors))
		.pipe(gulp.dest(css.build_admin))
		.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
};

function css_print_Task() {
	css.build_print = destFolder + 'print/css/';
	return gulp.src(css.src_print)
		.pipe(debug({title: "css_print_Task:"}))
		.pipe(sass(css.sassOpts))
		.pipe(postcss(css.processors))
		.pipe(gulp.dest(css.build_print))
		.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
};

//JavaScript settings
var js = {
  src_admin   		: dir.src + 'admin/js/**/*.js',
  src_public  		: dir.src + 'public/js/**/*.js',
  build_admin     	: destFolder + 'admin/js/',
  build_public     	: destFolder + 'public/js/',
  filename_public   : minified_public_js_file,
  filename_admin    : minified_admin_js_file
};

// JavaScript processing public files for dev
 function js_public() {
	 js.build_public = destFolder + 'public/js/';
	 return gulp.src(js.src_public, { sourcemaps: true })
		.pipe(deporder())
		.pipe(concat(js.filename_public))
		.pipe(newer(js.build_public))
		.pipe(gulp.dest(js.build_public, { sourcemaps: '.' }))
		.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());

};

// JavaScript processing private files for dev
function js_admin() {
	js.build_admin = destFolder + 'admin/js/';
	return gulp.src(js.src_admin, { sourcemaps: true })
		.pipe(deporder())
		.pipe(concat(js.filename_admin))
		.pipe(newer(js.build_admin))
		.pipe(gulp.dest(js.build_admin, { sourcemaps: '.' }))
		.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());

};

//JavaScript processing public files for prod
function js_public_prod() {
	js.build_public = destFolder + 'public/js/';
	return gulp.src(js.src_public, { sourcemaps: true })
		.pipe(debug({title: "js_public_prod:"}))
		.pipe(deporder())
		.pipe(concat(js.filename_public))
		.pipe(stripdebug())
//		.pipe(uglify())
		.pipe(newer(js.build_public))
		.pipe(gulp.dest(js.build_public, { sourcemaps: '.' }))
		.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());

};

//JavaScript processing admin files for prod
function js_admin_prod() {
	js.build_admin = destFolder + 'admin/js/';
	return gulp.src(js.src_admin, { sourcemaps: true })
		.pipe(debug({title: "js_admin_prod:"}))
		.pipe(deporder())
		.pipe(concat(js.filename_admin))
		.pipe(stripdebug())
		.pipe(uglify())
		.pipe(newer(js.build_admin))
		.pipe(gulp.dest(js.build_admin, { sourcemaps: '.' }))
		.pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());

};


//Browsersync options
var syncOpts = {
  proxy       : domainName,
  files       : dir.build + '**/*',
  open        : false,
  notify      : false,
  ghostMode   : false,
  ui: {
    port: 8001
  }
};


// browser-sync
function browsersyncManagement(cb) {
  if (browsersync === false) {
    browsersync = require('browser-sync').create();
    browsersync.init(syncOpts);
  }
  cb();
};

//watch for file changes
function watch(cb){

  // page changes
  gulp.watch(php.src, gulp.series(browsersyncManagement,phpCopy));

  gulp.watch(vendors.src, gulp.series(browsersyncManagement, others ));
  
  gulp.watch(dir.src + '**/*.json', gulp.series(browsersyncManagement, jsonCopy, composer_libs ));

  // image changes
  gulp.watch(images.src, gulp.series(browsersyncManagement, imagesCopy));

    // CSS changes
  gulp.watch(css.watch_admin, gulp.series(fontsCopy,gulp.series(browsersyncManagement, css_admin_Task)));
  gulp.watch(css.watch_public, gulp.series(fontsCopy,gulp.series(browsersyncManagement, css_public_task)));
  gulp.watch(css.watch_print, css_print_Task);

  gulp.watch(lang.src, langCopy);

  // JavaScript main changes
  gulp.watch([js.src_admin,js.src_public], gulp.series(browsersyncManagement, js_admin, js_public));

  cb();

};

function zipAll(){
	return gulp.src(dir.dist)
		  .pipe(gulpzip( devName + '.zip' ) )
		  .pipe(gulp.dest('dist'))
  }

exports.cleanDev = gulp.series(
	startupWrapper,
	setDevEnv,
	clean
	);

//run distrib tasks
exports.dist = gulp.series(
					startupWrapper,
					setProdEnv,
					clean,
					gulp.parallel(fontsCopy, phpCopy),
					gulp.parallel(imagesCopy),
					gulp.parallel(css_public_task, css_admin_Task,css_print_Task),
					gulp.parallel(jsonCopy, composer_libs),  
					//gulp.parallel(jsonCopy, composer_libs,others),  
					gulp.parallel(leafletCopy,select2Copy,others,langCopy),
					gulp.parallel(js_admin_prod, js_public_prod),
					zipAll);

//default task
exports.default = gulp.series(
					startupWrapper,
					setDevEnv,
                    gulp.parallel(fontsCopy, phpCopy),
                    gulp.parallel(imagesCopy),
                    gulp.parallel(css_public_task, css_admin_Task,css_print_Task),
                    gulp.parallel(leafletCopy,select2Copy,others,langCopy),
                    gulp.parallel(jsonCopy, composer_libs),
					//gulp.parallel(jsonCopy, composer_libs,others),
                    gulp.parallel(js_admin, js_public),
                    gulp.parallel(watch, browsersyncManagement)
                    );
