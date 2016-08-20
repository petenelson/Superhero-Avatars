module.exports = function( grunt ) {

	grunt.initConfig( {
		pkg:    grunt.file.readJSON( 'package.json' ),

		wp_readme_to_markdown: {
			options: {
				screenshot_url: "https://raw.githubusercontent.com/petenelson/dashboard-directory-size/master/assets/{screenshot}.png",
				},
			your_target: {
				files: {
					'README.md': 'readme.txt'
				}
			},
		},

		clean:  {
			wp: [ "release" ]
		},

		copy:   {
			// create release for WordPress repository
			wp: {
				files: [

					// directories
					{ expand: true, src: ['assets/**'], dest: 'release/meaty-avatars' },

					// root dir files
					{
						expand: true,
						src: [
							'*.php',
							'readme.txt',
							],
						dest: 'release/meaty-avatars'
					}

				]
			} // wp

		}

	} ); // grunt.initConfig


	// Load tasks
	var tasks = [
		'grunt-contrib-clean',
		'grunt-contrib-copy',
		'grunt-wp-readme-to-markdown'
		];

	for	( var i = 0; i < tasks.length; i++ ) {
		grunt.loadNpmTasks( tasks[ i ] );
	};


	// Register tasks

	grunt.registerTask( 'readme', ['wp_readme_to_markdown' ] );

	// create release for WordPress repository
	grunt.registerTask( 'wp', [ 'clean', 'copy' ] );

	grunt.util.linefeed = '\n';

};
