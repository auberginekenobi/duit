# DuMi
#### A Fantasktic App

DuMi is a productivity manager for anyone who has ever looked at their calendar, to-do list, or jumble of sticky notes and wondered at the possibility of having one thing that could do it all. The purpose of DuMi is to organize projects, tasks, events, and other commitments (termed "du's") with a flexible, transformable interface that can go from a full weekly spread to a homework checklist to a project timeline and back again with the click of a mouse. 

Features include:

- [ ] Link dates and times to du’s or leave them as stand-alone
- [ ] Set du's to repeat daily, weekly, or monthly
- [ ] Group du's into categories by assigning them custom tags
- [ ] Arrange priority schemes for du's
- [ ] View personalized lists according to deadline, priority, category, etc.
- [ ] Import and sync events froom Google Calendar
- [ ] Track your du completion trends over time

## Getting Started

### Prerequisites

The project relies on MySQL for managing its database.

### Install

Currently there is only the database constructor, `make-tables.sql`, which can be run from within MySQL as root user:

```
mysql> source path/to/make-tables.sql;
```

The constructor features sample data and will select several tables to display to the console.

## What's New



## Authors

* **Kelli Rockwell** - *Primary developer* - [[courier-new]](https://github.com/courier-new)
* **Owen Chapman** - *Primary developer* - [[auberginekenobi]](https://github.com/auberginekenobi)
* **Patrick Shao** - *Primary developer* - [[courier-new]](https://github.com/patrickshao)

## License

DuMi is released under the [MIT](/LICENSE) license.
