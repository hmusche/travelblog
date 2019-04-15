# travelblog
Blog software for our current world journey, using [TimeZoneDB](https://timezonedb.com/) for correct timezoning info, [MapBox](https://www.mapbox.com/) for Post Location, and Google Translate API to translate articles in the background to users locale.

## Example
To see this in action, see https://no-fly-zone.de

## Usage (ToDo)
Currently a dump of the SQL database is needed if you'd want
to spin up an installation of this. A DB installer and updater
is on the timeline.

The JS relies on logic supplied by MooTools, and uses MapBox
to display a map for geolocationed blog posts. A valid key is
needed in the config. The goal is to get rid of all frameworks
(namely jQuery and MooTools). 

If you're interested in my work, or have suggestions for improvement,
feel free to hit me up at hmusche@gmail.com.
