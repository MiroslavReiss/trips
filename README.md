### Plots points from a sqlite DB 

Needs some work to get going.

1. Expect the database to be filled with points by a tracker. The tracker
calls (GET) the function "add_pt.php" with a number of parameters.

2. The database needs two tables, users and points.

..* users

~~~~
sqlite> .schema users
CREATE TABLE users (
  id INTEGER PRIMARY KEY,
  userid VARCHAR(32) UNIQUE,
  rkey VARCHAR(16) UNIQUE,
  wkey VARCHAR(16) UNIQUE,
  name TEXT,
  email INTEGER UNIQUE,
  datetime TEXT
);
~~~~

..* points

~~~~
sqlite> .schema points
CREATE TABLE points (
  id INTEGER PRIMARY KEY,
  lat TEXT,
  lon TEXT,
  acc FLOAT,
  speed FLOAT,
  bearing FLOAT,
  alt FLOAT,
  type INTEGER,
  datetime TEXT,
  gpstime INTEGER,
  userid VARCHAR(32),
  trackid VARCHAR(32),
  comment TEXT,
  dist float);
~~~~

#### Screen shots

![alt tag](https://raw.githubusercontent.com/durian/trips/master/Screen Shot 2016-10-22 at 10.22.52.png)

![alt tag](https://raw.githubusercontent.com/durian/trips/master/Screen Shot 2016-10-22 at 10.23.46.png)
