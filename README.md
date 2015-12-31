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

