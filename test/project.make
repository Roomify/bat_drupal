; bat testing makefile

api = 2
core = 8.x
projects[drupal][version] = 8.6.10

defaults[projects][subdir] = contrib

; Pull fullcalendar_library
projects[fullcalendar_library][type] = module
projects[fullcalendar_library][download][type] = git
projects[fullcalendar_library][download][url] = https://github.com/Roomify/fullcalendar_library.git
projects[fullcalendar_library][download][branch] = 8.x-1.x

; Pull the latest version of bat_api
projects[bat_api][type] = module
projects[bat_api][download][type] = git
projects[bat_api][download][url] = https://github.com/Roomify/bat_api.git
projects[bat_api][download][branch] = 8.x-1.x
projects[bat_api][subdir] = bat

; +++++ Contrib Modules +++++

projects[services][version] = 4.0-beta4
projects[services][patch][] = https://www.drupal.org/files/issues/2019-02-13/3031777-10-error-call-to-a-member-function-getdefinitions-on-null-when-clear-cache.patch

; +++++ Libraries +++++

; colorpicker
libraries[colorpicker][directory_name] = colorpicker
libraries[colorpicker][type] = library
libraries[colorpicker][destination] = libraries
libraries[colorpicker][download][type] = get
libraries[colorpicker][download][url] = http://www.eyecon.ro/colorpicker/colorpicker.zip

; fullcalendar
libraries[fullcalendar][directory_name] = fullcalendar
libraries[fullcalendar][type] = library
libraries[fullcalendar][destination] = libraries
libraries[fullcalendar][download][type] = get
libraries[fullcalendar][download][url] = https://github.com/arshaw/fullcalendar/releases/download/v3.9.0/fullcalendar-3.9.0.zip

; scheduler
libraries[scheduler][directory_name] = fullcalendar-scheduler
libraries[scheduler][type] = library
libraries[scheduler][destination] = libraries
libraries[scheduler][download][type] = get
libraries[scheduler][download][url] = https://github.com/fullcalendar/fullcalendar-scheduler/releases/download/v1.9.3/fullcalendar-scheduler-1.9.3.zip
