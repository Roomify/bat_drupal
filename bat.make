api = 2
core = 7.x

; Required Modules
projects[bat_api][version] = 2.x
projects[xautoload][version] = 5.7
projects[composer_manager][version] = 1.8
projects[date][version] = 2.10
projects[entity][version] = 1.8
projects[entityreference][version] = 1.5
projects[ctools][version] = 1.12
projects[jquery_update][version] = 3.0-alpha5
projects[libraries][version] = 2.3
projects[views][version] = 3.18
projects[views_megarow][version] = 1.7
projects[views_bulk_operations][version] = 3.4
projects[search_api][version] = 1.22
projects[services][version] = 3.20
projects[facetapi][version] = 1.5
projects[facetapi][patch][] = https://www.drupal.org/files/issues/facetapi-cur-search-block-not-exported-1469002_2.patch

; FullCalendar
libraries[fullcalendar][directory_name] = fullcalendar
libraries[fullcalendar][type] = library
libraries[fullcalendar][destination] = libraries
libraries[fullcalendar][download][type] = get
; NB: please update the link in the documentation when changing this version.
libraries[fullcalendar][download][url] = https://github.com/arshaw/fullcalendar/releases/download/v3.6.1/fullcalendar-3.6.1.zip

; FullCalendar Scheduler
libraries[scheduler][directory_name] = fullcalendar-scheduler
libraries[scheduler][type] = library
libraries[scheduler][destination] = libraries
libraries[scheduler][download][type] = get
; NB: please update the link in the documentation when changing this version.
libraries[scheduler][download][url] = https://github.com/fullcalendar/fullcalendar-scheduler/releases/download/v1.8.0/fullcalendar-scheduler-1.8.0.zip

; jquery.timepicker
libraries[jquery.timepicker][directory_name] = jquery.timepicker
libraries[jquery.timepicker][type] = library
libraries[jquery.timepicker][destination] = libraries
libraries[jquery.timepicker][download][type] = get
libraries[jquery.timepicker][download][url] = https://fgelinas.com/code/timepicker/releases/jquery-ui-timepicker-0.3.3.zip
