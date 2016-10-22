### Plots points from a sqlite DB 

Needs some work to get going.

1. Expect the database to be filled with points by a tracker. The tracker
calls (GET) the function "add_pt.php" with a number of parameters.

~~~~
add_pt.php?lat=56.33804&lon=12.89562&wkey=998d1ef610d897aa&acc=30.0&speed=1&bearing=170.7&alt=68&time=1341735161
~~~~

2. The database needs two tables, users and points.

#### users

The rkey and wkey are the read and write keys. The wkey needs to be
included in the GET statement when adding a point (see example above).
~~~~
sqlite> .schema users
CREATE TABLE users (
  id INTEGER PRIMARY KEY,
  userid VARCHAR(32) UNIQUE,
  rkey VARCHAR(16) UNIQUE,
  wkey VARCHAR(16) UNIQUE,
  name TEXT,
  email TEXT UNIQUE,
  datetime TEXT
);
~~~~

Example entry:
~~~~
sqlite> select * from users;
id|userid|rkey|wkey|name |email|datetime
1|18675ad1d922346ed56bdeeb1b91cd23|04fda935cfa9f2b7|59a047fe8a050c40|Peter|peter@example.com|2012-07-11 08:08:34
~~~~

#### points
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

Example entries:
~~~~
sqlite> select * from points order by id asc limit 10;
id|lat|lon|acc|speed|bearing|alt|type|datetime|gpstime|userid|trackid|comment|dist
60000|56.13282|13.33143|12.0|24.5|250.2|113.9|0|1444070684|1444070684|f22d8332a3ef367678c1021c13965440|||1542.2188761999
60001|56.12885|13.30972|11.0|21.5|255.8|114.3|0|1444070746|1444070746|f22d8332a3ef367678c1021c13965440|||1416.3147849256
~~~~

#### Screen shots

![alt tag](https://raw.githubusercontent.com/durian/trips/master/Screen Shot 2016-10-22 at 10.22.52.png)

![alt tag](https://raw.githubusercontent.com/durian/trips/master/Screen Shot 2016-10-22 at 10.23.46.png)
