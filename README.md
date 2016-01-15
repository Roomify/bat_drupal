# Welcome to BAT for Drupal

BAT stands for Booking and Availability Management Tools.

[BAT](https://github.com/roomify/bat) on its own is a PHP library that provides some of the core functionality required to build a booking and availability management system.

BAT for Drupal is a wrapper and UI around BAT. It is build by the [Roomify.us](https://roomify.us) team to provide a foundation through which a wide range of availability management, reservation and booking use cases can be addressed. BAT for Drupal will work with both Drupal 7 and Drupal 8.

BAT builds on our experience with [Rooms](http://drupal.org/project/rooms), which handles the problem of bookings specifically for the accommodation for rental use case (vacation rentals, hotels, B&B, etc). BAT is essentially a tool to allow you to build modules like Rooms and much more. It handles events with daily or down to the minute granularity.

As such, BAT is a **booking and availability management framework** - much in the same way that Drupal is a content management framework and Drupal Commerce is an e-commerce framework. Our aim is to build specific solutions on top of BAT to tackle specific application domains.


# Objectives

BAT aims to address the following tasks.

- **Define bookable things**. Entities that could represent anything within an application domain that have information associated with them about what *state* they find themselves in any given period of time. In addition, you may also need to associate *pricing information* related to the cost of changing the *availability state* of a *thing* over a given time (e.g. booking a hotel room for a few nights).

- **Manage availability states**. *Bookable things* will find themselves in various states (e.g. "available to book", "unavailable", "currently in use by Bruce", etc). BAT allows you to define such states and provides both GUI-based tools (e.g. interactive calendars) as well as API-based tools to change such states (because machines want to have fun too).

- **Search for available things**.  Given a time range, a set of acceptable availability states, and an arbitrary number of other search filters BAT, should be able to answer questions such as: "BAT, is the car available to pick up from the cave at 4pm today, thanks - Robin".

- **Determine cost of booking**. At any given time and given state, things may have a cost to change from one state to another (e.g. go from "car sitting in cave" to "car being used by Robin". This cost for the change in state (i.e. a booking) is determined using tools that BAT provides to define what the pricing terms will be.


# Installation

## Dependencies

### PHP Libraries
The core booking and availability management functionality is provided through a PHP library called BAT, also developed by Roomify. The required version is described in the composer.json file in the root of the module. The library is available on [Github](https://github.com/roomify/bat) and through [Packagist](https://packagist.org/packages/roomify/bat).

### Drupal Modules

Before enabling BAT you are going to need to download at least the following modules:
- CTools
- Entity
- Views
- Entity Reference
- Date
- Date Popup
- JQuery Update
- Libraries
- Variable
- XAutoload
- Composer Manager (this makes it easier to manage the BAT PHP library)
- Views Megarow
- BAT API

### External Libraries

To display calendars and dates we use the following libraries:

- Fullcalendar - http://fullcalendar.io/ - You need to [download the following zip](https://github.com/arshaw/fullcalendar/releases/download/v2.6.0/fullcalendar-2.6.0.zip) and unpack it in your site's libraries directory, in a directory called fullcalendar
- Fullcalendar Scheduler - http://fullcalendar.io/ - You need to [download the following zip](https://github.com/fullcalendar/fullcalendar-scheduler/releases/download/v1.2.0/fullcalendar-scheduler-1.2.0.zip) and unpack in your site's libraries directory in a directory called fullcalendar-scheduler. Please note that scheduler is a premium add-on to fullcalendar, and you must purchase a license if you intend to use it in a commercial project. See: [Scheduler License Information](http://fullcalendar.io/scheduler/license/) (Scheduler is not developed by Roomify)
- MomentJS - http://momentjs.com/ - The [moment.js](http://momentjs.com/downloads/moment.min.js) library should be placed in your site's library directory under the moment directory, so that you end up with the file located here: <library path>/moment/moment.min.js

## Configuration
 - Enable at least the BAT, BAT Unit, BAT Event and BAT Event UI modules (if you want to view events on a calendar)
 - For BAT Event UI to work you need the BAT API module which is in a separate project - http://drupal.org/project/bat_api - *you need branch 7.x-2.x*
 - Make sure to set the jQuery for the admin theme to at least 1.10 by visiting *admin/config/development/jquery_update*

## Setup

- Create an Event Type - events store information about what state (or value) a unit has over a given period. For example, if you want to manage the availability state of units you can create an Event Type called "Availability" - tell BAT that this is a fixed state event type and then (under the States Type of Event Type configuration) add three states "Available", "Unavailable" and "Booked" - by making the Booked state blocking, you will ensure that you can edit it on the Calendar and it is not overridden by state changes.

- With an Event Type in place we can now create a Unit Type Bundle - this describes the types of unit types that your booking application handles. For example, if you are managing a hotel with hotel rooms and conference rooms, you would create one unit type bundle for Hotel Rooms and another for Conference Rooms.

- You can now create Types. These are the types of units for each bundle you created above. So you can create Hotel Rooms of Type Standard and Hotel Rooms of Type Deluxe.

- Finally for each type you can add actual units - you are now telling the application how many things of type Standard or Deluxe you have available.

- Almost there! Your units will have default values for things like availability or cost. You let BAT know about these default values by doing two things.

- 1. Add a field named (e.g.) Default Availability on your unit type bundle, using the BAT Availability Reference field type. This is a field type BAT creates that allows you to reference the availability states of an Event. In this case we will reference the Event Type we created before.

- 2. Go to "bat/config/type-bundles/", edit your Unit Type Bundle once more, and in the Events tab select the field_default_availability field (or whatever you called it). This tells BAT that this field holds the information for default availability.

- Now you can edit your types and define what their default availability is.

- You can now visit the "admin/bat/unit-management" page and should see a link to the Calendar pages (if you enabled the bat_event_ui module). There you can create and edit the availability state of your units and create bookings!


Admittedly it is not simple. However BAT is a tool to build booking tools. We are now using BAT to rebuild our accommodations booking module: [Rooms](http://drupal.org/project/rooms). BAT makes no assumptions and attempts to provide as much flexibility as is possible!
