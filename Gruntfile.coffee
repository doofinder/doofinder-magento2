module.exports = (grunt) ->
  config =
    version:
      project:
        options:
          prefix: 'doofinder-magento2\",\\s+\"version\":\\s+\"'
        src: ["package.json", "package-lock.json", "composer.json"]
      # module:
      #   options:
      #     prefix: 'name=\"Doofinder_Feed\"\\s+setup_version=\"'
      #   src: "etc/module.xml"

  grunt.initConfig config
  grunt.loadNpmTasks "grunt-version"

  grunt.registerTask "release", ["version:project:patch"]
  grunt.registerTask "release:minor", ["version:project:minor"]
  grunt.registerTask "release:major", ["version:project:major"]
