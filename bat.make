api = 2
core = 7.x

; Required Modules
projects[bat_api][version] = 2.x
projects[xautoload][version] = 5.6
projects[composer_manager][version] = 1.8
projects[date][version] = 2.9
projects[entity][version] = 1.7
projects[entityreference][version] = 1.1
projects[entityreference][patch][] = https://www.drupal.org/files/issues/entityreference-1836106-20.patch
projects[ctools][version] = 1.9
projects[jquery_update][version] = 3.0-alpha3
projects[libraries][version] = 2.2
projects[views][version] = 3.13
projects[views_megarow][version] = 1.5
projects[views_bulk_operations][version] = 3.3
projects[search_api][version] = 1.17
projects[services][version] = 3.14
projects[facetapi][version] = 1.5
projects[facetapi][patch][] = https://www.drupal.org/files/issues/facetapi-cur-search-block-not-exported-1469002_2.patch

; FullCalendar
libraries[fullcalendar][directory_name] = fullcalendar
libraries[fullcalendar][type] = library
libraries[fullcalendar][destination] = libraries
libraries[fullcalendar][download][type] = get
libraries[fullcalendar][download][url] = https://github.com/arshaw/fullcalendar/releases/download/v2.6.1/fullcalendar-2.6.1.zip

; FullCalendar Scheduler
libraries[scheduler][directory_name] = fullcalendar-scheduler
libraries[scheduler][type] = library
libraries[scheduler][destination] = libraries
libraries[scheduler][download][type] = get
libraries[scheduler][download][url] = https://github.com/fullcalendar/fullcalendar-scheduler/releases/download/v1.2.1/fullcalendar-scheduler-1.2.1.zip

; jquery.timepicker
libraries[jquery.timepicker][directory_name] = jquery.timepicker
libraries[jquery.timepicker][type] = library
libraries[jquery.timepicker][destination] = libraries
libraries[jquery.timepicker][download][type] = get
libraries[jquery.timepicker][download][url] = https://fgelinas.com/code/timepicker/releases/jquery-ui-timepicker-0.3.3.zip
