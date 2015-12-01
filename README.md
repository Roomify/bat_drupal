# Welcome to BAT

BAT stands for Booking and Availability Management Tools.

It is a set of tools created by the [Roomify.us](https://roomify.us) team to provide a foundation through which a wide range of availability management, reservation and booking use cases can be addressed. BAT will work with both Drupal 7 and Drupal 8.

BAT builds on our experience with [Rooms](http://drupal.org/project/rooms), which handles the problem of bookings specifically for the accommodation for rental use case(vacation rentals, hotels, B&B, etc).

BAT on its own is a **booking and availability management framework** - much in the same way Drupal is a content management framework and Drupal Commerce is an e-commerce framework. Our aim is to build specific solutions on top of BAT to tackle specific application domains.


# Objectives

BAT aims to address the following tasks.

- **Define bookable things**. Entities that could represent anything within an application domain that have information associated with them about what *availability state* they find themselves in any given period of time. In addition, you may also need to associate *pricing information* related to the cost of changing the *availability state* a *thing* over a given time (e.g. booking a hotel room for a few nights). 

- **Manage availability states**. *Bookable things* will find themselves in various states (e.g. "available to book", "unavailable", "currently in use by Bruce", etc). BAT allows you to define such states and provides both GUI-based tools (e.g. interactive calendars) as well as API-based tools to change such states (because machines want to have fun too).

- **Search for available things**.  Given a time range, a set of acceptable availability states, and an arbitrary number of other search filters BAT should be able to answer question such as: "BAT, is the car available to pick up from the cave at 4pm today, thanks - Robin".

- **Determine cost of booking**. At any given time and given state things will have a cost to change from one state to another (e.g. go from "car sitting in cave" to "car being used by Robin". This cost for the change in state (i.e. a booking) is determined using tools that BAT provides to define what the pricing terms will be.


# Installation

## Dependencies

### Drupal Modules

Before enabling BAT you are going to need to download the following modules
- Date
- Date Popup
- JQuery Update
- Libraries
- Variable
- XAutoload

### External Libraries

To display calendars and dates we use the following libraries:

- Fullcalendar - http://fullcalendar.io/ - You need to [download the following zip](https://github.com/arshaw/fullcalendar/releases/download/v2.4.0/fullcalendar-2.4.0.zip) an unpack in libraries in a directory called fullcalendar
- MomentJS - http://momentjs.com/ - The [moment.js](http://momentjs.com/downloads/moment.min.js) library should be placed in sites/all/libraries so that you end up with the file located here: sites/all/libraries/moment/moment.min.js

## Configuration
 - Enable all the BAT modules
 - The BAT API module is in a separate project - http://drupal.org/project/bat_api - and you need branch 7.x-2.x
 - Make sure to set the jQuery for the admin theme to at least 1.10 by visiting *admin/config/development/jquery_update*
 
### Creating Booking Units
The first thing you will want to do is create a bookable unit which you can then manage the availability of.

Visit *admin/bat/units/unit-types* to create a unit. Bookable units are basic entities that you can manage the permissions off and add any fields you require.
 
### Pricing
To add price information to your bookable units you will need to:
- Add a Commerce Price field to your Bookable Unit Entity Type. 
- Under *bat/units/unit-types/<yourunittype>* make sure that you have the correct price field selected under the pricing tab.
