; bat testing makefile

api = 2
core = 8.x
projects[drupal][version] = 8.4.2

defaults[projects][subdir] = contrib

; Pull the latest version of bat_api
projects[bat_api][type] = module
projects[bat_api][download][type] = git
projects[bat_api][download][url] = https://github.com/Roomify/bat_api_d8.git
projects[bat_api][subdir] = bat

; +++++ Contrib Modules +++++

projects[services][version] = 4.0-beta4

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
libraries[fullcalendar][download][url] = https://github.com/arshaw/fullcalendar/releases/download/v3.7.0/fullcalendar-3.7.0.zip

; scheduler
libraries[scheduler][directory_name] = fullcalendar-scheduler
libraries[scheduler][type] = library
libraries[scheduler][destination] = libraries
libraries[scheduler][download][type] = get
libraries[scheduler][download][url] = https://github.com/fullcalendar/fullcalendar-scheduler/releases/download/v1.9.0/fullcalendar-scheduler-1.9.0.zip
