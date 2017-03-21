# DUiT: A Fantasktic App

<a href="#"><img src="/img/logo-square.jpg" align="left" height="100px" hspace="8" vspace="8"></a>

DUiT is a productivity manager for anyone who has ever looked at their calendar, to-do list, or jumble of sticky notes and wondered at the possibility of having one thing that could do it all. The purpose of DUiT is to organize projects, tasks, events, and other commitments (termed "du's") with a flexible, transformable interface that can go from a full weekly spread to a homework checklist to a project timeline and back again with the click of a mouse. 

## Features

**DUiT is still under development;** as such, most features exist as plans.

Implemented features are indicated with checked boxes, planned features with unchecked boxes.

- [x] Create and delete du's
- [ ] Edit and duplicate du's
- [ ] Check off completed du's and mark du's as in progress
- [x] Link dates and times to du’s or leave them as stand-alone
- [ ] Set du's to repeat daily, weekly, or monthly
- [ ] Group du's into categories by assigning them custom tags
- [ ] Arrange priority schemes for du's
- [ ] View personalized lists according to deadline, priority, category, etc.
- [ ] Import and sync events froom Google Calendar
- [ ] Track your du completion trends over time

For a detailed list of current individual features, see [What's New](#whats-new).

## Getting Started

### Prerequisites

The project relies on MySQL for managing its database.

### Install

After cloning the repository and setting up your preferred configuration of a LAMP stack, you can build the database with the constructor file, `duit-db/make-tables.sql`, which can be run from within MySQL as root user:

```
mysql> source path/to/make-tables.sql;
```

The constructor features sample data and will select several tables to display to the console for example purposes only. These examples can be removed by opening the file for editing and commenting out the last fifth of the file, beginning at line 167 with:

```sql
-- SAMPLE SELECTS 

SELECT 'TABLE OF DU\'S' AS '';
...
```

The backend is primarily handled by `duit-db/db-mapper.php`, which features a plethora of functions, all individually documented within the file itself. These will be consolidated into a separate documentation location in the near future. For now, `db-mappy.php` provides a sandbox space for testing the collection of functions that interact with the database.


## What's New

v0.0.2 (3-14-2017): Du's and the database

* Fleshed out du objects
* Introduced collection of functions for interacting with the database
* Added extensive logging for backend function calls

v0.0.1 (3-14-2017): Initializing GitHub repo

* Added DB constructor
* Added README, LICENSE, and images

v0.0.0 (3-8-2017): The project is underway!

* The proposed task-keeping system is named DUiT and gets a sick new logo

## Authors

<a href="https://github.com/courier-new"><img src="https://avatars2.githubusercontent.com/u/8942601?v=3&s=460" align="left" height="30px"></a> **Kelli Rockwell** - [[courier-new]](https://github.com/courier-new)

<a href="https://github.com/auberginekenobi"><img src="https://avatars3.githubusercontent.com/u/5943686?v=3&s=460" align="left" height="30px"></a> **Owen Chapman** -  [[auberginekenobi]](https://github.com/auberginekenobi)

<a href="https://github.com/patrickshao"><img src="https://avatars0.githubusercontent.com/u/5953037?v=3&s=460" align="left" height="30px"></a> **Patrick Shao** - [[patrickshao]](https://github.com/patrickshao)

## License

DUiT is released under the [MIT](/LICENSE) license.
