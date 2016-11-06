#
import requests
#import urllib.request
import json
import time
import math
import sys

def project(pt, d, angle):
    """
    Project from point, distance d, angle
    """
    R = 6378.1 #Radius of the Earth, km
    brng = math.radians(angle)
    lat1 = math.radians(pt[0])
    lon1 = math.radians(pt[1])

    lat2 = math.asin( math.sin(lat1)*math.cos(d/R) + math.cos(lat1)*math.sin(d/R)*math.cos(brng))
    lon2 = lon1 + math.atan2(math.sin(brng)*math.sin(d/R)*math.cos(lat1), math.cos(d/R)-math.sin(lat1)*math.sin(lat2))

    lat2 = math.degrees(lat2)
    lon2 = math.degrees(lon2)
    return (lat2, lon2)

# ----

# VIEW WITH http://berck.se/trips/lastseen2.php?rkey=0142593af753b1f0
#
print( "http://berck.se/trips/lastseen2.php?rkey=0142593af753b1f0" )

trips_url = "http://berck.se/trips/add_pt.php"
trips_wkey = "e176e1487d5834a0"

# http://www.onlineconversion.com/unix_time.htm
dt = 880000000 # Thu, 20 Nov 1997 04:26:40 GMT
dt = int(time.time())

# Start
lat = 56.26434113
lon = 12.87838519
cnt = 0
    
if True:
    delay = 1
    for x in range(0,4):
        if x > 0:
            time.sleep(delay)
        dt += delay
        full = trips_url+"?lat="+str(lat)+"&lon="+str(lon)+"&wkey="+trips_wkey+"&dt="+str(dt)+"&comment=pt"+str(cnt)
        cnt += 1
        print( full )
        r = requests.get(full)
        print( r.status_code )
        print( "--" )
        lat, lon = project( (lat, lon), 0.1, 0) #100 meters

    # stationary
    print( lat, lon )
    delay = 20
    for x in range(0,4):
        time.sleep(delay)
        dt += delay
        full = trips_url+"?lat="+str(lat)+"&lon="+str(lon)+"&wkey="+trips_wkey+"&dt="+str(dt)+"&comment=pt"+str(cnt)
        cnt += 1
        print( full )
        r = requests.get(full)
        print( r.status_code )
        print( "--" )
        lat, lon = project( (lat, lon), 0.005, 0) #5 meters

    # stationary
    print( lat, lon )
    for x in range(0,4):
        time.sleep(delay)
        dt += delay
        full = trips_url+"?lat="+str(lat)+"&lon="+str(lon)+"&wkey="+trips_wkey+"&dt="+str(dt)+"&comment=pt"+str(cnt)
        cnt += 1
        print( full )
        r = requests.get(full)
        print( r.status_code )
        print( "--" )
        lat, lon = project( (lat, lon), 0.1, 0) #100 meters
        
sys.exit(1)



# time delay, lat, lon
points = [
    #( 1, 56.24544,    12.88694),
    #(20, 56.24544,    12.88704),
    (10, 56.26434113, 12.87838519),
    (10, 56.26431729, 12.87861586), # +15 meter
    (10, 56.26427558, 12.87884653)  # +15 meter
    ]

points2 = [
    (10, 56.26989394, 12.88805723),
    (10, 56.27057309, 12.88754225), #+100m
    (10, 56.27064458, 12.88747787), #+10m
    (10, 56.27072798, 12.88745642),
    (10, 56.27088288, 12.88730621),
    (10, 56.27096628, 12.88724184), # A
    (30, 56.27102585, 12.88719893), # B
    (30, 56.27102586, 12.88719894), # C
    (30, 56.27128798, 12.88706481), # D
]
'''
After points2 on a clean DB, after "A" and before "B"

sqlite> .header on
sqlite> select * from points where userid="fc011c0d9d440c5da0d30324f0bf90ce" limit 20;
id|lat|lon|acc|speed|bearing|alt|type|datetime|gpstime|userid|trackid|comment|dist
317735|56.26989394|12.88805723|||||0|1477898891|1477898891|fc011c0d9d440c5da0d30324f0bf90ce|||-1.0
317736|56.27057309|12.88754225|||||0|1477898901|1477898901|fc011c0d9d440c5da0d30324f0bf90ce|||81.96221059999
317737|56.27064458|12.88747787|||||2|1477898921|1477898921|fc011c0d9d440c5da0d30324f0bf90ce|||8.8902896035681
317738|56.27088288|12.88730621|||||0|1477898931|1477898931|fc011c0d9d440c5da0d30324f0bf90ce|||28.546895808925
317739|56.27096628|12.88724184|||||1|1477898941|1477898941|fc011c0d9d440c5da0d30324f0bf90ce|||10.092268586289

After "B"
id|lat|lon|acc|speed|bearing|alt|type|datetime|gpstime|userid|trackid|comment|dist
317735|56.26989394|12.88805723|||||0|1477899116|1477899116|fc011c0d9d440c5da0d30324f0bf90ce|||-1.0
317736|56.27057309|12.88754225|||||0|1477899126|1477899126|fc011c0d9d440c5da0d30324f0bf90ce|||81.96221059999
317737|56.27064458|12.88747787|||||2|1477899146|1477899146|fc011c0d9d440c5da0d30324f0bf90ce|||8.8902896035681
317738|56.27088288|12.88730621|||||0|1477899156|1477899156|fc011c0d9d440c5da0d30324f0bf90ce|||28.546895808925
317739|56.27096628|12.88724184|||||2|1477899226|1477899226|fc011c0d9d440c5da0d30324f0bf90ce|||10.092268586289

After C
sqlite> select * from points where userid="fc011c0d9d440c5da0d30324f0bf90ce" limit 20;
id|lat|lon|acc|speed|bearing|alt|type|datetime|gpstime|userid|trackid|comment|dist
317735|56.26989394|12.88805723|||||0|1477900081|1477900081|fc011c0d9d440c5da0d30324f0bf90ce|||-1.0
317736|56.27057309|12.88754225|||||0|1477900091|1477900091|fc011c0d9d440c5da0d30324f0bf90ce|||81.96221059999
317737|56.27064458|12.88747787|||||2|1477900111|1477900111|fc011c0d9d440c5da0d30324f0bf90ce|||8.8902896035681
317738|56.27088288|12.88730621|||||0|1477900121|1477900121|fc011c0d9d440c5da0d30324f0bf90ce|||28.546895808925
317739|56.27096628|12.88724184|||||2|1477900191|1477900191|fc011c0d9d440c5da0d30324f0bf90ce|||10.092268586289

After D
sqlite> select * from points where userid="fc011c0d9d440c5da0d30324f0bf90ce" limit 20;
317735|56.26989394|12.88805723|||||0|1477900081|1477900081|fc011c0d9d440c5da0d30324f0bf90ce|||-1.0
317736|56.27057309|12.88754225|||||0|1477900091|1477900091|fc011c0d9d440c5da0d30324f0bf90ce|||81.96221059999
317737|56.27064458|12.88747787|||||2|1477900111|1477900111|fc011c0d9d440c5da0d30324f0bf90ce|||8.8902896035681
317738|56.27088288|12.88730621|||||0|1477900121|1477900121|fc011c0d9d440c5da0d30324f0bf90ce|||28.546895808925
317739|56.27096628|12.88724184|||||2|1477900191|1477900191|fc011c0d9d440c5da0d30324f0bf90ce|||10.092268586289
317740|56.27128798|12.88706481|||||0|1477900221|1477900221|fc011c0d9d440c5da0d30324f0bf90ce|||37.414620960712
'''
    
