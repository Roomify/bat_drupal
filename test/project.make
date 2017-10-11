; bat testing makefile

api = 2
core = 7.x
projects[drupal][version] = 7.56

defaults[projects][subdir] = contrib

; Pull the latest version of bat_api
projects[bat_api][type] = module
projects[bat_api][download][type] = git
projects[bat_api][download][url] = https://github.com/Roomify/bat_api.git
projects[bat_api][download][branch] = 7.x-2.x
projects[bat_api][subdir] = bat

; +++++ Contrib Modules +++++

projects[composer_manager][version] = 1.8

projects[ctools][version] = 1.12

projects[date][version] = 2.10

projects[entity][version] = 1.8

projects[entityreference][version] = 1.5

projects[facetapi][version] = 1.5

projects[jquery_update][version] = 3.0-alpha5

projects[libraries][version] = 2.3

projects[search_api][version] = 1.22

projects[services][version] = 3.20

projects[variable][version] = 2.5

projects[views][version] = 3.18

projects[views_bulk_operations][version] = 3.4

projects[views_megarow][version] = 1.7

projects[xautoload][version] = 5.7


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
libraries[fullcalendar][download][url] = https://github.com/arshaw/fullcalendar/releases/download/v3.6.1/fullcalendar-3.6.1.zip

; scheduler
libraries[scheduler][directory_name] = fullcalendar-scheduler
libraries[scheduler][type] = library
libraries[scheduler][destination] = libraries
libraries[scheduler][download][type] = get
libraries[scheduler][download][url] = https://github.com/fullcalendar/fullcalendar-scheduler/releases/download/v1.8.0/fullcalendar-scheduler-1.8.0.zip

; jquery.timepicker
libraries[jquery.timepicker][directory_name] = jquery.timepicker
libraries[jquery.timepicker][type] = library
libraries[jquery.timepicker][destination] = libraries
libraries[jquery.timepicker][download][type] = get
libraries[jquery.timepicker][download][url] = https://fgelinas.com/code/timepicker/releases/jquery-ui-timepicker-0.3.3.zip
