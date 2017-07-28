/**
 * Gruntfile.js
 * @author      {@link https://socialveo.com Socialveo}
 * @copyright   Copyright (C) 2017 Socialveo Sagl - All Rights Reserved
 * @license     Proprietary Software Socialveo (C) 2017, Socialveo Sagl {@link https://socialveo.com/legal Socialveo Legal Policies}
 */

module.exports = function (grunt) {

    require('load-grunt-tasks')(grunt);

    // tasks
    grunt.initConfig({

        config: {
            sassDir: "templates/bootstrap/assets/css/socialveo",
            sassFileName: "templates/bootstrap/assets/css/socialveo/custom"
        },
        sass: {
            dev: {
                options: {
                    sourceMap: true,
                    unixNewlines: true,
                    style: 'expanded'
                },
                files: [
                    {
                        expand: true,
                        cwd: '<%= config.sassDir %>',
                        src: ['**/*.scss', '!<%= config.sassFileName %>.scss'],
                        dest: '<%= config.sassDir %>',
                        ext: '.css'
                    },
                ]
            },
        },
        watch: {
            options: {
                debounceDelay: 1,
            },
            css: {
                files: ['<%= config.sassDir %>**/*.scss'],
                    tasks: ['sass:dev'],
                    options: {
                    spawn: false,
                }
            }
        }
    });
};