# we get a dist=15 on trips... it seems the total dist is measured from the original point,
# but the display from th previous one? No, always from last (UPDATEd) point.

for delay, lat, lon in points2:
    print( delay, lat, lon )
    time.sleep(delay)
    dt += delay
    full = trips_url+"?lat="+str(lat)+"&lon="+str(lon)+"&wkey="+trips_wkey+"&dt="+str(dt)
    print( full )
    r = requests.get(full)
    print( r.status_code )
    print( "--" )


'''
met lege DB en:
 (10, 56.26434113, 12.87838519),
 (10, 56.26431729, 12.87861586), # +15 meter
 (10, 56.26427558, 12.87884653)  # +15 meter

sqlite> .header on

sqlite> select * from points where userid="fc011c0d9d440c5da0d30324f0bf90ce" limit 10;
id|lat|lon|acc|speed|bearing|alt|type|datetime|gpstime|userid|trackid|comment|dist
317735|56.26434113|12.87838519|||||0|1477898541|1477898541|fc011c0d9d440c5da0d30324f0bf90ce|||-1.0
317736|56.26431729|12.87861586|||||2|1477898561|1477898561|fc011c0d9d440c5da0d30324f0bf90ce|||14.493320402164

That last point (317736) is the second point in the points list.
'''
    
'''
sqlite> .header on
sqlite> select * from points where userid="fc011c0d9d440c5da0d30324f0bf90ce" limit 10;
id|lat|lon|acc|speed|bearing|alt|type|datetime|gpstime|userid|trackid|comment|dist
317735|56.24544|12.88694|||||0|1477897102|1477897102|fc011c0d9d440c5da0d30324f0bf90ce|||-1.0
317736|56.24544|12.88694|||||2|1477897488|1477897488|fc011c0d9d440c5da0d30324f0bf90ce|||0.0
317737|56.26432921|12.87837982|||||0|1477897508|1477897508|fc011c0d9d440c5da0d30324f0bf90ce|||2166.5269300577

Has been at type=2 until we added the point farther away.
'''

'''
sqlite> select * from points where userid="fc011c0d9d440c5da0d30324f0bf90ce" limit 20;
id|lat|lon|acc|speed|bearing|alt|type|datetime|gpstime|userid|trackid|comment|dist
317735|56.24544|12.88694|||||0|1477897102|1477897102|fc011c0d9d440c5da0d30324f0bf90ce|||-1.0
317736|56.24544|12.88694|||||2|1477897488|1477897488|fc011c0d9d440c5da0d30324f0bf90ce|||0.0
317737|56.26432921|12.87837982|||||0|1477897508|1477897508|fc011c0d9d440c5da0d30324f0bf90ce|||2166.5269300577
317738|56.24544|12.88694|||||0|880000001|880000001|fc011c0d9d440c5da0d30324f0bf90ce|||2166.5269300577
317739|56.24544|12.88704|||||1|880000021|880000021|fc011c0d9d440c5da0d30324f0bf90ce|||6.1801376084407
317740|56.26432921|12.87837982|||||0|880000031|880000031|fc011c0d9d440c5da0d30324f0bf90ce|||2168.0435547904
317741|56.24544|12.88694|||||0|1477898076|1477898076|fc011c0d9d440c5da0d30324f0bf90ce|||2166.5269300577
317742|56.24544|12.88704|||||1|1477898096|1477898096|fc011c0d9d440c5da0d30324f0bf90ce|||6.1801376084407
317743|56.26434113|12.87838519|||||0|1477898106|1477898106|fc011c0d9d440c5da0d30324f0bf90ce|||2169.2465484385
317744|56.26431729|12.87861586|||||2|1477898126|1477898126|fc011c0d9d440c5da0d30324f0bf90ce|||14.493320402164
'''
