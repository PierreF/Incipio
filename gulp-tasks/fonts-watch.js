module.exports = function(gulp, config) {
    return function() {
        gulp.watch(config.src + '/fonts/**/*', ['fonts']);
    };
};
